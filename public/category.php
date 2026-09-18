<?php 
session_start();
include("../include/config.php");

$slug = $_GET['slug'];

$cat_query = $conn->prepare("SELECT * FROM categories WHERE slug = ?");
$cat_query->bind_param("s", $slug);
$cat_query->execute();
$cat_result = $cat_query->get_result();
$category = $cat_result->fetch_assoc();

// If category not found, redirect to home
if(!$category) {
    header("Location: index.php");
    exit;  
}

$category_id = $category['id'];

$products = $conn->prepare("SELECT * FROM products WHERE cat_id = ? ORDER BY id DESC");
$products->bind_param('i', $category_id);
$products->execute();
$product_result = $products->get_result();

// Categories for header
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category['name']; ?> - MyStore</title>
    
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
        
        .navbar-custom .nav-link.active {
            color: #fff !important;
            background: rgba(255,255,255,0.15);
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
        
        /* ===== CATEGORIES BAR ===== */
        .categories-bar {
            background: #fff;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            margin-bottom: 30px;
            border-radius: 12px;
        }
        
        .categories-bar .category-label {
            font-weight: 600;
            color: #1a1a2e;
            margin-right: 10px;
        }
        
        .categories-bar .category-link {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 50px;
            color: #495057;
            text-decoration: none;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.3s;
            margin: 3px 4px;
        }
        
        .categories-bar .category-link:hover {
            background: #f0f2f5;
            color: #667eea;
        }
        
        .categories-bar .category-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        
        .categories-bar .category-link i {
            margin-right: 6px;
        }
        
        /* ===== CATEGORY HEADER ===== */
        .category-header {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
            margin-bottom: 30px;
        }
        
        .category-header h2 {
            font-weight: 700;
            color: #1a1a2e;
        }
        
        .category-header h2 i {
            color: #667eea;
            margin-right: 12px;
        }
        
        .category-header p {
            color: #6c757d;
            font-size: 15px;
            margin: 0;
        }
        
        .category-header .product-count {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 20px;
            background: #e7f3ff;
            color: #4facfe;
            font-size: 13px;
            font-weight: 600;
        }
        
        /* ===== PRODUCT GRID ===== */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 25px;
            padding: 0;
        }
        
        .product-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.1);
        }
        
        .product-card .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: #f8f9fa;
        }
        
        .product-card .product-body {
            padding: 20px;
        }
        
        .product-card .product-body .product-tag {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: #e7f3ff;
            color: #4facfe;
            margin-bottom: 10px;
        }
        
        .product-card .product-body .product-name {
            font-weight: 600;
            font-size: 16px;
            color: #1a1a2e;
            margin-bottom: 6px;
            text-decoration: none;
            display: block;
        }
        
        .product-card .product-body .product-name:hover {
            color: #667eea;
        }
        
        .product-card .product-body .product-price {
            font-weight: 700;
            font-size: 20px;
            color: #667eea;
            margin-bottom: 4px;
        }
        
        .product-card .product-body .product-price .original-price {
            font-size: 14px;
            color: #adb5bd;
            text-decoration: line-through;
            font-weight: 400;
            margin-left: 8px;
        }
        
        .product-card .product-body .product-stock {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 12px;
        }
        
        .product-card .product-body .product-stock i {
            margin-right: 4px;
        }
        
        .product-card .product-body .product-stock.in-stock {
            color: #22c55e;
        }
        
        .product-card .product-body .product-stock.out-of-stock {
            color: #ef4444;
        }
        
        .btn-view {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            justify-content: center;
        }
        
        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.35);
            color: #fff;
        }
        
        .btn-view i {
            font-size: 14px;
        }
        
        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border-radius: 16px;
            grid-column: 1 / -1;
        }
        
        .empty-state i {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 16px;
            display: block;
        }
        
        .empty-state h5 {
            color: #1a1a2e;
            font-weight: 600;
        }
        
        .empty-state p {
            color: #6c757d;
            font-size: 14px;
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
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 15px;
            }
            
            .product-card .product-image {
                height: 160px;
            }
            
            .product-card .product-body {
                padding: 15px;
            }
            
            .product-card .product-body .product-price {
                font-size: 17px;
            }
            
            .category-header {
                padding: 20px;
            }
            
            .category-header h2 {
                font-size: 22px;
            }
            
            .categories-bar .category-link {
                font-size: 12px;
                padding: 4px 12px;
            }
        }
        
        @media (max-width: 576px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            
            .product-card .product-image {
                height: 130px;
            }
            
            .product-card .product-body .product-name {
                font-size: 14px;
            }
            
            .product-card .product-body .product-price {
                font-size: 15px;
            }
            
            .btn-view {
                font-size: 12px;
                padding: 8px 12px;
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
                        <span class="cart-badge">0</span>
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

<!-- ===== CATEGORIES BAR ===== -->
<div class="container">
    <div class="categories-bar">
        <div class="d-flex flex-wrap align-items-center">
            <span class="category-label"><i class="fas fa-tags"></i> Categories:</span>
            <a href="index.php" class="category-link">
                <i class="fas fa-th-list"></i> All
            </a>
            <?php 
            // Reset category pointer
            $categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
            while ($cat = $categories->fetch_assoc()): 
                $active = ($cat['id'] == $category_id) ? 'active' : '';
            ?>
                <a href="category.php?slug=<?php echo $cat['slug']; ?>" class="category-link <?php echo $active; ?>">
                    <i class="fas fa-tag"></i> <?php echo $cat['name']; ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- ===== CATEGORY HEADER ===== -->
<div class="container">
    <div class="category-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2><i class="fas fa-folder-tree"></i> <?php echo $category['name']; ?></h2>
                <p>Showing products in this category</p>
            </div>
            <div>
                <span class="product-count">
                    <i class="fas fa-box"></i> <?php echo $product_result->num_rows; ?> Products
                </span>
            </div>
        </div>
    </div>
</div>

<!-- ===== PRODUCT GRID ===== -->
<div class="container">
    <div class="product-grid">
        <?php if($product_result->num_rows > 0): ?>
            <?php while($product = $product_result->fetch_assoc()): ?>
                <div class="product-card">
                    <img src="../upload/<?php echo $product['image']; ?>" 
                         class="product-image" 
                         alt="<?php echo $product['name']; ?>">
                    <div class="product-body">
                        <span class="product-tag">
                            <i class="fas fa-tag"></i> <?php echo $category['name']; ?>
                        </span>
                        <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="product-name">
                            <?php echo $product['name']; ?>
                        </a>
                        <div class="product-price">
                            Rs. <?php echo number_format($product['price'], 2); ?>
                            <?php if($product['price'] > 1000): ?>
                                <span class="original-price">Rs. <?php echo number_format($product['price'] * 1.2, 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php 
                        $stock_status = '';
                        $stock_text = '';
                        if($product['stock'] <= 0) {
                            $stock_status = 'out-of-stock';
                            $stock_text = 'Out of Stock';
                        } elseif($product['stock'] < 10) {
                            $stock_status = 'out-of-stock';
                            $stock_text = 'Only ' . $product['stock'] . ' left!';
                        } else {
                            $stock_status = 'in-stock';
                            $stock_text = 'In Stock';
                        }
                        ?>
                        <div class="product-stock <?php echo $stock_status; ?>">
                            <i class="fas fa-<?php echo ($product['stock'] > 0) ? 'check-circle' : 'times-circle'; ?>"></i>
                            <?php echo $stock_text; ?>
                        </div>
                        <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn-view">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h5>No Products Found</h5>
                <p>No products available in this category yet. Check back soon!</p>
                <a href="index.php" class="btn btn-primary rounded-pill mt-3">
                    <i class="fas fa-arrow-left"></i> Browse All Products
                </a>
            </div>
        <?php endif; ?>
    </div>
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