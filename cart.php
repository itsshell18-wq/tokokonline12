
# 4. checkout.html - Halaman Checkout dengan Cicilan & SPayLater
checkout_html = """<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - TokoKu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #ec4899;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: var(--dark);
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 1rem 0;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        h1 {
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
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
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
        
        .payment-option.selected {
            border-color: var(--primary);
            background: #f5f3ff;
        }
        
        .payment-option input {
            margin-right: 1rem;
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
        
        .payment-name {
            font-weight: 600;
            color: var(--dark);
        }
        
        .payment-badge {
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            margin-left: auto;
        }
        
        .badge-cicilan {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .badge-spaylater {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }
        
        /* Cicilan Section */
        .cicilan-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px dashed #e2e8f0;
            display: none;
        }
        
        .cicilan-section.active {
            display: block;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
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
        
        .item-qty {
            font-size: 0.85rem;
            color: #64748b;
        }
        
        .item-total {
            font-weight: 600;
            color: var(--primary);
        }
        
        /* Summary Sidebar */
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
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
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
        
        .summary-row.total {
            padding-top: 1rem;
            border-top: 2px solid #e2e8f0;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark);
            margin: 1rem 0;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: var(--gradient);
            color: white;
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            width: 100%;
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
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.html" class="logo"><i class="fas fa-shopping-bag"></i> TokoKu</a>
        </div>
    </nav>

    <div class="container">
        <h1><i class="fas fa-credit-card"></i> Checkout</h1>
        
        <form id="checkoutForm" onsubmit="return processCheckout(event)">
            <div class="checkout-grid">
                <div class="checkout-left">
                    <!-- Alamat -->
                    <div class="checkout-section">
                        <h2><i class="fas fa-map-marker-alt"></i> Alamat Pengiriman</h2>
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" id="fullName" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. Telepon</label>
                            <input type="tel" id="phone" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alamat Lengkap</label>
                            <textarea id="address" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    
                    <!-- Metode Pembayaran -->
                    <div class="checkout-section">
                        <h2><i class="fas fa-wallet"></i> Metode Pembayaran</h2>
                        
                        <div class="payment-methods">
                            <label class="payment-option" onclick="selectPayment('transfer', this)">
                                <input type="radio" name="payment" value="transfer" checked>
                                <div class="payment-content">
                                    <div class="payment-icon"><i class="fas fa-university"></i></div>
                                    <span class="payment-name">Transfer Bank</span>
                                </div>
                            </label>
                            
                            <label class="payment-option" onclick="selectPayment('ewallet', this)">
                                <input type="radio" name="payment" value="ewallet">
                                <div class="payment-content">
                                    <div class="payment-icon"><i class="fas fa-wallet"></i></div>
                                    <span class="payment-name">E-Wallet</span>
                                </div>
                            </label>
                            
                            <label class="payment-option" onclick="selectPayment('cicilan', this)">
                                <input type="radio" name="payment" value="cicilan">
                                <div class="payment-content">
                                    <div class="payment-icon"><i class="fas fa-calendar-alt"></i></div>
                                    <span class="payment-name">Cicilan 0%</span>
                                    <span class="payment-badge badge-cicilan">Cicilan</span>
                                </div>
                            </label>
                            
                            <label class="payment-option" onclick="selectPayment('spaylater', this)">
                                <input type="radio" name="payment" value="spaylater">
                                <div class="payment-content">
                                    <div class="payment-icon"><i class="fas fa-clock"></i></div>
                                    <span class="payment-name">SPayLater</span>
                                    <span class="payment-badge badge-spaylater">PayLater</span>
                                </div>
                            </label>
                            
                            <label class="payment-option" onclick="selectPayment('cod', this)">
                                <input type="radio" name="payment" value="cod">
                                <div class="payment-content">
                                    <div class="payment-icon"><i class="fas fa-money-bill-wave"></i></div>
                                    <span class="payment-name">COD (Bayar di Tempat)</span>
                                </div>
                            </label>
                        </div>
                        
                        <!-- Cicilan Options -->
                        <div id="cicilanOptions" class="cicilan-section">
                            <h3>Pilih Tenor Cicilan</h3>
                            <div class="cicilan-tenor">
                                <label class="tenor-option">
                                    <input type="radio" name="tenor" value="3" onchange="calculateCicilan()">
                                    <div class="tenor-content">
                                        <span class="tenor-month">3 Bulan</span>
                                        <span class="tenor-amount" id="tenor3">Rp 0/bln</span>
                                    </div>
                                </label>
                                <label class="tenor-option">
                                    <input type="radio" name="tenor" value="6" onchange="calculateCicilan()">
                                    <div class="tenor-content">
                                        <span class="tenor-month">6 Bulan</span>
                                        <span class="tenor-amount" id="tenor6">Rp 0/bln</span>
                                    </div>
                                </label>
                                <label class="tenor-option">
                                    <input type="radio" name="tenor" value="12" onchange="calculateCicilan()" checked>
                                    <div class="tenor-content">
                                        <span class="tenor-month">12 Bulan</span>
                                        <span class="tenor-amount" id="tenor12">Rp 0/bln</span>
                                    </div>
                                </label>
                            </div>
                            <div id="cicilanSummary" class="cicilan-summary active">
                                <p><strong>Detail Cicilan</strong></p>
                                <p>Pembayaran per bulan: <strong id="cicilanPerMonth">Rp 0</strong></p>
                                <p>Total: <span id="cicilanTotal">Rp 0</span> (0% bunga)</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items -->
                    <div class="checkout-section">
                        <h2><i class="fas fa-shopping-bag"></i> Produk Dipesan</h2>
                        <div class="checkout-items" id="checkoutItems">
                            <!-- Items loaded by JS -->
                        </div>
                    </div>
                </div>
                
                <!-- Summary -->
                <div class="checkout-right">
                    <div class="checkout-summary">
                        <h3>Ringkasan Pesanan</h3>
                        <div class="summary-rows">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span id="summarySubtotal">Rp 0</span>
                            </div>
                            <div class="summary-row">
                                <span>Ongkir</span>
                                <span style="color: var(--success); font-weight: 600;">Gratis</span>
                            </div>
                            <div class="summary-row total">
                                <span>Total Pembayaran</span>
                                <span id="summaryTotal">Rp 0</span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-circle"></i> Buat Pesanan
                        </button>
                        <a href="cart.html" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        let cart = [];
        let total = 0;

        function formatRupiah(angka) {
            return 'Rp ' + angka.toString().replace(/\\B(?=(\\d{3})+(?!\\d))/g, ".");
        }

        function loadCheckout() {
            cart = JSON.parse(localStorage.getItem('cart')) || [];
            
            if (cart.length === 0) {
                window.location.href = 'cart.html';
                return;
            }
            
            // Render items
            const container = document.getElementById('checkoutItems');
            total = 0;
            
            container.innerHTML = cart.map(item => {
                const subtotal = item.price * item.quantity;
                total += subtotal;
                return `
                    <div class="checkout-item">
                        <img src="${item.image}" alt="${item.name}">
                        <div class="item-info">
                            <h4>${item.name}</h4>
                            <span class="item-qty">${item.quantity} x ${formatRupiah(item.price)}</span>
                        </div>
                        <span class="item-total">${formatRupiah(subtotal)}</span>
                    </div>
                `;
            }).join('');
            
            // Update summary
            document.getElementById('summarySubtotal').textContent = formatRupiah(total);
  