
products_php = """<?php
// products.php - Halaman Semua Produk
require_once 'config.php';

// Filter dan sorting
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';
$search = $_GET['search'] ?? '';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Build query
$where = "WHERE p.is_active = 1";
$params = [];

if ($category) {
    $where .= " AND c.slug = ?";
    $params[] = $category;
}

if ($price_min !== '') {
    $where .= " AND p.price >= ?";
    $params[] = (float)$price_min;
}

if ($price_max !== '') {
    $where .= " AND p.price <= ?";
    $params[] = (float)$price_max;
}

if ($search) {
    $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Sorting
$order_by = "p.created_at DESC";
switch ($sort) {
    case 'price_low':
        $order_by = "p.price ASC";
        break;
    case 'price_high':
        $order_by = "p.price DESC";
        break;
    case 'name':
        $order_by = "p.name ASC";
        break;
}

// Count total
$count_sql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id $where";
$stmt = db()->prepare($count_sql);
$stmt->execute($params);
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Get products
$sql = "
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $where
    ORDER BY $order_by
    LIMIT $limit OFFSET $offset
";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$stmt = db()->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

$page_title = 'Produk - TokoKu';
include 'header.php';
?>

<div class="products-page">
    <div class="products-header">
        <h1><i class="fas fa-box"></i> Semua Produk</h1>
        <p>Temukan produk berkualitas dengan harga terbaik</p>
    </div>
    
    <div class="products-layout">
        <!-- Sidebar Filters -->
        <aside class="filters-sidebar">
            <div class="filter-card">
                <h3><i class="fas fa-filter"></i> Filter</h3>
                
                <form method="GET" class="filter-form">
                    <!-- Search -->
                    <div class="filter-group">
                        <label>Cari Produk</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Nama produk..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <!-- Category -->
                    <div class="filter-group">
                        <label>Kategori</label>
                        <select name="category" class="form-control">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['slug']; ?>" 
                                    <?php echo $category === $cat['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Price Range -->
                    <div class="filter-group">
                        <label>Rentang Harga</label>
                        <div class="price-inputs">
                            <input type="number" name="price_min" class="form-control" 
                                   placeholder="Min" value="<?php echo $price_min; ?>">
                            <span>-</span>
                            <input type="number" name="price_max" class="form-control" 
                                   placeholder="Max" value="<?php echo $price_max; ?>">
                        </div>
                    </div>
                    
                    <!-- Sort -->
                    <div class="filter-group">
                        <label>Urutkan</label>
                        <select name="sort" class="form-control">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Harga: Rendah ke Tinggi</option>
                            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Harga: Tinggi ke Rendah</option>
                            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Nama: A-Z</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-filter"></i> Terapkan Filter
                    </button>
                    
                    <?php if ($category || $price_min || $price_max || $search): ?>
                    <a href="products.php" class="btn btn-outline btn-block">
                        <i class="fas fa-times"></i> Reset Filter
                    </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Promo Banner -->
            <div class="promo-card">
                <h4>Cicilan 0%</h4>
                <p>Nikmati kemudahan cicilan tanpa bunga untuk pembelian diatas Rp 1 juta</p>
                <a href="#" class="btn btn-sm btn-primary">Info Lengkap</a>
            </div>
        </aside>
        
        <!-- Products Grid -->
        <div class="products-content">
            <div class="products-toolbar">
                <span class="results-count">Menampilkan <?php echo count($products); ?> dari <?php echo $total_rows; ?> produk</span>
                
                <div class="view-toggle">
                    <button class="view-btn active" data-view="grid">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="view-btn" data-view="list">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
            
            <?php if (empty($products)): ?>
                <div class="empty-products">
                    <i class="fas fa-search"></i>
                    <h3>Tidak Ada Produk</h3>
                    <p>Coba ubah filter atau kata kunci pencarian Anda</p>
                </div>
            <?php else: ?>
                <div class="products-grid" id="productsGrid">
                    <?php foreach ($products as $product): ?>
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
                                    <i class="fas fa-cart-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                        
                        <div class="product-info">
                            <span class="category-tag"><?php echo htmlspecialchars($product['category_name']); ?></span>
                            <h3 class="product-title">
                                <a href="product.php?slug=<?php echo $product['slug']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            <p class="product-description">
                                <?php echo substr(htmlspecialchars($product['description']), 0, 60); ?>...
                            </p>
                            <div class="product-footer">
                                <span class="product-price"><?php echo formatRupiah($product['price']); ?></span>
                                
                                <?php if ($product['price'] >= 1000000): ?>
                                <span class="cicilan-badge-mini">Cicilan 0%</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>&category=<?php echo $category; ?>&sort=<?php echo $sort; ?>&price_min=<?php echo $price_min; ?>&price_max=<?php echo $price_max; ?>&search=<?php echo $search; ?>" 
                           class="btn btn-outline">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="page-active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&category=<?php echo $category; ?>&sort=<?php echo $sort; ?>&price_min=<?php echo $price_min; ?>&price_max=<?php echo $price_max; ?>&search=<?php echo $search; ?>" class="page-number">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?>&category=<?php echo $category; ?>&sort=<?php echo $sort; ?>&price_min=<?php echo $price_min; ?>&price_max=<?php echo $price_max; ?>&search=<?php echo $search; ?>" 
                           class="btn btn-outline">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.products-page {
    max-width: 1400px;
    margin: 0 auto;
}

.products-header {
    text-align: center;
    margin-bottom: 2rem;
}

.products-header h1 {
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.products-header p {
    color: #64748b;
}

.products-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 2rem;
}

/* Sidebar */
.filters-sidebar {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.filter-card,
.promo-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: var(--shadow);
}

.filter-card h3 {
    margin-bottom: 1.5rem;
    color: var(--dark);
}

.filter-group {
    margin-bottom: 1.5rem;
}

.filter-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--dark);
}

.price-inputs {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.price-inputs input {
    flex: 1;
}

.price-inputs span {
    color: #64748b;
}

.btn-block {
    width: 100%;
    justify-content: center;
    margin-bottom: 0.5rem;
}

.promo-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-align: center;
}

.promo-card h4 {
    margin-bottom: 0.5rem;
}

.promo-card p {
    font-size: 0.9rem;
    margin-bottom: 1rem;
    opacity: 0.9;
}

/* Products Content */
.products-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: white;
    border-radius: 10px;
    box-shadow: var(--shadow);
}

.results-count {
    color: #64748b;
}

.view-toggle {
    display: flex;
    gap: 0.5rem;
}

.view-btn {
    width: 40px;
    height: 40px;
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.view-btn.active,
.view-btn:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.cicilan-badge-mini {
    display: inline-block;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.empty-products {
    text-align: center;
    padding: 4rem;
    background: white;
    border-radius: 15px;
    box-shadow: var(--shadow);
}

.empty-products i {
    font-size: 4rem;
    color: #e2e8f0;
    margin-bottom: 1rem;
}

.empty-products h3 {
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.empty-products p {
    color: #64748b;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    margin-top: 2rem;
}

.page-number,
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

.page-number:hover {
    background: #f8fafc;
}

.page-active {
    background: var(--primary);
    color: white;
}

@media (max-width: 968px) {
    .products-layout {
        grid-template-columns: 1fr;
    }
    
    .filters-sidebar {
        position: static;
        order: 2;
    }
    
    .products-content {
        order: 1;
    }
}
</style>

<script>
// View toggle
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const view = this.dataset.view;
        const grid = document.getElementById('productsGrid');
        
        if (view === 'list') {
            grid.style.gridTemplateColumns = '1fr';
        } else {
            grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(280px, 1fr))';
        }
    });
});
</script>

<?php include 'footer.php'; ?>
"""

with open('/mnt/kimi/output/products.php', 'w', encoding='utf-8') as f:
    f.write(products_php)

print("✅ File products.php berhasil dibuat!")
print("\n📦 Fitur Halaman Produk:")
print("- Filter sidebar (kategori, harga, search)")
print("- Sorting (terbaru, harga, nama)")
print("- Grid/List view toggle")
print("- Pagination")
print("- Badge cicilan untuk produk >1jt")
print("- Quick add to cart")
print("- Sticky sidebar filters")
print("- Promo banner cicilan")
