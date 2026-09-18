<?php
session_start();
include("../include/config.php");

// Check if cart exists
// if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])){
//     header("Location: cart.php");
//     exit;
// }

$cart = $_SESSION['cart'];
$cart_items = [];
$total = 0;

if(empty($cart)){
    $cart_empty = true;
} else {
    foreach($cart as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        if($product){
            $subtotal = $product['price'] * $quantity;
            $total += $subtotal;
            $product['quantity'] = $quantity;
            $product['subtotal'] = $subtotal;
            $cart_items[] = $product;
        }
    }
}

if(isset($_POST['place_order'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $phone = $_POST['phone'];
    $notes = $_POST['notes'];

    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (name, email, address, city, phone, notes, total_amount, order_date) VALUES(?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssssd", $name, $email, $address, $city, $phone, $notes, $total);
        $stmt->execute();
        $order_id = $conn->insert_id;

        // Insert order items
        foreach($cart_items as $item){
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES(?, ?, ?, ?)");
            $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
            $stmt->execute();
            
            // Update stock
            $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $update_stock->bind_param("ii", $item['quantity'], $item['id']);
            $update_stock->execute();
        }

        // Commit transaction
        $conn->commit();
        
        // Clear cart
        unset($_SESSION['cart']);
        
        // Redirect to thank you page
        header("Location: thank-you.php?order_id=" . $order_id);
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error processing order: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MyStore</title>
    
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
            background: #f0f2f5;
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
        }
        
        .navbar-custom .nav-link:hover {
            color: #fff !important;
            background: rgba(255,255,255,0.15);
        }
        
        .navbar-custom .nav-link i {
            margin-right: 8px;
        }
        
        /* ===== CHECKOUT CONTAINER ===== */
        .checkout-container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .checkout-header {
            background: #fff;
            border-radius: 16px;
            padding: 25px 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
            margin-bottom: 25px;
        }
        
        .checkout-header h2 {
            font-weight: 700;
            color: #1a1a2e;
            margin: 0;
        }
        
        .checkout-header h2 i {
            color: #667eea;
            margin-right: 12px;
        }
        
        .checkout-header .step-badge {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 20px;
            background: #e7f3ff;
            color: #4facfe;
            font-size: 13px;
            font-weight: 600;
        }
        
        /* ===== FORM CARD ===== */
        .form-card {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
        }
        
        .form-card .form-header {
            margin-bottom: 25px;
        }
        
        .form-card .form-header h5 {
            font-weight: 600;
            color: #1a1a2e;
        }
        
        .form-card .form-header p {
            color: #6c757d;
            font-size: 14px;
            margin: 0;
        }
        
        .form-card .form-label {
            font-weight: 600;
            font-size: 13px;
            color: #1a1a2e;
        }
        
        .form-card .form-label .required {
            color: #dc3545;
            margin-left: 3px;
        }
        
        .form-card .form-control {
            border-radius: 10px;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-card .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
        }
        
        .form-card .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px 0 0 10px;
            color: #6c757d;
        }
        
        .form-card .input-group .form-control {
            border-radius: 0 10px 10px 0;
        }
        
        /* ===== ORDER SUMMARY ===== */
        .summary-card {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
            position: sticky;
            top: 20px;
        }
        
        .summary-card .summary-header {
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 15px;
        }
        
        .summary-card .summary-header h5 {
            font-weight: 600;
            color: #1a1a2e;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f1f3f5;
        }
        
        .summary-item .item-info {
            flex: 1;
        }
        
        .summary-item .item-name {
            font-weight: 500;
            color: #1a1a2e;
            font-size: 14px;
        }
        
        .summary-item .item-meta {
            color: #6c757d;
            font-size: 13px;
        }
        
        .summary-item .item-price {
            font-weight: 600;
            color: #667eea;
        }
        
        .summary-total {
            padding-top: 15px;
            margin-top: 15px;
            border-top: 2px solid #e9ecef;
        }
        
        .summary-total .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
        }
        
        .summary-total .total-row.grand-total {
            padding-top: 10px;
            margin-top: 5px;
            border-top: 2px solid #e9ecef;
        }
        
        .summary-total .total-row.grand-total .label {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a2e;
        }
        
        .summary-total .total-row.grand-total .amount {
            font-size: 22px;
            font-weight: 800;
            color: #667eea;
        }
        
        .btn-place-order {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: #fff;
            border: none;
            padding: 14px 40px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn-place-order:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.35);
            color: #fff;
        }
        
        .btn-place-order i {
            margin-right: 8px;
        }
        
        .btn-back {
            background: transparent;
            color: #6c757d;
            border: 2px solid #e9ecef;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-back:hover {
            background: #f8f9fa;
            color: #1a1a2e;
            border-color: #dee2e6;
        }
        
        /* ===== EMPTY STATE ===== */
        .empty-checkout {
            text-align: center;
            padding: 80px 20px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        
        .empty-checkout i {
            font-size: 80px;
            color: #d1d5db;
            margin-bottom: 20px;
        }
        
        .empty-checkout h4 {
            color: #1a1a2e;
            font-weight: 700;
        }
        
        .empty-checkout p {
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
        }
        
        .footer a:hover {
            color: #fff;
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
            .checkout-header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .summary-card {
                position: relative;
                top: 0;
                margin-top: 20px;
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
                    <a class="nav-link" href="cart.php">
                        <i class="fas fa-shopping-cart"></i> Cart
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="checkout.php">
                        <i class="fas fa-credit-card"></i> Checkout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ===== CHECKOUT CONTENT ===== -->
<div class="checkout-container">

    <!-- Checkout Header -->
    <div class="checkout-header d-flex justify-content-between align-items-center flex-wrap">
        <h2><i class="fas fa-credit-card"></i> Checkout</h2>
        <span class="step-badge">
            <i class="fas fa-clipboard-list"></i> Step 2 of 2
        </span>
    </div>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(isset($cart_empty) && $cart_empty): ?>
        <!-- Empty Cart -->
        <div class="empty-checkout">
            <i class="fas fa-shopping-cart"></i>
            <h4>Your Cart is Empty</h4>
            <p>You need to add items to your cart before checking out.</p>
            <a href="index.php" class="btn btn-primary rounded-pill px-4 py-2 mt-3">
                <i class="fas fa-arrow-left"></i> Start Shopping
            </a>
        </div>
    <?php else: ?>

        <div class="row g-4">
            <!-- Billing Form -->
            <div class="col-lg-7">
                <div class="form-card">
                    <div class="form-header">
                        <h5><i class="fas fa-user me-2 text-primary"></i> Billing Details</h5>
                        <p>Please fill in your information to complete the order</p>
                    </div>

                    <form action="" method="post">
                        <div class="row g-3">
                            <!-- Name -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-user me-1"></i> Full Name <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-envelope me-1"></i> Email <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-phone me-1"></i> Phone Number <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" name="phone" class="form-control" placeholder="03XX-XXXXXXX" required>
                                </div>
                            </div>

                            <!-- City -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-city me-1"></i> City <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-city"></i></span>
                                    <input type="text" name="city" class="form-control" placeholder="Lahore" required>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i> Delivery Address <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <textarea name="address" class="form-control" rows="3" placeholder="House #, Street, Area" required></textarea>
                                </div>
                            </div>

                            <!-- Order Notes -->
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-sticky-note me-1"></i> Order Notes (Optional)
                                </label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Any special instructions for delivery"></textarea>
                            </div>

                            <div class="col-12">
                                <div class="d-flex gap-3 flex-wrap mt-2">
                                    <button type="submit" name="place_order" class="btn-place-order">
                                        <i class="fas fa-check-circle"></i> Place Order
                                    </button>
                                    <a href="cart.php" class="btn-back">
                                        <i class="fas fa-arrow-left"></i> Back to Cart
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-5">
                <div class="summary-card">
                    <div class="summary-header">
                        <h5><i class="fas fa-receipt me-2 text-primary"></i> Order Summary</h5>
                        <small class="text-muted"><?php echo count($cart_items); ?> items in your cart</small>
                    </div>

                    <?php foreach($cart_items as $item): ?>
                        <div class="summary-item">
                            <div class="item-info">
                                <div class="item-name"><?php echo $item['name']; ?></div>
                                <div class="item-meta">Qty: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="item-price">Rs. <?php echo number_format($item['subtotal'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>

                    <div class="summary-total">
                        <div class="total-row">
                            <span>Subtotal</span>
                            <span>Rs. <?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="total-row">
                            <span>Delivery</span>
                            <span><span class="text-success">Free</span></span>
                        </div>
                        <div class="total-row grand-total">
                            <span class="label">Total</span>
                            <span class="amount">Rs. <?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>

                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted d-flex align-items-center">
                            <i class="fas fa-shield-alt text-success me-2"></i>
                            Your information is secure and will only be used for order processing.
                        </small>
                    </div>
                </div>
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
                    Your trusted online store for quality products.
                </p>
            </div>
            <div class="col-lg-2 col-md-4">
                <h5>Quick Links</h5>
                <a href="index.php"><i class="fas fa-chevron-right me-1"></i> Home</a>
                <a href="cart.php"><i class="fas fa-chevron-right me-1"></i> Cart</a>
                <a href="checkout.php"><i class="fas fa-chevron-right me-1"></i> Checkout</a>
            </div>
            <div class="col-lg-3 col-md-4">
                <h5>Categories</h5>
                <?php
                $cat_footer = $conn->query("SELECT name, slug FROM categories ORDER BY name ASC LIMIT 5");
                while($cat = $cat_footer->fetch_assoc()):
                ?>
                    <a href="category.php?slug=<?php echo $cat['slug']; ?>">
                        <i class="fas fa-chevron-right me-1"></i> <?php echo $cat['name']; ?>
                    </a>
                <?php endwhile; ?>
            </div>
            <div class="col-lg-3 col-md-4">
                <h5>Contact Us</h5>
                <p><i class="fas fa-phone me-2"></i> +92 300 1234567</p>
                <p><i class="fas fa-envelope me-2"></i> info@mystore.com</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <strong>MyStore</strong>. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>