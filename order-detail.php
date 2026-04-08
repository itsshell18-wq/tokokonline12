
order_detail_php = """<?php
// order-detail.php - Detail Pesanan
require_once 'config.php';

// Cek login
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$order_id) {
    redirect('orders.php');
}

// Ambil detail order
$stmt = db()->prepare("
    SELECT o.*, pm.name as payment_method_name, pm.code as payment_code
    FROM orders o
    LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    flashMessage('error', 'Pesanan tidak ditemukan');
    redirect('orders.php');
}

// Ambil items
$stmt = db()->prepare("
    SELECT oi.*, p.name, p.image, p.slug
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

// Timeline tracking (simulasi)
$tracking = [
    ['status' => 'Pesanan Dibuat', 'date' => $order['created_at'], 'completed' => true],
    ['status' => 'Pembayaran', 'date' => $order['payment_status'] === 'paid' ? $order['updated_at'] : null, 'completed' => $order['payment_status'] === 'paid'],
    ['status' => 'Diproses', 'date' => in_array($order['status'], ['processing', 'shipped', 'delivered']) ? $order['updated_at'] : null, 'completed' => in_array($order['status'], ['processing', 'shipped', 'delivered'])],
    ['status' => 'Dikirim', 'date' => in_array($order['status'], ['shipped', 'delivered']) ? $order['updated_at'] : null, 'completed' => in_array($order['status'], ['shipped', 'delivered'])],
    ['status' => 'Selesai', 'date' => $order['status'] === 'delivered' ? $order['updated_at'] : null, 'completed' => $order['status'] === 'delivered'],
];

$page_title = 'Detail Pesanan #' . $order['order_code'] . ' - TokoKu';
include 'header.php';
?>

<div class="order-detail-page">
    <div class="detail-header">
        <div class="header-left">
            <a href="history.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <h1>Detail Pesanan</h1>
            <p class="order-code"><?php echo htmlspecialchars($order['order_code']); ?></p>
        </div>
        <div class="header-actions">
            <span class="status-badge status-<?php echo $order['status']; ?>">
                <?php echo getStatusLabel($order['status']); ?>
            </span>
            <?php if ($order['payment_status'] === 'unpaid'): ?>
                <a href="payment.php?order_id=<?php echo $order_id; ?>" class="btn btn-primary">
                    <i class="fas fa-credit-card"></i> Bayar
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Tracking Timeline -->
    <div class="tracking-section">
        <h3><i class="fas fa-truck"></i> Status Pengiriman</h3>
        <div class="tracking-timeline">
            <?php foreach ($tracking as $step): ?>
            <div class="tracking-step <?php echo $step['completed'] ? 'completed' : ''; ?>">
                <div class="step-icon">
                    <i class="fas fa-<?php echo $step['completed'] ? 'check' : 'circle'; ?>"></i>
                </div>
                <div class="step-info">
                    <span class="step-name"><?php echo $step['status']; ?></span>
                    <?php if ($step['date']): ?>
                        <span class="step-date"><?php echo date('d M Y H:i', strtotime($step['date'])); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="detail-grid">
        <!-- Order Info -->
        <div class="detail-main">
            <div class="info-card">
                <h3>Informasi Pesanan</h3>
                <div class="info-row">
                    <span class="info-label">Tanggal Pesanan</span>
                    <span class="info-value"><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Metode Pembayaran</span>
                    <span class="info-value">
                        <?php echo htmlspecialchars($order['payment_method_name']); ?>
                        <?php if ($order['cicilan_months'] > 0): ?>
                            <span class="badge-cicilan">Cicilan <?php echo $order['cicilan_months']; ?>x</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status Pembayaran</span>
                    <span class="info-value">
                        <span class="payment-badge payment-<?php echo $order['payment_status']; ?>">
                            <?php echo $order['payment_status'] === 'paid' ? 'Lunas' : 'Belum Dibayar'; ?>
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Alamat Pengiriman</span>
                    <span class="info-value address"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></span>
                </div>
            </div>
            
            <!-- Items List -->
            <div class="items-card">
                <h3>Produk Dipesan</h3>
                <div class="items-list">
                    <?php foreach ($items as $item): ?>
                    <div class="item-row">
                        <img src="uploads/products/<?php echo $item['image']; ?>" alt="" class="item-thumb">
                        <div class="item-info">
                            <a href="product.php?slug=<?php echo $item['slug']; ?>" class="item-name">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </a>
                            <span class="item-qty"><?php echo $item['quantity']; ?> x <?php echo formatRupiah($item['price']); ?></span>
                        </div>
                        <span class="item-total"><?php echo formatRupiah($item['subtotal']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Summary Sidebar -->
        <div class="detail-sidebar">
            <div class="summary-card">
                <h3>Ringkasan Pembayaran</h3>
                <div class="summary-rows">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span><?php echo formatRupiah($order['total_amount']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Ongkir</span>
                        <span class="text-success">Gratis</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span><?php echo formatRupiah($order['total_amount']); ?></span>
                    </div>
                </div>
                
                <?php if ($order['cicilan_months'] > 0): ?>
                <div class="cicilan-detail">
                    <h4>Detail Cicilan</h4>
                    <p><strong><?php echo $order['cicilan_months']; ?>x</strong> pembayaran</p>
                    <p class="cicilan-amount"><?php echo formatRupiah($order['cicilan_per_month']); ?> / bulan</p>
                    <small class="text-muted">Pembayaran pertama: Sekarang</small>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Help Box -->
            <div class="help-card">
                <h4>Butuh Bantuan?</h4>
                <p>Hubungi customer service kami jika ada masalah dengan pesanan Anda.</p>
                <a href="#" class="btn btn-outline btn-sm">
                    <i class="fas fa-headset"></i> Hubungi CS
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.order-detail-page {
    max-width: 1000px;
    margin: 0 auto;
}

.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
}

.header-left {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.btn-back {
    color: #64748b;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.btn-back:hover {
    color: var(--primary);
}

.detail-header h1 {
    color: var(--dark);
    margin: 0;
}

.order-code {
    color: #64748b;
    font-size: 0.9rem;
}

.header-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
}

/* Tracking Section */
.tracking-section {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow);
}

.tracking-section h3 {
    margin-bottom: 1.5rem;
    color: var(--dark);
}

.tracking-timeline {
    display: flex;
    justify-content: space-between;
    position: relative;
}

.tracking-timeline::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e2e8f0;
    z-index: 0;
}

.tracking-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
    flex: 1;
}

.step-icon {
    width: 40px;
    height: 40px;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
    color: #94a3b8;
}

.tracking-step.completed .step-icon {
    background: var(--success);
    border-color: var(--success);
    color: white;
}

.step-info {
    text-align: center;
}

.step-name {
    display: block;
    font-size: 0.85rem;
    color: #64748b;
    font-weight: 500;
}

.step-date {
    font-size: 0.75rem;
    color: #94a3b8;
}

.tracking-step.completed .step-name {
    color: var(--dark);
}

/* Detail Grid */
.detail-grid {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
}

.info-card,
.items-card,
.summary-card,
.help-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow);
}

.info-card h3,
.items-card h3,
.summary-card h3 {
    margin-bottom: 1.5rem;
    color: var(--dark);
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    color: #64748b;
    font-weight: 500;
}

.info-value {
    color: var(--dark);
    text-align: right;
    max-width: 60%;
}

.info-value.address {
    font-size: 0.9rem;
    line-height: 1.5;
}

.badge-cicilan {
    display: inline-block;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-size: 0.75rem;
    margin-left: 0.5rem;
}

.payment-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.payment-paid {
    background: #d1fae5;
    color: #065f46;
}

.payment-unpaid {
    background: #fee2e2;
    color: #991b1b;
}

/* Items List */
.items-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.item-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 10px;
}

.item-thumb {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.item-info {
    flex: 1;
}

.item-name {
    display: block;
    color: var(--dark);
    font-weight: 500;
    text-decoration: none;
    margin-bottom: 0.25rem;
}

.item-name:hover {
    color: var(--primary);
}

.item-qty {
    font-size: 0.85rem;
    color: #64748b;
}

.item-total {
    font-weight: 600;
    color: var(--primary);
}

/* Summary Sidebar */
.summary-rows {
    margin-bottom: 1.5rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    color: #64748b;
}

.summary-row.total {
    padding-top: 1rem;
    border-top: 2px solid #e2e8f0;
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--dark);
}

.cicilan-detail {
    padding-top: 1.5rem;
    border-top: 1px dashed #e2e8f0;
}

.cicilan-detail h4 {
    margin-bottom: 0.5rem;
    color: var(--dark);
}

.cicilan-amount {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary);
    margin: 0.5rem 0;
}

.help-card {
    text-align: center;
}

.help-card h4 {
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.help-card p {
    color: #64748b;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .detail-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .tracking-timeline {
        flex-direction: column;
        gap: 1rem;
    }
    
    .tracking-timeline::before {
        display: none;
    }
    
    .tracking-step {
        flex-direction: row;
        gap: 1rem;
    }
    
    .step-info {
        text-align: left;
    }
}
</style>

<?php
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

with open('/mnt/kimi/output/order-detail.php', 'w', encoding='utf-8') as f:
    f.write(order_detail_php)

print("✅ File order-detail.php berhasil dibuat!")
print("\n📋 Fitur Detail Pesanan:")
print("- Tracking timeline visual")
print("- Informasi lengkap pesanan")
print("- Detail produk dengan thumbnail")
print("- Badge cicilan jika ada")
print("- Status pembayaran")
print("- Ringkasan pembayaran sidebar")
print("- Detail cicilan per bulan")
print("- Card bantuan customer service")
