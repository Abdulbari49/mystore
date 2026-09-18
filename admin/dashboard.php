<?php
session_start();
include("../include/config.php");

// Admin login check
if(!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
$product_query = $conn->query("SELECT COUNT(*) AS total from products ");
$product_data = $product_query->fetch_assoc();
$total_products =  $product_data['total'];

$total_categories = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'];
$low_stock = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock < 10")->fetch_assoc()['total'];

$order_check = $conn->query("SHOW TABLES LIKE 'orders' ");
if($order_check->num_rows>0){
     $total_orders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
}else{
    $total_orders = 0;
}

$recent_result = $conn->query("SELECT * FROM products order by id desc limit 5 ");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - StoreAdmin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --sidebar-width: 260px;
        }
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: #f0f2f5;
            min-height: 100vh;
        }
        
        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--primary-gradient);
            padding: 20px 0;
            z-index: 1000;
            transition: all 0.3s;
            box-shadow: 2px 0 20px rgba(0,0,0,0.1);
        }
        
        .sidebar-brand {
            padding: 0 25px 30px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-brand h3 {
            color: #fff;
            font-weight: 700;
            font-size: 24px;
            margin: 0;
        }
        
        .sidebar-brand h3 i {
            margin-right: 10px;
        }
        
        .sidebar-brand small {
            color: rgba(255,255,255,0.7);
            font-size: 12px;
            display: block;
            margin-top: 5px;
        }
        
        .nav-item {
            padding: 12px 25px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            cursor: pointer;
        }
        
        .nav-item:hover, .nav-item.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-left-color: #fff;
        }
        
        .nav-item i {
            width: 24px;
            margin-right: 12px;
            font-size: 18px;
        }
        
        .nav-item .badge {
            margin-left: auto;
            background: rgba(255,255,255,0.2);
            color: #fff;
        }
        
        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
        }
        
        /* ===== STATS CARDS ===== */
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            transition: all 0.3s;
            border: 1px solid rgba(0,0,0,0.04);
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #fff;
            margin-bottom: 16px;
        }
        
        .stat-icon.purple { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-icon.blue { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .stat-icon.orange { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .stat-icon.green { background: linear-gradient(135deg, #43e97b, #38f9d7); }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a2e;
            margin: 0;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
            margin: 4px 0 0 0;
        }
        
        /* ===== TABLE ===== */
        .table-container {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
        }
        
        .table-container .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .table-container .table-header h5 {
            font-weight: 600;
            color: #1a1a2e;
            margin: 0;
        }
        
        .table {
            margin: 0;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #e9ecef;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table td {
            vertical-align: middle;
            font-size: 14px;
            color: #495057;
        }
        
        .product-img {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #f0f2f5;
        }
        
        .badge-stock {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        /* ===== QUICK ACTIONS ===== */
        .quick-actions {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
        }
        
        .quick-actions h5 {
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 16px;
        }
        
        .quick-actions .btn {
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14px;
        }
        
        /* ===== FOOTER ===== */
        .footer {
            background: #fff;
            padding: 20px 0;
            margin-top: 30px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
        }
        
        .footer p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .stat-number {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h3><i class="fas fa-store-alt"></i> StoreAdmin</h3>
        <small>Management Dashboard</small>
    </div>
    <a href="#" class="nav-item active">
        <i class="fas fa-chart-pie"></i> Dashboard
    </a>
    <a href="#" class="nav-item">
        <i class="fas fa-box"></i> Products
        <span class="badge"><?php echo $total_products; ?></span>
    </a>
    <a href="#" class="nav-item">
        <i class="fas fa-tags"></i> Categories
        <span class="badge"><?php echo $total_categories; ?></span>
    </a>
    <a href="#" class="nav-item">
        <i class="fas fa-shopping-cart"></i> Orders
        <span class="badge"><?php echo $total_orders; ?></span>
    </a>
    <a href="#" class="nav-item" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">

    <!-- Mobile Toggle -->
    <button class="btn btn-light mb-3 d-md-none" onclick="document.getElementById('sidebar').classList.toggle('show')" style="border-radius: 10px;">
        <i class="fas fa-bars"></i> Menu
    </button>

    <!-- Welcome -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h4 class="fw-bold text-dark mb-1">Dashboard</h4>
            <p class="text-muted small">Welcome back! Here's what's happening with your store.</p>
        </div>
        <div>
            <span class="badge bg-primary rounded-pill px-3 py-2">
                <i class="far fa-calendar-alt me-1"></i> <?php echo date('F j, Y'); ?>
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-cubes"></i></div>
                <h2 class="stat-number"><?php echo $total_products; ?></h2>
                <p class="stat-label">Total Products</p>
                <small class="text-success"><i class="fas fa-arrow-up"></i> 12% increase</small>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-folder-tree"></i></div>
                <h2 class="stat-number"><?php echo $total_categories; ?></h2>
                <p class="stat-label">Total Categories</p>
                <small class="text-muted">Organized collections</small>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-triangle-exclamation"></i></div>
                <h2 class="stat-number"><?php echo $low_stock; ?></h2>
                <p class="stat-label">Low Stock Products</p>
                <small class="text-danger"><i class="fas fa-exclamation-circle"></i> Needs attention</small>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-receipt"></i></div>
                <h2 class="stat-number"><?php echo $total_orders; ?></h2>
                <p class="stat-label">Total Orders</p>
                <small class="text-success"><i class="fas fa-arrow-up"></i> 8% growth</small>
            </div>
        </div>
    </div>

    <!-- Recent Products Table -->
    <div class="table-container mb-4">
        <div class="table-header">
            <h5><i class="fas fa-clock-rotate-left me-2 text-primary"></i> Recent Products</h5>
            <a href="#" class="btn btn-sm btn-outline-primary rounded-pill">View All <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $recent_result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <img src="../upload/<?php echo $row['image']; ?>" class="product-img" alt="">
                        </td>
                        <td><strong><?php echo $row['name']; ?></strong></td>
                        <td><span class="fw-bold text-primary">Rs. <?php echo number_format($row['price'], 2); ?></span></td>
                        <td><?php echo $row['stock']; ?></td>
                        <td>
                            <?php if($row['stock'] <= 0): ?>
                                <span class="badge-stock bg-danger text-white">Out of Stock</span>
                            <?php elseif($row['stock'] < 10): ?>
                                <span class="badge-stock bg-warning text-dark">Low Stock (<?php echo $row['stock']; ?>)</span>
                            <?php else: ?>
                                <span class="badge-stock bg-success text-white">In Stock</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h5><i class="fas fa-bolt me-2 text-warning"></i> Quick Actions</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="products.php" class="btn btn-success">
                <i class="fas fa-plus-circle me-1"></i> Add Product
            </a>
            <a href="category.php" class="btn btn-primary">
                <i class="fas fa-folder-plus me-1"></i> Add Category
            </a>
            <a href="../public/index.php" class="btn btn-outline-secondary" target="_blank">
                <i class="fas fa-eye me-1"></i> View Store
            </a>
            <a href="#" class="btn btn-outline-info">
                <i class="fas fa-file-export me-1"></i> Export Report
            </a>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> <strong>StoreAdmin</strong>. All rights reserved. <span class="text-muted">|</span> Made with <i class="fas fa-heart text-danger"></i> by Your Team</p>
    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>