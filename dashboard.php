
dashboard_php = """<?php
// dashboard.php - Dashboard User
require_once 'config.php';

// Cek login
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Statistik user
$stmt = db()->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_orders = $stmt->fetch()['total_orders'];

$stmt = db()->prepare("SELECT SUM(total_amount) as total_spent FROM orders WHERE user_id = ? AND payment_status = 'paid'");
$stmt->execute([$user_id]);
$total_spent = $stmt->fetch()['total_spent'] ?? 0;

$stmt = db()->prepare("SELECT COUNT(*) as cart_items FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetch()['cart_items'];

// Pesanan terbaru
$stmt = db()->prepare("
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_orders = $stmt->fetchAll();

$page_title = 'Dashboard - TokoKu';
include 'header.php';
?>

<div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="uploads/profiles/<?php echo $current_user['profile_image']; ?>" 
                 alt="Profile" 
                 class="sidebar-avatar"
                 onerror="this.src='assets/default-avatar.jpg'">
            <h3><?php echo htmlspecialchars($current_user['full_name']); ?></h3>
            <p><?php echo htmlspecialchars($current_user['email']); ?></p>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> Profil Saya</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Pesanan</a></li>
            <li><a href="history.php"><i class="fas fa-history"></i> Riwayat Belanja</a></li>
            <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Keranjang</a></li>
            <li><a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a></li>
            <li class="divider"></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                <div class="stat-label">Total Pesanan</div>
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <a href="orders.php" class="stat-link">Lihat detail <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                <div class="stat-label">Total Belanja</div>
                <div class="stat-number"><?php echo formatRupiah($total_spent); ?></div>
                <a href="history.php" class="stat-link">Lihat riwayat <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-label">Item di Keranjang</div>
                <div class="stat-number"><?php echo $cart_items; ?></div>
                <a href="cart.php" class="stat-link">Lihat keranjang <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="stat-icon"><i class="fas fa-crown"></i></div>
                <div class="stat-label">Member Status</div>
                <div class="stat-number">Gold</div>
                <span class="stat-link">Active Member</span>
            </div>
        </div>
        
        <!-- Pesanan Terbaru -->
        <section class="recent-orders">
            <div class="section-header">
                <h2><i class="fas fa-clock"></i> Pesanan Terbaru</h2>
                <a href="orders.php" class="btn btn-outline btn-sm">Lihat Semua</a>
            </div>
            
            <?php if (empty($recent_orders)): ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <p>Belum ada pesanan</p>
                    <a href="products.php" class="btn btn-primary">Mulai Belanja</a>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($recent_orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <h4><?php echo htmlspecialchars($order['order_code']); ?></h4>
                                <span class="order-date"><?php echo date('d M Y', strtotime($order['created_at'])); ?></span>
                            </div>
                            <span class="order-status status-<?php echo $order['status']; ?>">
                                <?php echo getStatusLabel($order['status']); ?>
                            </span>
                        </div>
                        <div class="order-body">
                            <div class="order-info">
                                <span><i class="fas fa-box"></i> <?php echo $order['item_count']; ?> item</span>
                                <span><i class="fas fa-money-bill"></i> <?php echo formatRupiah($order['total_amount']); ?></span>
                            </div>
                            <?php if ($order['cicilan_months'] > 0): ?>
                                <div class="cicilan-badge">
                                    <i class="fas fa-calendar-alt"></i> 
                                    Cicilan <?php echo $order['cicilan_months']; ?>x
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="order-footer">
                            <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">
                                Detail Pesanan
                            </a>
                            <?php if ($order['payment_status'] === 'unpaid'): ?>
                                <a href="payment.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                    Bayar Sekarang
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Quick Actions -->
        <section class="quick-actions">
            <h2><i class="fas fa-bolt"></i> Aksi Cepat</h2>
            <div class="actions-grid">
                <a href="products.php" class="action-card">
                    <i class="fas fa-plus-circle"></i>
                    <span>Belanja Lagi</span>
                </a>
                <a href="profile.php" class="action-card">
                    <i class="fas fa-user-edit"></i>
                    <span>Edit Profil</span>
                </a>
                <a href="cart.php" class="action-card">
                    <i class="fas fa-cart-arrow-down"></i>
                    <span>Lihat Keranjang</span>
                </a>
                <a href="help.php" class="action-card">
                    <i class="fas fa-question-circle"></i>
                    <span>Bantuan</span>
                </a>
            </div>
        </section>
    </main>
</div>

<style>
.dashboard {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 2rem;
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.sidebar {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: var(--shadow);
    height: fit-content;
    position: sticky;
    top: 100px;
}

.sidebar-header {
    text-align: center;
    padding-bottom: 2rem;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 1.5rem;
}

.sidebar-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--primary);
    margin-bottom: 1rem;
}

.sidebar-header h3 {
    font-size: 1.2rem;
    margin-bottom: 0.25rem;
}

.sidebar-header p {
    color: #64748b;
    font-size: 0.9rem;
}

.sidebar-menu {
    list-style: none;
}

.sidebar-menu li {
    margin-bottom: 0.25rem;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    color: #475569;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.3s;
}

.sidebar-menu a:hover,
.sidebar-menu a.active {
    background: var(--gradient);
    color: white;
}

.sidebar-menu .divider {
    height: 1px;
    background: #e2e8f0;
    margin: 1rem 0;
}

.main-content h1 {
    margin-bottom: 2rem;
    color: var(--dark);
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--gradient);
    color: white;
    padding: 1.5rem;
    border-radius: 15px;
    position: relative;
    overflow: hidden;
}

.stat-icon {
    font-size: 2rem;
    opacity: 0.8;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin: 0.5rem 0;
}

.stat-link {
    color: white;
    text-decoration: none;
    font-size: 0.85rem;
    opacity: 0.9;
}

.stat-link:hover {
    opacity: 1;
}

/* Orders List */
.recent-orders {
    margin: 2rem 0;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.order-card {
    background: #f8fafc;
    border-radius: 15px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
    transition: all 0.3s;
}

.order-card:hover {
    box-shadow: var(--shadow);
    transform: translateY(-2px);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.order-header h4 {
    color: var(--dark);
    margin-bottom: 0.25rem;
}

.order-date {
    color: #64748b;
    font-size: 0.85rem;
}

.order-status {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-processing {
    background: #dbeafe;
    color: #1e40af;
}

.status-shipped {
    background: #e0e7ff;
    color: #3730a3;
}

.status-delivered {
    background: #d1fae5;
    color: #065f46;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.order-body {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.order-info {
    display: flex;
    gap: 1.5rem;
    color: #64748b;
    font-size: 0.9rem;
}

.order-footer {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

/* Quick Actions */
.quick-actions {
    margin-top: 2rem;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.action-card {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 15px;
    padding: 1.5rem;
    text-align: center;
    text-decoration: none;
    color: var(--dark);
    transition: all 0.3s;
}

.action-card:hover {
    border-color: var(--primary);
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.action-card i {
    font-size: 2rem;
    color: var(--primary);
    margin-bottom: 0.5rem;
    display: block;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem;
    color: #64748b;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: #cbd5e1;
}

.empty-state p {
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .dashboard {
        grid-template-columns: 1fr;
    }
    
    .sidebar {
        display: none;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
}
</style>

<?php
// Helper functions
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

with open('/mnt/kimi/output/dashboard.php', 'w', encoding='utf-8') as f:
    f.write(dashboard_php)

print("✅ File dashboard.php berhasil dibuat!")
print("\n📊 Fitur Dashboard:")
print("- Sidebar navigasi lengkap")
print("- Statistik cards (total order, belanja, keranjang)")
print("- Pesanan terbaru dengan status")
print("- Badge cicilan pada pesanan")
print("- Quick actions grid")
print("- Responsive layout")
