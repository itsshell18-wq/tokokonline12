
checkout_php = """<?php
// checkout.php - Halaman Checkout
require_once 'config.php';

// Cek login
if (!isLoggedIn()) {
    flashMessage('error', 'Silakan login terlebih dahulu');
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Ambil data keranjang
$stmt = db()->prepare("
    SELECT c.*, p.name, p.price, p.image, p.stock,
           (c.quantity * p.price) as subtotal
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    flashMessage('error', 'Keranjang Anda kosong');
    redirect('cart.php');
}

// Hitung total
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['subtotal'];
}
$shipping = 0; // Gratis ongkir
$total = $subtotal + $shipping;

// Ambil metode pembayaran
$stmt = db()->query("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY type, name");
$payment_methods = $stmt->fetchAll();

// Ambil data user
$stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid';
    } else {
        $shipping_address = trim($_POST['shipping_address'] ?? '');
        $payment_method_id = (int)($_POST['payment_method'] ?? 0);
        $cicilan_months = (int)($_POST['cicilan_months'] ?? 0);
        
        if (empty($shipping_address)) {
            $error = 'Alamat pengiriman wajib diisi';
        } elseif ($payment_method_id === 0) {
            $error = 'Pilih metode pembayaran';
        } else {
            try {
                db()->beginTransaction();
                
                // Generate order code
                $order_code = generateOrderCode();
                
                // Hitung cicilan per bulan jika ada
                $cicilan_per_month = 0;
                if ($cicilan_months > 0) {
                    $cicilan_per_month = $total / $cicilan_months;
                }
                
                // Insert order
                $stmt = db()->prepare("
                    INSERT INTO orders (user_id, order_code, total_amount, shipping_address, 
                                       payment_method_id, cicilan_months, cicilan_per_month)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $user_id, $order_code, $total, $shipping_address, 
                    $payment_method_id, $cicilan_months, $cicilan_per_month
                ]);
                
                $order_id = db()->lastInsertId();
                
                // Insert order items dan update stok
                foreach ($cart_items as $item) {
                    $stmt = db()->prepare("
                        INSERT INTO order_items (order_id, product_id, quantity, price, subtotal)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $order_id, $item['product_id'], $item['quantity'], 
                        $item['price'], $item['subtotal']
                    ]);
                    
                    // Update stok produk
                    $stmt = db()->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $stmt->execute([$item['quantity'], $item['product_id']]);
                }
                
                // Kosongkan keranjang
                $stmt = db()->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                // Insert payment history
                $stmt = db()->prepare("
                    INSERT INTO payment_history (order_id, amount, payment_method, status)
                    VALUES (?, ?, ?, 'pending')
                ");
                
                // Get payment method name
                $stmt_pm = db()->prepare("SELECT name FROM payment_methods WHERE id = ?");
                $stmt_pm->execute([$payment_method_id]);
                $pm_name = $stmt_pm->fetch()['name'];
                
                $stmt->execute([$order_id, $total, $pm_name]);
                
                db()->commit();
                
                // Redirect ke halaman pembayaran
                redirect('payment.php?order_id=' . $order_id);
                
            } catch (PDOException $e) {
                db()->rollBack();
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Checkout - TokoKu';
include 'header.php';
?>

<div class="checkout-page">
    <h1><i class="fas fa-credit-card"></i> Checkout</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" class="checkout-form" onsubmit="return validateForm(this)">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
        
        <div class="checkout-grid">
            <!-- Left Column -->
            <div class="checkout-left">
                <!-- Alamat Pengiriman -->
                <section class="checkout-section">
                    <h2><i class="fas fa-map-marker-alt"></i> Alamat Pengiriman</h2>
                    <div class="form-group">
                        <textarea name="shipping_address" class="form-control" rows="4" 
                                  placeholder="Masukkan alamat lengkap Anda..." required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                </section>
                
                <!-- Metode Pembayaran -->
                <section class="checkout-section">
                    <h2><i class="fas fa-wallet"></i> Metode Pembayaran</h2>
                    
                    <div class="payment-methods">
                        <?php foreach ($payment_methods as $pm): ?>
                        <label class="payment-option" data-type="<?php echo $pm['type']; ?>">
                            <input type="radio" name="payment_method" value="<?php echo $pm['id']; ?>" 
                                   <?php echo $pm['code'] === 'bank_transfer' ? 'checked' : ''; ?>
                                   onchange="handlePaymentChange(this)">
                            <div class="payment-content">
                                <div class="payment-icon">
                                    <i class="fas fa-<?php echo getPaymentIcon($pm['code']); ?>"></i>
                                </div>
                                <div class="payment-info">
                                    <span class="payment-name"><?php echo htmlspecialchars($pm['name']); ?></span>
                                    <?php if ($pm['type'] === 'cicilan'): ?>
                                        <span class="payment-badge cicilan">Cicilan 0%</span>
                                    <?php elseif ($pm['type'] === 'paylater'): ?>
                                        <span class="payment-badge spaylater">PayLater</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Cicilan Options -->
                    <div id="cicilan-options" class="cicilan-section" style="display: none;">
                        <h3>Pilih Tenor Cicilan</h3>
                        <div class="cicilan-tenor">
                            <?php foreach ([3, 6, 12] as $month): ?>
                            <label class="tenor-option">
                                <input type="radio" name="cicilan_months" value="<?php echo $month; ?>" 
                                       onchange="calculateCicilan()">
                                <div class="tenor-content">
                                    <span class="tenor-month"><?php echo $month; ?> Bulan</span>
                                    <span class="tenor-amount">
                                        <?php echo formatRupiah($total / $month); ?>/bulan
                                    </span>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <div id="cicilan-summary" class="cicilan-summary"></div>
                    </div>
                </section>
                
                <!-- Ringkasan Produk -->
                <section class="checkout-section">
                    <h2><i class="fas fa-shopping-bag"></i> Produk Dipesan</h2>
                    <div class="checkout-items">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="checkout-item">
                            <img src="uploads/products/<?php echo $item['image']; ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="item-info">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p><?php echo $item['quantity']; ?> x <?php echo formatRupiah($item['price']); ?></p>
                            </div>
                            <span class="item-total"><?php echo formatRupiah($item['subtotal']); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
            
            <!-- Right Column - Summary -->
            <div class="checkout-right">
                <div class="checkout-summary">
                    <h3>Ringkasan Pesanan</h3>
                    
                    <div class="summary-rows">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span><?php echo formatRupiah($subtotal); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Ongkir</span>
                            <span class="text-success">Gratis</span>
                        </div>
                        <div class="summary-row discount" id="discount-row" style="display: none;">
                            <span>Diskon</span>
                            <span class="text-success">-Rp 0</span>
                        </div>
                    </div>
                    
                    <div class="summary-total">
                        <span>Total Pembayaran</span>
                        <span id="final-total"><?php echo formatRupiah($total); ?></span>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-order">
                        <i class="fas fa-check-circle"></i> Buat Pesanan
                    </button>
                    
                    <a href="cart.php" class="btn btn-outline btn-back">
                        <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.checkout-page {
    max-width: 1200px;
    margin: 0 auto;
}

.checkout-page h1 {
    margin-bottom: 2rem;
    color: var(--dark);
}

.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 2rem;
}

.checkout-section {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow);
}

.checkout-section h2 {
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Payment Methods */
.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.payment-option {
    display: flex;
    align-items: center;
    padding: 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s;
}

.payment-option:hover {
    border-color: var(--primary);
}

.payment-option input[type="radio"] {
    margin-right: 1rem;
}

.payment-option input[type="radio"]:checked + .payment-content {
    color: var(--primary);
}

.payment-option:has(input:checked) {
    border-color: var(--primary);
    background: #f5f3ff;
}

.payment-content {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
}

.payment-icon {
    width: 50px;
    height: 50px;
    background: #f8fafc;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--primary);
}

.payment-info {
    display: flex;
    flex-direction: column;
}

.payment-name {
    font-weight: 600;
    color: var(--dark);
}

.payment-badge {
    font-size: 0.75rem;
    padding: 0.2rem 0.5rem;
    border-radius: 20px;
    margin-top: 0.25rem;
    width: fit-content;
}

.payment-badge.cicilan {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.payment-badge.spaylater {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

/* Cicilan Section */
.cicilan-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px dashed #e2e8f0;
}

.cicilan-section h3 {
    font-size: 1rem;
    margin-bottom: 1rem;
}

.cicilan-tenor {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.tenor-option {
    flex: 1;
    min-width: 120px;
    cursor: pointer;
}

.tenor-option input {
    display: none;
}

.tenor-content {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
    transition: all 0.3s;
}

.tenor-option:hover .tenor-content {
    border-color: var(--primary);
}

.tenor-option input:checked + .tenor-content {
    border-color: var(--primary);
    background: #f5f3ff;
}

.tenor-month {
    display: block;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.tenor-amount {
    font-size: 0.85rem;
    color: var(--primary);
    font-weight: 500;
}

.cicilan-summary {
    margin-top: 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-radius: 10px;
    display: none;
}

.cicilan-summary.active {
    display: block;
    animation: slideDown 0.3s ease;
}

/* Checkout Items */
.checkout-items {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.checkout-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 10px;
}

.checkout-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.item-info {
    flex: 1;
}

.item-info h4 {
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
}

.item-info p {
    font-size: 0.85rem;
    color: #64748b;
}

.item-total {
    font-weight: 600;
    color: var(--primary);
}

/* Checkout Summary */
.checkout-summary {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: var(--shadow);
    position: sticky;
    top: 100px;
}

.checkout-summary h3 {
    margin-bottom: 1.5rem;
    color: var(--dark);
}

.summary-rows {
    margin-bottom: 1.5rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    color: #64748b;
}

.summary-row.discount {
    color: var(--success);
}

.summary-total {
    display: flex;
    justify-content: space-between;
    padding-top: 1rem;
    border-top: 2px solid #e2e8f0;
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 1.5rem;
}

.btn-order {
    width: 100%;
    margin-bottom: 0.75rem;
    justify-content: center;
    padding: 1rem;
    font-size: 1.1rem;
}

.btn-back {
    width: 100%;
    justify-content: center;
}

@media (max-width: 968px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }
    
    .checkout-summary {
        position: static;
    }
}
</style>

<script>
function handlePaymentChange(radio) {
    const cicilanSection = document.getElementById('cicilan-options');
    const paymentType = radio.closest('.payment-option').dataset.type;
    
    if (paymentType === 'cicilan') {
        cicilanSection.style.display = 'block';
        cicilanSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        cicilanSection.style.display = 'none';
        document.querySelectorAll('input[name="cicilan_months"]').forEach(r => r.checked = false);
        document.getElementById('cicilan-summary').classList.remove('active');
    }
}

function calculateCicilan() {
    const total = <?php echo $total; ?>;
    const selectedTenor = document.querySelector('input[name="cicilan_months"]:checked');
    const summaryDiv = document.getElementById('cicilan-summary');
    
    if (selectedTenor) {
        const months = parseInt(selectedTenor.value);
        const perMonth = total / months;
        
        summaryDiv.innerHTML = `
            <p><strong>Cicilan ${months}x Bulan</strong></p>
            <p> Pembayaran per bulan: <strong>Rp ${formatNumber(perMonth)}</strong></p>
            <p>Total: Rp ${formatNumber(total)} (0% bunga)</p>
            <small class="text-muted">Pembayaran pertama akan diproses setelah konfirmasi pesanan</small>
        `;
        summaryDiv.classList.add('active');
    }
}

function formatNumber(num) {
    return num.toFixed(0).replace(/\\B(?=(\\d{3})+(?!\\d))/g, ".");
}
</script>

<?php
function getPaymentIcon($code) {
    $icons = [
        'bank_transfer' => 'university',
        'e_wallet' => 'wallet',
        'credit_card' => 'credit-card',
        'cicilan' => 'calendar-alt',
        'spaylater' => 'clock',
        'cod' => 'money-bill-wave'
    ];
    return $icons[$code] ?? 'money-check';
}

include 'footer.php';
?>
"""

with open('/mnt/kimi/output/checkout.php', 'w', encoding='utf-8') as f:
    f.write(checkout_php)

print("✅ File checkout.php berhasil dibuat!")
print("\n💳 Fitur Checkout:")
print("- Form alamat pengiriman")
print("- Pilihan metode pembayaran lengkap")
print("- Badge khusus untuk Cicilan & SPayLater")
print("- Opsi tenor cicilan (3/6/12 bulan)")
print("- Perhitungan cicilan otomatis")
print("- Ringkasan produk")
print("- Sticky summary card")
print("- Transaksi database dengan rollback jika error")
