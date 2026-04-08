
history_php = """<?php
// history.php - Riwayat Belanja Lengkap
require_once 'config.php';

// Cek login
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Filter dan pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where = "WHERE o.user_id = ?";
$params = [$user_id];

if ($status_filter) {
    $where .= " AND o.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where .= " AND (o.order_code LIKE ? OR p.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Count total
$count_sql = "SELECT COUNT(DISTINCT o.id) as total FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id LEFT JOIN products p ON oi.product_id = p.id $where";
$stmt = db()->prepare($count_sql);
$stmt->execute($params);
$total_rows = $stmt->fetch()['total'];
$total_pages = ceil($total_rows / $limit);

// Ambil data orders dengan items
$sql = "
    SELECT o.*, pm.name as payment_method, 
           COUNT(oi.id) as total_items,
           GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
    FROM orders o
    LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    $where
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT $limit OFFSET $offset
";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Statistik
$stats = [
    'total_orders' => 0,
    'completed' => 0,
    'processing' => 0,
    'cancelled' => 0,
    'total_spent' => 0
];

$stmt = db()->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status IN ('pending', 'processing', 'shipped') THEN 1 ELSE 0 END) as processing,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as total_spent
    FROM orders 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

$page_title = 'Riwayat Belanja - TokoKu';
include 'header.php';
?>

<div class="history-page">
    <div class="history-header">
        <h1><i class="fas fa-history"></i> Riwayat Belanja</h1>
        <div class="header-actions">
            <a href="products.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Belanja Baru
            </a>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-overview">
        <div class="stat-box">
            <div class="stat-icon blue"><i class="fas fa-shopping-bag"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $stats['total_orders']; ?></span>
                <span class="stat-label">Total Pesanan</span>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $stats['completed']; ?></span>
                <span class="stat-label">Selesai</span>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $stats['processing']; ?></span>
                <span class="stat-label">Dalam Proses</span>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon purple"><i class="fas fa-wallet"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?php echo formatRupiah($stats['total_spent'] ?? 0); ?></span>
                <span class="stat-label">Total Belanja</span>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-section">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Diproses</option>
                    <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Dikirim</option>
                    <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Selesai</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                </select>
            </div>
            <div class="filter-group search-group">
                <input type="text" name="search" class="form-control" 
                       placeholder="Cari kode pesanan atau produk..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
    
    <!-- Orders Timeline -->
    <div class="orders-timeline">
        <?php if (empty($orders)): ?>
            <div class="empty-history">
                <i class="fas fa-shopping-basket"></i>
                <h3>Belum Ada Riwayat Belanja</h3>
                <p>Yuk mulai belanja dan nikmati kemudahan berbelanja online!</p>
                <a href="products.php" class="btn btn-primary">Mulai Belanja</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
            <div class="timeline-card">
                <div class="timeline-marker">
                    <div class="marker-icon status-<?php echo $order['status']; ?>">
                        <i class="fas fa-<?php echo getStatusIcon($order['status']); ?>"></i>
                    </div>
                    <div class="timeline-line"></div>
                </div>
                
                <div class="timeline-content">
                    <div class="order-card-header">
                        <div class="order-info">
                            <h3><?php echo htmlspecialchars($order['order_code']); ?></h3>
                            <span class="order-date">
                                <i class="fas fa-calendar"></i> 
                                <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?>
                            </span>
                        </div>
                        <div class="order-badges">
                            <span class="badge status-<?php echo $order['status']; ?>">
                                <?php echo getStatusLabel($order['status']); ?>
                            </span>
                            <?php if ($order['cicilan_months'] > 0): ?>
                                <span class="badge cicilan">
                                    <i class="fas fa-calendar-alt"></i> 
                                    Cicilan <?php echo $order['cicilan_months']; ?>x
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="order-products">
                        <p class="products-preview">
                            <i class="fas fa-box"></i> 
                            <?php echo $order['total_items']; ?> item: 
                            <?php echo substr(htmlspecialchars($order['product_names']), 0, 100); ?>
                            <?php echo strlen($order['product_names']) > 100 ? '...' : ''; ?>
                        </p>
                    </div>
                    
                    <div class="order-footer">
                        <div class="order-amount">
                            <span class="amount-label">Total:</span>
                            <span class="amount-value"><?php echo formatRupiah($order['total_amount']); ?></span>
                            <?php if ($order['cicilan_months'] > 0): ?>
                                <small class="cicilan-note">
                                    (<?php echo formatRupiah($order['cicilan_per_month']); ?>/bulan)
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="order-actions">
                            <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            <?php if ($order['payment_status'] === 'unpaid'): ?>
                                <a href="payment.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-credit-card"></i> Bayar
                                </a>
                            <?php endif; ?>
                            <?php if ($order['status'] === 'delivered'): ?>
                                <button class="btn btn-sm btn-success" onclick="reviewOrder(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-star"></i> Ulas
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page-1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo $search; ?>" 
                       class="btn btn-outline">
                        <i class="fas fa-chevron-left"></i> Sebelumnya
                    </a>
                <?php endif; ?>
                
                <div class="page-numbers">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="page-active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo $search; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo $search; ?>" 
                       class="btn btn-outline">
                        Selanjutnya <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.history-page {
    max-width: 1000px;
    margin: 0 auto;
}

.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.history-header h1 {
    color: var(--dark);
}

/* Stats Overview */
.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-box {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: var(--shadow);
    transition: transform 0.3s;
}

.stat-box:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-icon.blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-icon.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.stat-icon.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.stat-icon.purple { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--dark);
}

.stat-label {
    font-size: 0.9rem;
    color: #64748b;
}

/* Filters */
.filter-section {
    background: white;
    border-radius: 15px;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow);
}

.filter-form {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.search-group {
    display: flex;
    gap: 0.5rem;
}

.search-group input {
    flex: 1;
}

/* Timeline */
.orders-timeline {
    position: relative;
}

.timeline-card {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
    animation: fadeInLeft 0.5s ease;
}

@keyframes fadeInLeft {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.timeline-marker {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.marker-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: white;
    z-index: 2;
}

.marker-icon.status-pending { background: #f59e0b; }
.marker-icon.status-processing { background: #3b82f6; }
.marker-icon.status-shipped { background: #6366f1; }
.marker-icon.status-delivered { background: #10b981; }
.marker-icon.status-cancelled { background: #ef4444; }

.timeline-line {
    width: 2px;
    flex: 1;
    background: #e2e8f0;
    margin-top: 0.5rem;
}

.timeline-card:last-child .timeline-line {
    display: none;
}

.timeline-content {
    flex: 1;
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: var(--shadow);
}

.order-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.order-info h3 {
    color: var(--dark);
    margin-bottom: 0.25rem;
}

.order-date {
    font-size: 0.85rem;
    color: #64748b;
}

.order-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.badge {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge.status-pending { background: #fef3c7; color: #92400e; }
.badge.status-processing { background: #dbeafe; color: #1e40af; }
.badge.status-shipped { background: #e0e7ff; color: #3730a3; }
.badge.status-delivered { background: #d1fae5; color: #065f46; }
.badge.status-cancelled { background: #fee2e2; color: #991b1b; }
.badge.cicilan { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }

.order-products {
    padding: 1rem;
    background: #f8fafc;
    border-radius: 10px;
    margin-bottom: 1rem;
}

.products-preview {
    color: #64748b;
    font-size: 0.9rem;
    margin: 0;
}

.order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

.order-amount {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
}

.amount-label {
    color: #64748b;
}

.amount-value {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--dark);
}

.cicilan-note {
    color: #64748b;
}

.order-actions {
    display: flex;
    gap: 0.5rem;
}

/* Empty State */
.empty-history {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 20px;
    box-shadow: var(--shadow);
}

.empty-history i {
    font-size: 5rem;
    color: #e2e8f0;
    margin-bottom: 1.5rem;
}

.empty-history h3 {
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.empty-history p {
    color: #64748b;
    margin-bottom: 1.5rem;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
}

.page-numbers {
    display: flex;
    gap: 0.5rem;
}

.page-numbers a,
.page-active {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    text-decoration: none;
    color: var(--dark);
    font-weight: 500;
}

.page-numbers a:hover {
    background: #f8fafc;
}

.page-active {
    background: var(--primary);
    color: white;
}

@media (max-width: 768px) {
    .history-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .timeline-card {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .timeline-line {
        display: none;
    }
    
    .order-card-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .order-footer {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}
</style>

<script>
function reviewOrder(orderId) {
    // Implementasi review modal
    alert('Fitur ulasan akan segera hadir! Order ID: ' + orderId);
}
</script>

<?php
function getStatusIcon($status) {
    $icons = [
        'pending' => 'clock',
        'processing' => 'cog',
        'shipped' => 'truck',
        'delivered' => 'check-circle',
        'cancelled' => 'times-circle'
    ];
    return $icons[$status] ?? 'question-circle';
}

function getStatusLabel($status) {
    $labels = [
        'pending' => 'Menunggu',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'delivered' => 'Selesai',
        'cancelled' => 'Dibatalkan'
    ];
    return $labels[$status] ?? $status;
}

include 'footer.php';
?>
"""

with open('/mnt/kimi/output/history.php', 'w', encoding='utf-8') as f:
    f.write(history_php)

print("✅ File history.php berhasil dibuat!")
print("\n📜 Fitur Riwayat Belanja:")
print("- Statistik overview (total, selesai, proses, belanja)")
print("- Filter by status dan search")
print("- Timeline view dengan visual marker")
print("- Badge cicilan pada pesanan")
print("- Detail produk dalam pesanan")
print("- Pagination")
print("- Animasi fade-in untuk setiap card")
print("- Responsive design")
