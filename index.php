
index_php = """<?php
// index.php - Halaman Utama
require_once 'config.php';

// Ambil produk unggulan
$stmt = db()->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1 
    ORDER BY p.created_at DESC 
    LIMIT 8
");
$featured_products = $stmt->fetchAll();

// Ambil kategori
$stmt = db()->query("SELECT * FROM categories LIMIT 6");
$categories = $stmt->fetchAll();

$page_title = 'TokoKu - Belanja Online Mudah & Aman';
include 'header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Selamat Datang di TokoKu</h1>
        <p>Belanja online dengan berbagai produk berkualitas. Nikmati kemudahan pembayaran dengan Cicilan 0% dan SPayLater!</p>
        <div class="hero-buttons">
            <a href="products.php" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag"></i> Mulai Belanja
            </a>
            <a href="#categories" class="btn btn-outline btn-lg">
                <i class="fas fa-th-large"></i> Lihat Kategori
            </a>
        </div>
    </div>
    <div class="hero-stats">
        <div class="stat">
            <h3>10K+</h3>
            <p>Produk</p>
        </div>
        <div class="stat">
            <h3>50K+</h3>
            <p>Pelanggan</p>
        </div>
        <div class="stat">
            <h3>4.9</h3>
            <p>Rating</p>
        </div>
    </div>
</section>

<!-- Kategori Section -->
<section id="categories" class="section">
    <h2 class="section-title">
        <i class="fas fa-th-large"></i> Kategori Produk
    </h2>
    <div class="categories-grid">
        <?php foreach ($categories as $cat): ?>
        <a href="products.php?category=<?php echo $cat['slug']; ?>" class="category-card">
            <div class="category-icon">
                <i class="fas fa-<?php echo getCategoryIcon($cat['slug']); ?>"></i>
            </div>
            <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
            <p><?php echo htmlspecialchars($cat['description']); ?></p>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Produk Unggulan -->
<section class="section">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-fire"></i> Produk Terbaru
        </h2>
        <a href="products.php" class="btn btn-outline">
            Lihat Semua <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    
    <div class="products-grid">
        <?php foreach ($featured_products as $product): ?>
        <div class="product-card" data-aos="fade-up">
            <div class="product-image-container">
                <img src="uploads/products/<?php echo $product['image']; ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     class="product-image"
                     onerror="this.src='assets/default-product.jpg'">
                <?php if ($product['stock'] < 10): ?>
                    <span class="stock-badge low">Stok Menipis</span>
                <?php endif; ?>
                <div class="product-overlay">
                    <button class="btn btn-primary" onclick="addToCart(<?php echo $product['id']; ?>, this)">
                        <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                    </button>
                </div>
            </div>
            <div class="product-info">
                <span class="category-tag"><?php echo htmlspecialchars($product['category_name']); ?></span>
                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="product-description"><?php echo substr(htmlspecialchars($product['description']), 0, 60); ?>...</p>
                <div class="product-footer">
                    <span class="product-price"><?php echo formatRupiah($product['price']); ?></span>
                    <span class="stock-info">
                        <i class="fas fa-box"></i> <?php echo $product['stock']; ?> tersisa
                    </span>
                </div>
                
                <!-- Info Cicilan -->
                <?php if ($product['price'] >= 1000000): ?>
                <div class="cicilan-info-mini">
                    <span class="cicilan-badge">Cicilan 0%</span>
                    <small>mulai <?php echo formatRupiah($product['price'] / 12); ?>/bln</small>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Fitur Section -->
<section class="features-section">
    <h2 class="section-title text-center">Kenapa Memilih TokoKu?</h2>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3>Aman & Terpercaya</h3>
            <p>Sistem pembayaran aman dengan enkripsi SSL 256-bit</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-percentage"></i>
            </div>
            <h3>Cicilan 0%</h3>
            <p>Nikmati kemudahan cicilan tanpa bunga hingga 12 bulan</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <h3>SPayLater</h3>
            <p>Belanja sekarang bayar nanti dengan SPayLater</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-shipping-fast"></i>
            </div>
            <h3>Pengiriman Cepat</h3>
            <p>Gratis ongkir untuk pembelian diatas Rp 500.000</p>
        </div>
    </div>
</section>

<style>
.hero {
    position: relative;
    overflow: hidden;
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 600px;
}

.hero-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-lg {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}

.btn-outline {
    background: transparent;
    border: 2px solid white;
    color: white;
}

.btn-outline:hover {
    background: white;
    color: var(--primary);
}

.hero-stats {
    display: flex;
    gap: 3rem;
    margin-top: 3rem;
    position: relative;
    z-index: 2;
}

.hero-stats .stat h3 {
    font-size: 2.5rem;
    font-weight: 700;
}

.hero-stats .stat p {
    opacity: 0.9;
}

.section {
    margin: 4rem 0;
}

.section-title {
    font-size: 2rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title i {
    color: var(--primary);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.text-center {
    text-align: center;
    justify-content: center;
}

/* Categories */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.category-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    text-decoration: none;
    color: var(--dark);
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}

.category-icon {
    width: 80px;
    height: 80px;
    background: var(--gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2rem;
    color: white;
}

.category-card h3 {
    margin-bottom: 0.5rem;
}

.category-card p {
    color: #64748b;
    font-size: 0.9rem;
}

/* Product Card Enhancements */
.product-image-container {
    position: relative;
    overflow: hidden;
}

.stock-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 10;
}

.stock-badge.low {
    background: var(--danger);
    color: white;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.product-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    padding: 2rem 1rem 1rem;
    opacity: 0;
    transition: opacity 0.3s;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.category-tag {
    display: inline-block;
    background: #e0e7ff;
    color: var(--primary);
    padding: 0.2rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    margin-bottom: 0.5rem;
}

.product-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

.stock-info {
    font-size: 0.85rem;
    color: #64748b;
}

.cicilan-info-mini {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px dashed #e2e8f0;
}

/* Features Section */
.features-section {
    background: white;
    border-radius: 20px;
    padding: 3rem;
    margin: 4rem 0;
    box-shadow: var(--shadow);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.feature-card {
    text-align: center;
    padding: 2rem;
    border-radius: 15px;
    background: #f8fafc;
    transition: all 0.3s;
}

.feature-card:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-5px);
}

.feature-icon {
    width: 70px;
    height: 70px;
    background: var(--gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.8rem;
    color: white;
}

.feature-card:hover .feature-icon {
    background: white;
    color: var(--primary);
}

.feature-card h3 {
    margin-bottom: 0.5rem;
}

.feature-card p {
    color: #64748b;
    font-size: 0.9rem;
}

.feature-card:hover p {
    color: rgba(255,255,255,0.9);
}

@media (max-width: 768px) {
    .hero-stats {
        gap: 1.5rem;
    }
    
    .hero-stats .stat h3 {
        font-size: 1.8rem;
    }
    
    .hero-buttons {
        flex-direction: column;
    }
    
    .section-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}
</style>

<?php
// Helper function untuk icon kategori
function getCategoryIcon($slug) {
    $icons = [
        'elektronik' => 'laptop',
        'fashion' => 'tshirt',
        'makanan' => 'utensils',
        'rumah-tangga' => 'couch',
        'default' => 'box'
    ];
    return $icons[$slug] ?? $icons['default'];
}

include 'footer.php';
?>
"""

with open('/mnt/kimi/output/index.php', 'w', encoding='utf-8') as f:
    f.write(index_php)

print("✅ File index.php berhasil dibuat!")
print("\n🏠 Fitur Homepage:")
print("- Hero section dengan animasi")
print("- Statistik toko")
print("- Grid kategori produk")
print("- Produk unggulan dengan badge stok")
print("- Quick add to cart dengan animasi")
print("- Info cicilan untuk produk >1jt")
print("- Features section dengan hover effects")
