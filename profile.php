
profile_php = """<?php
// profile.php - Halaman Profil Pengguna
require_once 'config.php';

// Cek login
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Ambil data user
$stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $full_name = trim($_POST['full_name'] ?? '');
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            
            if (empty($full_name) || empty($email)) {
                $error = 'Nama lengkap dan email wajib diisi';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Format email tidak valid';
            } else {
                try {
                    // Cek email sudah dipakai user lain
                    $stmt = db()->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $stmt->execute([$email, $user_id]);
                    if ($stmt->fetch()) {
                        $error = 'Email sudah digunakan oleh pengguna lain';
                    } else {
                        $stmt = db()->prepare("
                            UPDATE users 
                            SET full_name = ?, email = ?, phone = ?, address = ?, updated_at = NOW()
                            WHERE id = ?
                        ");
                        $stmt->execute([$full_name, $email, $phone, $address, $user_id]);
                        $success = 'Profil berhasil diperbarui';
                        
                        // Refresh data
                        $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $user = $stmt->fetch();
                    }
                } catch (PDOException $e) {
                    $error = 'Terjadi kesalahan sistem';
                }
            }
        }
        
        elseif ($action === 'change_password') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($current_password) || empty($new_password)) {
                $error = 'Semua field password wajib diisi';
            } elseif (strlen($new_password) < 6) {
                $error = 'Password baru minimal 6 karakter';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Password baru dan konfirmasi tidak cocok';
            } elseif (!password_verify($current_password, $user['password'])) {
                $error = 'Password saat ini salah';
            } else {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = db()->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $user_id]);
                $success = 'Password berhasil diubah';
            }
        }
        
        elseif ($action === 'upload_photo') {
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = $_FILES['profile_image']['type'];
                
                if (!in_array($file_type, $allowed)) {
                    $error = 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF';
                } else {
                    $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                    $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
                    $upload_path = 'uploads/profiles/';
                    
                    if (!is_dir($upload_path)) {
                        mkdir($upload_path, 0755, true);
                    }
                    
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path . $filename)) {
                        // Hapus foto lama
                        if ($user['profile_image'] && $user['profile_image'] !== 'default.jpg') {
                            @unlink($upload_path . $user['profile_image']);
                        }
                        
                        $stmt = db()->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                        $stmt->execute([$filename, $user_id]);
                        $success = 'Foto profil berhasil diperbarui';
                        
                        // Refresh data
                        $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $user = $stmt->fetch();
                    } else {
                        $error = 'Gagal mengupload file';
                    }
                }
            }
        }
    }
}

$page_title = 'Profil Saya - TokoKu';
include 'header.php';
?>

<div class="profile-page">
    <div class="profile-header">
        <h1><i class="fas fa-user-circle"></i> Profil Saya</h1>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="profile-grid">
        <!-- Sidebar Profile -->
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="profile-image-wrapper">
                    <img src="uploads/profiles/<?php echo $user['profile_image']; ?>" 
                         alt="Profile" 
                         class="profile-image-large"
                         onerror="this.src='assets/default-avatar.jpg'"
                         id="previewImage">
                    <label for="photoInput" class="change-photo-btn" title="Ganti Foto">
                        <i class="fas fa-camera"></i>
                    </label>
                </div>
                <h2 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <p class="profile-username">@<?php echo htmlspecialchars($user['username']); ?></p>
                <p class="profile-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                <p class="profile-phone"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone'] ?? '-'); ?></p>
                
                <div class="profile-stats">
                    <div class="profile-stat">
                        <span class="stat-number">
                            <?php
                            $stmt = db()->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
                            $stmt->execute([$user_id]);
                            echo $stmt->fetchColumn();
                            ?>
                        </span>
                        <span class="stat-label">Pesanan</span>
                    </div>
                    <div class="profile-stat">
                        <span class="stat-number">
                            <?php
                            $stmt = db()->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
                            $stmt->execute([$user_id]);
                            echo $stmt->fetchColumn();
                            ?>
                        </span>
                        <span class="stat-label">Keranjang</span>
                    </div>
                    <div class="profile-stat">
                        <span class="stat-number">0</span>
                        <span class="stat-label">Ulasan</span>
                    </div>
                </div>
                
                <form method="POST" action="" enctype="multipart/form-data" class="photo-form" id="photoForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                    <input type="hidden" name="action" value="upload_photo">
                    <input type="file" name="profile_image" id="photoInput" accept="image/*" style="display: none;">
                </form>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="profile-main">
            <!-- Edit Profile Form -->
            <div class="profile-section">
                <div class="section-header">
                    <h2><i class="fas fa-edit"></i> Edit Profil</h2>
                </div>
                
                <form method="POST" action="" class="profile-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small class="form-hint">Username tidak dapat diubah</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="full_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">No. Telepon</label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea name="address" class="form-control" rows="3" 
                                  placeholder="Masukkan alamat lengkap Anda..."><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Change Password Form -->
            <div class="profile-section">
                <div class="section-header">
                    <h2><i class="fas fa-lock"></i> Ganti Password</h2>
                </div>
                
                <form method="POST" action="" class="password-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label class="form-label">Password Saat Ini</label>
                        <div class="input-group">
                            <input type="password" name="current_password" class="form-control" required>
                            <button type="button" class="toggle-password" onclick="togglePassword(this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="new_password" class="form-control" required>
                            <small class="form-hint">Minimal 6 karakter</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key"></i> Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.profile-page {
    max-width: 1000px;
    margin: 0 auto;
}

.profile-header {
    margin-bottom: 2rem;
}

.profile-header h1 {
    color: var(--dark);
}

.profile-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
}

/* Sidebar */
.profile-sidebar {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.profile-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: var(--shadow);
    text-align: center;
}

.profile-image-wrapper {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto 1.5rem;
}

.profile-image-large {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    border: 4px solid #e0e7ff;
    transition: all 0.3s;
}

.profile-image-wrapper:hover .profile-image-large {
    border-color: var(--primary);
}

.change-photo-btn {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 40px;
    height: 40px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    border: 3px solid white;
}

.change-photo-btn:hover {
    background: var(--primary-dark);
    transform: scale(1.1);
}

.profile-name {
    font-size: 1.3rem;
    color: var(--dark);
    margin-bottom: 0.25rem;
}

.profile-username {
    color: #64748b;
    margin-bottom: 1rem;
}

.profile-email,
.profile-phone {
    color: #64748b;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.profile-stats {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

.profile-stat {
    text-align: center;
}

.profile-stat .stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}

.profile-stat .stat-label {
    font-size: 0.85rem;
    color: #64748b;
}

/* Main Content */
.profile-main {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.profile-section {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: var(--shadow);
}

.section-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.section-header h2 {
    font-size: 1.2rem;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.profile-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-actions {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

.btn-warning {
    background: var(--warning);
    color: white;
}

.btn-warning:hover {
    background: #d97706;
}

.input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.input-group .form-control {
    padding-right: 40px;
}

.toggle-password {
    position: absolute;
    right: 10px;
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
}

@media (max-width: 768px) {
    .profile-grid {
        grid-template-columns: 1fr;
    }
    
    .profile-sidebar {
        position: static;
    }
    
    .profile-form .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Preview image sebelum upload
document.getElementById('photoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
        };
        reader.readAsDataURL(file);
        
        // Auto submit form
        document.getElementById('photoForm').submit();
    }
});

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

with open('/mnt/kimi/output/profile.php', 'w', encoding='utf-8') as f:
    f.write(profile_php)

print("✅ File profile.php berhasil dibuat!")
print("\n👤 Fitur Profil Pengguna:")
print("- Upload foto profil dengan preview")
print("- Edit data profil (nama, email, telepon, alamat)")
print("- Ganti password dengan validasi")
print("- Statistik pengguna (pesanan, keranjang, ulasan)")
print("- Sidebar sticky dengan info profil")
print("- Toggle password visibility")
print("- Auto-submit form foto")
