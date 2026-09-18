<?php
session_start();
include("../include/config.php");

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - MyStore</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        /* ===== NAVBAR ===== */
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            padding: 15px 0;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .navbar-custom .navbar-brand {
            color: #fff !important;
            font-weight: 800;
            font-size: 24px;
            letter-spacing: -0.5px;
        }
        
        .navbar-custom .navbar-brand i {
            margin-right: 10px;
        }
        
        .navbar-custom .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 500;
            padding: 8px 20px !important;
            border-radius: 8px;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .navbar-custom .nav-link:hover {
            color: #fff !important;
            background: rgba(255,255,255,0.15);
        }
        
        .navbar-custom .nav-link i {
            margin-right: 8px;
        }
        
        .navbar-custom .cart-badge {
            background: #ff6b6b;
            color: #fff;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 4px;
        }
        
        /* ===== CART CONTAINER ===== */
        .cart-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .cart-header {
            background: #fff;
            border-radius: 16px;
            padding: 25px 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
            margin-bottom: 25px;
        }
        
        .cart-header h2 {
            font-weight: 700;
            color: #1a1a2e;
            margin: 0;
        }
        
        .cart-header h2 i {
            color: #667eea;
            margin-right: 12px;
        }
        
        .cart-header .cart-count {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 20px;
            background: #e7f3ff;
            color: #4facfe;
            font-size: 13px;
            font-weight: 600;
        }
        
        /* ===== CART ITEMS ===== */
        .cart-item {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
            margin-bottom: 16px;
            transition: all 0.3s;
        }
        
        .cart-item:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .cart-item .item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #f0f2f5;
        }
        
        .cart-item .item-details {
            flex: 1;
        }
        
        .cart-item .item-name {
            font-weight: 600;
            font-size: 18px;
            color: #1a1a2e;
            margin-bottom: 4px;
        }
        
        .cart-item .item-price {
            font-weight: 700;
            font-size: 18px;
            color: #667eea;
        }
        
        .cart-item .item-subtotal {
            font-weight: 600;
            font-size: 16px;
            color: #1a1a2e;
        }
        
        .cart-item .item-subtotal span {
            color: #667eea;
        }
        
        .cart-item .quantity-input {
            width: 80px;
            padding: 6px 10px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            transition: all 0.3s;
        }
        
        .cart-item .quantity-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            outline: none;
        }
        
        .btn-update {
            background: #4facfe;
            color: #fff;
            border: none;
            padding: 6px 18px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.3s;
        }
        
        .btn-update:hover {
            background: #3b8fd4;
            color: #fff;
            transform: translateY(-2px);
        }
        
        .btn-remove {
            background: #fee2e2;
            color: #ef4444;
            border: none;
            padding: 6px 18px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.3s;
        }
        
        .btn-remove:hover {
            background: #ef4444;
            color: #fff;
        }
        
        /* ===== CART SUMMARY ===== */
        .cart-summary {
            background: #fff;
            border-radius: 16px;
            padding: 25px 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
            margin-top: 20px;
            position: sticky;
            bottom: 0;
        }
        
        .cart-summary .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }
        
        .cart-summary .summary-row.total {
            border-top: 2px solid #e9ecef;
            padding-top: 16px;
            margin-top: 8px;
        }
        
        .cart-summary .summary-row.total .label {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a2e;
        }
        
        .cart-summary .summary-row.total .amount {
            font-size: 24px;
            font-weight: 800;
            color: #667eea;
        }
        
        .cart-summary .label {
            color: #6c757d;
            font-weight: 500;
        }
        
        .cart-summary .amount {
            font-weight: 600;
            color: #1a1a2e;
        }
        
        .btn-checkout {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: #fff;
            border: none;
            padding: 14px 40px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.35);
            color: #fff;
        }
        
        .btn-checkout i {
            font-size: 18px;
        }
        
        .btn-continue {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-continue:hover {
            background: #667eea;
            color: #fff;
        }
        
        /* ===== EMPTY CART ===== */
        .empty-cart {
            text-align: center;
            padding: 80px 20px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
        }
        
        .empty-cart i {
            font-size: 80px;
            color: #d1d5db;
            margin-bottom: 20px;
            display: block;
        }
        
        .empty-cart h4 {
            color: #1a1a2e;
            font-weight: 700;
        }
        
        .empty-cart p {
            color: #6c757d;
            font-size: 15px;
        }
        
        /* ===== FOOTER ===== */
        .footer {
            background: #1a1a2e;
            color: rgba(255,255,255,0.7);
            padding: 40px 0 20px;
            margin-top: 50px;
        }
        
        .footer h5 {
            color: #fff;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .footer a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        
        .footer a:hover {
            color: #fff;
        }
        
        .footer .social-icons a {
            display: inline-block;
            margin-right: 12px;
            font-size: 20px;
        }
        
        .footer .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
                text-align: center;
            }
            
            .cart-item .item-image {
                width: 120px;
                height: 120px;
                margin: 0 auto 15px;
            }
            
            .cart-item .item-details {
                text-align: center;
            }
            
            .cart-item .item-actions {
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .cart-summary .summary-row {
                flex-direction: column;
                text-align: center;
                gap: 4px;
            }
            
            .cart-summary .checkout-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            
            .cart-header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
        }
    </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-store-alt"></i> MyStore
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="cart.php">
                        <i class="fas fa-shopping-cart"></i> Cart
                        <span class="cart-badge"><?php echo array_sum($cart); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/dashboard.php">
                        <i class="fas fa-user-shield"></i> Admin
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ===== CART CONTENT ===== -->
<div class="cart-container">
    
    <!-- Cart Header -->
    <div class="cart-header d-flex justify-content-between align-items-center flex-wrap">
        <h2><i class="fas fa-shopping-cart"></i> My Cart</h2>
        <span class="cart-count">
            <i class="fas fa-box"></i> <?php echo count($cart); ?> Items
        </span>
    </div>

    <?php if(empty($cart)): ?>
        <!-- Empty Cart -->
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h4>Your Cart is Empty</h4>
            <p>Looks like you haven't added any items to your cart yet.</p>
            <a href="index.php" class="btn btn-primary rounded-pill px-4 py-2 mt-3">
                <i class="fas fa-arrow-left"></i> Start Shopping
            </a>
        </div>
    <?php else: ?>
        
        <!-- Cart Items -->
        <?php foreach ($cart as $product_id => $quantity): ?>
            <?php
            $query = $conn->query("SELECT * FROM products WHERE id = $product_id");
            $product = $query->fetch_assoc();
            
            if(!$product) {
                continue;
            }
            
            $subtotal = $product['price'] * $quantity;
            $total += $subtotal;
            ?>
            
            <div class="cart-item d-flex gap-4 align-items-center flex-wrap">
                <!-- Product Image -->
                <img src="../upload/<?php echo $product['image']; ?>" 
                     class="item-image" 
                     alt="<?php echo $product['name']; ?>">
                
                <!-- Product Details -->
                <div class="item-details">
                    <div class="item-name"><?php echo $product['name']; ?></div>
                    <div class="item-price">Rs. <?php echo number_format($product['price'], 2); ?></div>
                    <div class="item-subtotal mt-1">
                        Subtotal: <span>Rs. <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="d-flex align-items-center gap-2 ms-auto flex-wrap item-actions">
                    <!-- Update Quantity Form -->
                    <form action="update_cart.php" method="post" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <input type="number" 
                               name="quantity" 
                               class="quantity-input" 
                               value="<?php echo $quantity; ?>" 
                               min="1" 
                               max="<?php echo $product['stock']; ?>">
                        <button type="submit" name="update" class="btn-update">
                            <i class="fas fa-sync-alt"></i> Update
                        </button>
                    </form>
                    
                    <!-- Remove Form -->
                    <form action="remove_from_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <button type="submit" name="remove" class="btn-remove">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- Cart Summary -->
        <div class="cart-summary">
            <div class="summary-row">
                <span class="label">Subtotal</span>
                <span class="amount">Rs. <?php echo number_format($total, 2); ?></span>
            </div>
            <div class="summary-row">
                <span class="label">Shipping</span>
                <span class="amount">Rs. 0.00</span>
            </div>
            <div class="summary-row total">
                <span class="label">Total</span>
                <span class="amount">Rs. <?php echo number_format($total, 2); ?></span>
            </div>
            
            <div class="d-flex gap-3 mt-3 flex-wrap checkout-buttons">
                <a href="checkout.php" class="btn-checkout">
                    <i class="fas fa-credit-card"></i> Proceed to Checkout
                </a>
                <a href="index.php" class="btn-continue">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
            </div>
        </div>
        
    <?php endif; ?>
    
</div>

<!-- ===== FOOTER ===== -->
<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5><i class="fas fa-store-alt me-2"></i> MyStore</h5>
                <p style="font-size: 14px; line-height: 1.8;">
                    Your trusted online store for quality products. We offer the best prices and excellent customer service.
                </p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-4">
                <h5>Quick Links</h5>
                <a href="index.php"><i class="fas fa-chevron-right me-1" style="font-size: 10px;"></i> Home</a>
                <a href="cart.php"><i class="fas fa-chevron-right me-1" style="font-size: 10px;"></i> Cart</a>
                <a href="#"><i class="fas fa-chevron-right me-1" style="font-size: 10px;"></i> About Us</a>
                <a href="#"><i class="fas fa-chevron-right me-1" style="font-size: 10px;"></i> Contact</a>
            </div>
            <div class="col-lg-3 col-md-4">
                <h5>Categories</h5>
                <?php
                $cat_footer = $conn->query("SELECT name, slug FROM categories ORDER BY name ASC LIMIT 5");
                while($cat = $cat_footer->fetch_assoc()):
                ?>
                    <a href="category.php?slug=<?php echo $cat['slug']; ?>">
                        <i class="fas fa-chevron-right me-1" style="font-size: 10px;"></i> <?php echo $cat['name']; ?>
                    </a>
                <?php endwhile; ?>
            </div>
            <div class="col-lg-3 col-md-4">
                <h5>Contact Us</h5>
                <p style="font-size: 14px; margin-bottom: 5px;">
                    <i class="fas fa-map-marker-alt me-2"></i> 123 Main Street, City
                </p>
                <p style="font-size: 14px; margin-bottom: 5px;">
                    <i class="fas fa-phone me-2"></i> +92 300 1234567
                </p>
                <p style="font-size: 14px;">
                    <i class="fas fa-envelope me-2"></i> info@mystore.com
                </p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <strong>MyStore</strong>. All rights reserved. Made with <i class="fas fa-heart text-danger"></i></p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>