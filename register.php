
register_php = """<?php
// register.php - Halaman Registrasi
require_once 'config.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifikasi CSRF
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validasi
        if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
            $error = 'Semua field wajib diisi';
        } elseif (strlen($username) < 3) {
            $error = 'Username minimal 3 karakter';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter';
        } elseif ($password !== $confirm_password) {
            $error = 'Password dan konfirmasi password tidak cocok';
        } else {
            try {
                // Cek username sudah ada
                $stmt = db()->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                
                if ($stmt->fetch()) {
                    $error = 'Username atau email sudah terdaftar';
                } else {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert user baru
                    $stmt = db()->prepare("
                        INSERT INTO users (username, email, password, full_name, phone) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$username, $email, $hashed_password, $full_name, $phone]);
                    
                    $success = 'Registrasi berhasil! Silakan login.';
                }
            } catch (PDOException $e) {
                $error = 'Terjadi kesalahan sistem';
                error_log($e->getMessage());
            }
        }
    }
}

$page_title = 'Daftar - TokoKu';
include 'header.php';
?>

<div class="auth-container">
    <div class="auth-card" style="max-width: 500px;">
        <div class="auth-header">
            <h2><i class="fas fa-user-plus"></i> Daftar Akun</h2>
            <p>Buat akun baru untuk mulai berbelanja</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="auth-form" onsubmit="return validateForm(this)">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" class="form-control" placeholder="username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <div class="input-group">
                    <i class="fas fa-id-card"></i>
                    <input type="text" name="full_name" class="form-control" placeholder="Nama lengkap Anda" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">No. Telepon</label>
                <div class="input-group">
                    <i class="fas fa-phone"></i>
                    <input type="tel" name="phone" class="form-control" placeholder="08123456789">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <small class="form-hint">Minimal 6 karakter</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-user-plus"></i> Daftar Sekarang
            </button>
        </form>
        
        <div class="auth-footer">
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
</div>

<style>
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-hint {
    color: #64748b;
    font-size: 0.85rem;
    margin-top: 0.25rem;
    display: block;
}

@media (max-width: 480px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'footer.php'; ?>
"""

with open('/mnt/kimi/output/register.php', 'w', encoding='utf-8') as f:
    f.write(register_php)

print("✅ File register.php berhasil dibuat!")
print("\n📝 Fitur Registrasi:")
print("- Validasi input lengkap")
print("- Cek duplikasi username/email")
print("- Password hashing")
print("- CSRF protection")
print("- Form 2 kolom untuk desktop")
