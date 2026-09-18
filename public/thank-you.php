<?php
session_start();
include("../include/config.php");

// Get order ID
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Fetch order details for display
if($order_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    // Fetch order items
    if($order) {
        $items_stmt = $conn->prepare("SELECT oi.*, p.name as product_name FROM order_items oi 
                                      LEFT JOIN products p ON oi.product_id = p.id 
                                      WHERE oi.order_id = ?");
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        $order_items = $items_result->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - MyStore</title>
    
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
            display: flex;
            flex-direction: column;
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
        
        /* ===== THANK YOU CARD ===== */
        .thankyou-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        
        .thankyou-card {
            background: #fff;
            border-radius: 24px;
            padding: 50px 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            text-align: center;
            border: 1px solid rgba(0,0,0,0.04);
            position: relative;
            overflow: hidden;
        }
        
        .thankyou-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }
        
        .thankyou-card .success-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 50px;
            color: #22c55e;
            animation: bounceIn 0.8s ease;
        }
        
        @keyframes bounceIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .thankyou-card h2 {
            font-weight: 800;
            color: #1a1a2e;
            margin-bottom: 8px;
        }
        
        .thankyou-card .subtitle {
            color: #6c757d;
            font-size: 16px;
            margin-bottom: 25px;
        }
        
        .thankyou-card .order-id-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 20px;
            display: inline-block;
        }
        
        .thankyou-card .order-id-box .label {
            color: #6c757d;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
        }
        
        .thankyou-card .order-id-box .id {
            font-size: 22px;
            font-weight: 700;
            color: #667eea;
        }
        
        .thankyou-card .order-details {
            text-align: left;
            margin: 20px 0;
            padding: 0;
        }
        
        .thankyou-card .order-details .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f1f3f5;
            font-size: 14px;
        }
        
        .thankyou-card .order-details .detail-item:last-child {
            border-bottom: none;
        }
        
        .thankyou-card .order-details .detail-item .label {
            color: #6c757d;
        }
        
        .thankyou-card .order-details .detail-item .value {
            font-weight: 500;
            color: #1a1a2e;
        }
        
        .thankyou-card .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 25px;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            padding: 12px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.35);
            color: #fff;
        }
        
        .btn-outline-custom {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 10px 28px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-outline-custom:hover {
            background: #667eea;
            color: #fff;
        }
        
        /* ===== ORDER ITEMS SUMMARY ===== */
        .items-summary {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
        }
        
        .items-summary h6 {
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 12px;
            text-align: left;
        }
        
        .items-summary .item-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
            text-align: left;
        }
        
        .items-summary .item-row .item-name {
            color: #1a1a2e;
        }
        
        .items-summary .item-row .item-price {
            color: #6c757d;
            font-weight: 500;
        }
        
        .items-summary .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0 0;
            margin-top: 8px;
            border-top: 2px solid #e9ecef;
            font-weight: 700;
            font-size: 16px;
            text-align: left;
        }
        
        .items-summary .total-row .total-label {
            color: #1a1a2e;
        }
        
        .items-summary .total-row .total-amount {
            color: #667eea;
        }
        
        /* ===== FOOTER ===== */
        .footer {
            background: #1a1a2e;
            color: rgba(255,255,255,0.7);
            padding: 30px 0 20px;
            margin-top: auto;
        }
        
        .footer .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
            text-align: center;
            font-size: 14px;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 576px) {
            .thankyou-card {
                padding: 30px 20px;
            }
            
            .thankyou-card .success-icon {
                width: 70px;
                height: 70px;
                font-size: 35px;
            }
            
            .thankyou-card h2 {
                font-size: 24px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn-primary-custom, .btn-outline-custom {
                justify-content: center;
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
            </ul>
        </div>
    </div>
</nav>

<!-- ===== THANK YOU CONTENT ===== -->
<div class="thankyou-wrapper">
    <div class="thankyou-card">
        
        <!-- Success Icon -->
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h2>Order Confirmed! 🎉</h2>
        <p class="subtitle">Thank you for your purchase. We'll send you a confirmation email shortly.</p>
        
        <!-- Order ID -->
        <div class="order-id-box">
            <span class="label">Order Number</span>
            <span class="id">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></span>
        </div>
        
        <!-- Order Details -->
        <?php if(isset($order) && $order): ?>
            <div class="order-details">
                <div class="detail-item">
                    <span class="label">Order Date</span>
                 </div>
                <div class="detail-item">
                    <span class="label">Payment Method</span>
                    <span class="value">Cash on Delivery</span>
                </div>
                <div class="detail-item">
                    <span class="label">Delivery Address</span>
                </div>
                <div class="detail-item">
                    <span class="label">City</span>
                </div>
            </div>
            
            <!-- Order Items Summary -->
            <?php if(isset($order_items) && count($order_items) > 0): ?>
                <div class="items-summary">
                    <h6><i class="fas fa-receipt me-2 text-primary"></i> Order Summary</h6>
                    <?php foreach($order_items as $item): ?>
                        <div class="item-row">
                            <span class="item-name"><?php echo htmlspecialchars($item['product_name']); ?> × <?php echo $item['quantity']; ?></span>
                            <span class="item-price">Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="total-row">
                        <span class="total-label">Total Amount</span>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="index.php" class="btn-primary-custom">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
            <a href="index.php" class="btn-outline-custom">
                <i class="fas fa-print"></i> Print Receipt
            </a>
        </div>
        
        <!-- Additional Info -->
        <div class="mt-4 p-3 bg-light rounded text-start">
            <small class="text-muted d-flex align-items-start gap-2">
                <i class="fas fa-info-circle text-primary mt-1"></i>
                <span>We have sent a confirmation email to your registered email address. 
                If you have any questions, please contact our support team.</span>
            </small>
        </div>
        
    </div>
</div>

<!-- ===== FOOTER ===== -->
<footer class="footer">
    <div class="container">
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <strong>MyStore</strong>. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>