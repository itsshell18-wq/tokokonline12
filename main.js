
main_js = "// main.js - Animasi & Fungsionalitas Lengkap

// Animasi tambah ke keranjang
function addToCartAnimation(element) {
    element.classList.add('cart-added');
    
    // Buat efek ripple
    const ripple = document.createElement('span');
    ripple.style.cssText = `
        position: absolute;
        background: rgba(255,255,255,0.6);
        border-radius: 50%;
        transform: scale(0);
        animation: ripple 0.6s ease-out;
        pointer-events: none;
    `;
    
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = (rect.width - size) / 2 + 'px';
    ripple.style.top = (rect.height - size) / 2 + 'px';
    
    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.appendChild(ripple);
    
    setTimeout(() => ripple.remove(), 600);
    setTimeout(() => element.classList.remove('cart-added'), 500);
}

// Animasi sukses pembayaran dengan confetti
function showPaymentSuccess(message = 'Pembayaran Berhasil!') {
    // Buat modal sukses
    const modal = document.createElement('div');
    modal.className = 'payment-success';
    modal.innerHTML = `
        <div class="success-icon"></div>
        <h2>${message}</h2>
        <p>Terima kasih telah berbelanja!</p>
        <button class="btn btn-primary" onclick="this.closest('.payment-success').remove()">OK</button>
    `;
    
    document.body.appendChild(modal);
    
    // Confetti effect
    createConfetti();
    
    // Auto remove setelah 3 detik
    setTimeout(() => {
        if (modal.parentNode) {
            modal.style.animation = 'fadeOut 0.3s ease forwards';
            setTimeout(() => modal.remove(), 300);
        }
    }, 3000);
}

// Confetti generator
function createConfetti() {
    const colors = ['#6366f1', '#ec4899', '#10b981', '#f59e0b', '#ef4444'];
    
    for (let i = 0; i < 50; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.cssText = `
            left: ${Math.random() * 100}vw;
            background: ${colors[Math.floor(Math.random() * colors.length)]};
            animation-duration: ${Math.random() * 2 + 2}s;
            animation-delay: ${Math.random() * 0.5}s;
        `;
        document.body.appendChild(confetti);
        
        setTimeout(() => confetti.remove(), 4000);
    }
}

// AJAX Add to Cart
async function addToCart(productId, button) {
    try {
        addToCartAnimation(button);
        button.innerHTML = '<span class="loading"></span> Menambahkan...';
        
        const response = await fetch('ajax/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId,
                quantity: 1
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            updateCartBadge(data.cart_count);
            showNotification('Produk ditambahkan ke keranjang!', 'success');
            
            // Animasi cart icon
            const cartIcon = document.querySelector('.cart-icon');
            if (cartIcon) {
                cartIcon.style.animation = 'none';
                setTimeout(() => cartIcon.style.animation = 'bounce 0.5s', 10);
            }
        } else {
            showNotification(data.message || 'Gagal menambahkan ke keranjang', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan', 'error');
    } finally {
        button.innerHTML = '<i class="icon-cart"></i> Tambah ke Keranjang';
    }
}

// Update cart badge
function updateCartBadge(count) {
    const badge = document.querySelector('.cart-badge');
    if (badge) {
        badge.textContent = count;
        badge.style.animation = 'none';
        setTimeout(() => badge.style.animation = 'bounce 1s infinite', 10);
    }
}

// Notifikasi toast
function showNotification(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        z-index: 9999;
        animation: slideInRight 0.3s ease;
        font-weight: 500;
    `;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Hitung cicilan otomatis
function calculateCicilan() {
    const totalElement = document.getElementById('total-amount');
    const monthsSelect = document.getElementById('cicilan-months');
    const resultElement = document.getElementById('cicilan-result');
    
    if (totalElement && monthsSelect && resultElement) {
        const total = parseFloat(totalElement.dataset.total);
        const months = parseInt(monthsSelect.value);
        
        if (months > 0) {
            const perMonth = total / months;
            resultElement.innerHTML = `
                <div class="cicilan-info">
                    <p>Cicilan ${months}x bulan</p>
                    <h3>${formatRupiah(perMonth)}/bulan</h3>
                    <small>Total: ${formatRupiah(total)}</small>
                </div>
            `;
            resultElement.style.display = 'block';
        } else {
            resultElement.style.display = 'none';
        }
    }
}

// Format rupiah
function formatRupiah(angka) {
    return 'Rp ' + angka.toFixed(0).replace(/\\B(?=(\\d{3})+(?!\\d))/g, ".");
}

// Modal functions
function openModal(id) {
    document.getElementById(id).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Confirm delete
function confirmDelete(message, callback) {
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-content">
            <h3>Konfirmasi Hapus</h3>
            <p>${message}</p>
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button class="btn" onclick="this.closest('.modal').remove()">Batal</button>
                <button class="btn btn-danger" id="confirm-delete">Hapus</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    document.getElementById('confirm-delete').addEventListener('click', () => {
        callback();
        modal.remove();
    });
}

// Live search
let searchTimeout;
function liveSearch(input, target) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const query = input.value.toLowerCase();
        const items = document.querySelectorAll(target);
        
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(query) ? '' : 'none';
        });
    }, 300);
}

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// Navbar scroll effect
window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
});

// Intersection Observer untuk animasi scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

document.querySelectorAll('.product-card, .stat-card, .timeline-item').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
});

// Form validation dengan animasi
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = '#ef4444';
            input.style.animation = 'shake 0.5s';
            
            setTimeout(() => {
                input.style.animation = '';
            }, 500);
        } else {
            input.style.borderColor = '#10b981';
        }
    });
    
    return isValid;
}

// Shake animation
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }
    
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    @keyframes fadeOut {
        to { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
    }
`;
document.head.appendChild(style);

// Image preview untuk upload
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.style.animation = 'fadeIn 0.3s';
        };
        reader.readAsDataURL(file);
    }
}

// Lazy loading images
document.addEventListener('DOMContentLoaded', () => {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
});

// Export functions untuk global access
window.addToCart = addToCart;
window.showPaymentSuccess = showPaymentSuccess;
window.calculateCicilan = calculateCicilan;
window.openModal = openModal;
window.closeModal = closeModal;
window.confirmDelete = confirmDelete;
window.liveSearch = liveSearch;
window.validateForm = validateForm;
window.previewImage = previewImage;
window.showNotification = showNotification;
""

with open('/mnt/kimi/output/main.js', 'w', encoding='utf-8') as f:
    f.write(main_js)

print("✅ File main.js berhasil dibuat!")
print("\n⚡ Fitur JavaScript:")
print("- Animasi tambah ke keranjang dengan ripple effect")
print("- Animasi sukses pembayaran + confetti")
print("- AJAX cart operations")
print("- Toast notifications")
print("- Perhitungan cicilan otomatis")
print("- Modal & confirm dialogs")
print("- Live search")
print("- Form validation dengan shake animation")
print("- Intersection Observer untuk scroll animations")
print("- Lazy loading images")
