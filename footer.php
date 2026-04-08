
footer_php = """<?php
// footer.php - Template Footer
?>
    </div><!-- End Container -->
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3><i class="fas fa-shopping-bag"></i> TokoKu</h3>
                <p>Toko online terpercaya dengan berbagai produk berkualitas. Belanja mudah, aman, dan nyaman.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Menu Cepat</h4>
                <ul>
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="products.php">Produk</a></li>
                    <li><a href="cart.php">Keranjang</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Layanan</h4>
                <ul>
                    <li><a href="#"><i class="fas fa-truck"></i> Pengiriman</a></li>
                    <li><a href="#"><i class="fas fa-undo"></i> Pengembalian</a></li>
                    <li><a href="#"><i class="fas fa-shield-alt"></i> Garansi</a></li>
                    <li><a href="#"><i class="fas fa-headset"></i> Bantuan</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Metode Pembayaran</h4>
                <div class="payment-icons">
                    <i class="fas fa-university" title="Transfer Bank"></i>
                    <i class="fas fa-wallet" title="E-Wallet"></i>
                    <i class="fas fa-credit-card" title="Kartu Kredit"></i>
                    <i class="fas fa-hand-holding-usd" title="COD"></i>
                    <span class="cicilan-badge">Cicilan 0%</span>
                    <span class="spaylater-badge">SPayLater</span>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> TokoKu. All rights reserved.</p>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="main.js"></script>
    
    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('active');
        }
        
        // Dropdown toggle
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                toggle.parentElement.classList.toggle('active');
            });
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('active'));
            }
        });
    </script>
</body>
</html>
"""

# Tambahkan CSS untuk footer dan dropdown
footer_css = """
/* Dropdown Styles */
.dropdown {
    position: relative;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 10px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1000;
    list-style: none;
    padding: 0.5rem 0;
}

.dropdown.active .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-menu li a {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.8rem 1.2rem;
    color: var(--dark);
    text-decoration: none;
    transition: background 0.2s;
}

.dropdown-menu li a:hover {
    background: #f8fafc;
}

.dropdown-menu .divider {
    height: 1px;
    background: #e2e8f0;
    margin: 0.5rem 0;
}

.text-danger {
    color: var(--danger) !important;
}

/* Mobile Menu */
.mobile-menu-btn {
    display: none;
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--dark);
}

.mobile-menu {
    display: none;
    position: fixed;
    top: 70px;
    left: 0;
    right: 0;
    background: white;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    padding: 1rem;
    z-index: 999;
}

.mobile-menu.active {
    display: block;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.mobile-menu ul {
    list-style: none;
}

.mobile-menu li {
    border-bottom: 1px solid #e2e8f0;
}

.mobile-menu li a {
    display: block;
    padding: 1rem;
    color: var(--dark);
    text-decoration: none;
}

/* Footer */
.footer {
    background: var(--dark);
    color: white;
    padding: 3rem 0 1rem;
    margin-top: 4rem;
}

.footer-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.footer-section h3,
.footer-section h4 {
    margin-bottom: 1rem;
    color: white;
}

.footer-section ul {
    list-style: none;
}

.footer-section ul li {
    margin-bottom: 0.5rem;
}

.footer-section a {
    color: #94a3b8;
    text-decoration: none;
    transition: color 0.3s;
}

.footer-section a:hover {
    color: white;
}

.social-links {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.social-links a {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.social-links a:hover {
    background: var(--primary);
    transform: translateY(-3px);
}

.payment-icons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.payment-icons i {
    font-size: 1.5rem;
    color: #94a3b8;
}

.footer-bottom {
    text-align: center;
    padding-top: 2rem;
    margin-top: 2rem;
    border-top: 1px solid rgba(255,255,255,0.1);
    color: #94a3b8;
}

@media (max-width: 768px) {
    .nav-links {
        display: none;
    }
    
    .mobile-menu-btn {
        display: block;
    }
}
"""

with open('/mnt/kimi/output/footer.php', 'w', encoding='utf-8') as f:
    f.write(footer_php)

# Append footer CSS ke style.css
with open('/mnt/kimi/output/style.css', 'a', encoding='utf-8') as f:
    f.write(footer_css)

print("✅ File footer.php berhasil dibuat!")
print("✅ CSS untuk footer dan dropdown ditambahkan ke style.css!")
