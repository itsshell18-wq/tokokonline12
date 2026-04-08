
login_php = """<?php
// login.php - Halaman Login
require_once 'config.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifikasi CSRF
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid';
    } else {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = 'Email dan password wajib diisi';
        } else {
            try {
                $stmt = db()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'] ?? 'user';
                    
                    // Regenerate session ID untuk keamanan
                    session_regenerate_id(true);
                    
                    flashMessage('success', 'Selamat datang, ' . $user['full_name'] . '!');
                    redirect('dashboard.php');
                } else {
                    $error = 'Email atau password salah';
                }
            } catch (PDOException $e) {
                $error = 'Terjadi kesalahan sistem';
                error_log($e->getMessage());
            }
        }
    }
}

$page_title = 'Login - TokoKu';
include 'header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
            <p>Masuk ke akun Anda untuk melanjutkan belanja</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="auth-form" onsubmit="return validateForm(this)">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
            
            <div class="form-group">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    <button type="button" class="toggle-password" onclick="togglePassword(this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-options">
                <label class="checkbox">
                    <input type="checkbox" name="remember"> Ingat saya
                </label>
                <a href="forgot-password.php">Lupa password?</a>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <div class="auth-footer">
            <p>Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
        </div>
    </div>
</div>

<style>
.auth-container {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.auth-card {
    background: white;
    border-radius: 20px;
    box-shadow: var(--shadow);
    width: 100%;
    max-width: 450px;
    padding: 2.5rem;
    animation: slideUp 0.5s ease;
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header h2 {
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: #64748b;
}

.input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.input-group i:first-child {
    position: absolute;
    left: 1rem;
    color: #94a3b8;
}

.input-group .form-control {
    padding-left: 2.5rem;
}

.toggle-password {
    position: absolute;
    right: 1rem;
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.btn-block {
    width: 100%;
    justify-content: center;
}

.auth-footer {
    text-align: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

.auth-footer a {
    color: var(--primary);
    font-weight: 600;
}
</style>

<script>
function togglePassword(btn) {
    const input = btn.parentElement.querySelector('input');
    const icon = btn.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php include 'footer.php'; ?>
"""

with open('/mnt/kimi/output/login.php', 'w', encoding='utf-8') as f:
    f.write(login_php)

print("✅ File login.php berhasil dibuat!")
print("\n🔐 Fitur Login:")
print("- CSRF protection")
print("- Password hashing dengan password_verify")
print("- Session regeneration untuk keamanan")
print("- Input sanitization")
print("- Error handling")
print("- Toggle password visibility")
