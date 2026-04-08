
header_php = """<?php
// header.php - Template Header Utama
require_once 'config.php';

// Cek session dan set variabel user
$current_user = null;
$cart_count = 0;

if (isLoggedIn()) {
    $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch();
    
    // Hitung jumlah item di keranjang
    $stmt = db()->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_result = $stmt->fetch();
    $cart_count = $cart_result['total'] ?? 0;
}

// Set title default
$page_title = $page_title ?? 'Toko Online - Belanja Mudah & Aman';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generateCSRF(); ?>">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="style.css">
    
    <!-- Font Awesome untuk icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/favicon.png">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-shopping-bag"></i> TokoKu
            </a>
            
            <ul class="nav-links">
                <li><a href="index.php"><i class="fas fa-home"></i> Beranda</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Produk</a></li>
                
                <?php if (isLoggedIn()): ?>
                    <li>
                        <a href="cart.php" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-badge"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle">
                            <i class="fas fa-user-circle"></i> 
                            <?php echo htmlspecialchars($current_user['username']); ?>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><a href="profile.php"><i class="fas fa-user"></i> Profil</a></li>
                            <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Pesanan Saya</a></li>
                            <li><a href="history.php"><i class="fas fa-history"></i> Riwayat</a></li>
                            <li class="divider"></li>
                            <li><a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="register.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Daftar</a></li>
                <?php endif; ?>
            </ul>
            
            <!-- Mobile Menu Button -->
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <ul>
            <li><a href="index.php"><i class="fas fa-home"></i> Beranda</a></li>
            <li><a href="products.php"><i class="fas fa-box"></i> Produk</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Keranjang (<?php echo $cart_count; ?>)</a></li>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profil</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Pesanan</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="register.php"><i class="fas fa-user-plus"></i> Daftar</a></li>
            <?php endif; ?>
        </ul>
    </div>
    
    <!-- Flash Messages -->
    <?php echo showFlash(); ?>
    
    <!-- Main Content Container -->
    <div class="container">
"""

with open('/mnt/kimi/output/header.php', 'w', encoding='utf-8') as f:
    f.write(header_php)

print("✅ File header.php berhasil dibuat!")
print("\n📋 Fitur Header:")
print("- Navbar responsive dengan glassmorphism effect")
print("- Dropdown menu untuk user yang login")
print("- Badge keranjang real-time")
print("- Mobile menu toggle")
print("- CSRF token di meta tag")
print("- Flash messages system")
