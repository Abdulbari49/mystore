<?php
session_start();
include("../include/config.php");

$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

$id = $_GET['id'];

// Fetch product
$product_query = $conn->prepare("SELECT * FROM products WHERE id = ?");
$product_query->bind_param("i", $id);
$product_query->execute();
$product_result = $product_query->get_result();
$product = $product_result->fetch_assoc();

// If product not found
if (!$product) {
    echo "Product not found";
    exit;
}

// Get category
$cat_id = $product['cat_id'];
$cat_query = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$cat_query->bind_param("i", $cat_id);
$cat_query->execute();
$cat_result = $cat_query->get_result();
$category = $cat_result->fetch_assoc();

// Related products
$related = $conn->prepare("SELECT * FROM products WHERE cat_id = ? AND id != ? ORDER BY id DESC LIMIT 4");
$related->bind_param("ii", $cat_id, $id);
$related->execute();
$related_result = $related->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - MyStore</title>
    
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
        
        /* ===== BREADCRUMB ===== */
        .breadcrumb-custom {
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
        }
        
        .breadcrumb-custom .breadcrumb-item a {
            color: #667eea;
            text-decoration: none;
        }
        
        .breadcrumb-custom .breadcrumb-item.active {
            color: #6c757d;
        }
        
        /* ===== PRODUCT DETAIL CARD ===== */
        .product-detail-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.04);
            transition: all 0.3s;
        }
        
        .product-detail-card .product-image {
            width: 100%;
            height: 450px;
            object-fit: cover;
            background: #f8f9fa;
        }
        
        .product-detail-card .product-body {
            padding: 40px;
        }
        
        .product-detail-card .product-body .product-category {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: #e7f3ff;
            color: #4facfe;
            margin-bottom: 12px;
        }
        
        .product-detail-card .product-body .product-name {
            font-weight: 700;
            font-size: 28px;
            color: #1a1a2e;
            margin-bottom: 8px;
        }
        
        .product-detail-card .product-body .product-price {
            font-weight: 700;
            font-size: 32px;
            color: #667eea;
            margin-bottom: 6px;
        }
        
        .product-detail-card .product-body .product-price .original-price {
            font-size: 18px;
            color: #adb5bd;
            text-decoration: line-through;
            font-weight: 400;
            margin-left: 12px;
        }
        
        .product-detail-card .product-body .product-stock {
            font-size: 14px;
            margin-bottom: 16px;
            padding: 8px 16px;
            border-radius: 10px;
            display: inline-block;
        }
        
        .product-detail-card .product-body .product-stock.in-stock {
            background: #dcfce7;
            color: #166534;
        }
        
        .product-detail-card .product-body .product-stock.out-of-stock {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .product-detail-card .product-body .product-description {
            color: #495057;
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 20px;
            padding: 16px 0;
            border-top: 1px solid #e9ecef;
            border-bottom: 1px solid #e9ecef;
        }
        
        .product-detail-card .product-body .product-meta {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .product-detail-card .product-body .product-meta .meta-item {
            font-size: 14px;
            color: #6c757d;
        }
        
        .product-detail-card .product-body .product-meta .meta-item i {
            color: #667eea;
            margin-right: 6px;
        }
        
        .btn-add-cart {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            padding: 14px 40px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.35);
            color: #fff;
        }
        
        .btn-add-cart i {
            margin-right: 8px;
        }
        
        .btn-add-cart:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        /* ===== RELATED PRODUCTS ===== */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .section-header h3 {
            font-weight: 700;
            color: #1a1a2e;
        }
        
        .section-header h3 i {
            color: #667eea;
            margin-right: 10px;
        }
        
        .related-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
            height: 100%;
        }
        
        .related-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.1);
        }
        
        .related-card .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f8f9fa;
        }
        
        .related-card .product-body {
            padding: 18px;
        }
        
        .related-card .product-body .product-name {
            font-weight: 600;
            font-size: 15px;
            color: #1a1a2e;
            margin-bottom: 4px;
            text-decoration: none;
            display: block;
        }
        
        .related-card .product-body .product-name:hover {
            color: #667eea;
        }
        
        .related-card .product-body .product-price {
            font-weight: 700;
            font-size: 18px;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .related-card .product-body .btn-sm-cart {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.3s;
            width: 100%;
        }
        
        .related-card .product-body .btn-sm-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            color: #fff;
        }
        
        .related-card .product-body .btn-sm-cart i {
            margin-right: 6px;
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
            .product-detail-card .product-image {
                height: 250px;
            }
            
            .product-detail-card .product-body {
                padding: 25px;
            }
            
            .product-detail-card .product-body .product-name {
                font-size: 22px;
            }
            
            .product-detail-card .product-body .product-price {
                font-size: 26px;
            }
            
            .categories-bar .category-link {
                font-size: 12px;
                padding: 4px 12px;
            }
            
            .related-card .product-image {
                height: 150px;
            }
        }
        
        @media (max-width: 576px) {
            .product-detail-card .product-image {
                height: 200px;
            }
            
            .product-detail-card .product-body {
                padding: 18px;
            }
            
            .product-detail-card .product-body .product-name {
                font-size: 18px;
            }
            
            .product-detail-card .product-body .product-price {
                font-size: 22px;
            }
            
            .product-detail-card .product-body .product-meta {
                gap: 15px;
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
                $active = ($cat['id'] == $cat_id) ? 'active' : '';
            ?>
                <a href="category.php?slug=<?php echo $cat['slug']; ?>" class="category-link <?php echo $active; ?>">
                    <i class="fas fa-tag"></i> <?php echo $cat['name']; ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- ===== PRODUCT DETAIL ===== -->
<div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="breadcrumb-custom">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="category.php?slug=<?php echo $category['slug']; ?>"><?php echo $category['name']; ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $product['name']; ?></li>
        </ol>
    </nav>

    <!-- Product Detail Card -->
    <div class="product-detail-card">
        <div class="row g-0">
            <div class="col-md-5">
                <img src="../upload/<?php echo $product['image']; ?>" 
                     class="product-image" 
                     alt="<?php echo $product['name']; ?>">
            </div>
            <div class="col-md-7">
                <div class="product-body">
                    <span class="product-category">
                        <i class="fas fa-tag"></i> <?php echo $category['name']; ?>
                    </span>
                    
                    <h1 class="product-name"><?php echo $product['name']; ?></h1>
                    
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
                        $stock_text = 'Only ' . $product['stock'] . ' left in stock!';
                    } else {
                        $stock_status = 'in-stock';
                        $stock_text = '✓ In Stock';
                    }
                    ?>
                    <div class="product-stock <?php echo $stock_status; ?>">
                        <i class="fas fa-<?php echo ($product['stock'] > 0) ? 'check-circle' : 'times-circle'; ?>"></i>
                        <?php echo $stock_text; ?>
                    </div>
                    
                    <div class="product-description">
                        <?php echo nl2br($product['description']); ?>
                    </div>
                    
                    <div class="product-meta">
                        <span class="meta-item">
                            <i class="fas fa-box"></i> Product ID: #<?php echo $product['id']; ?>
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-clock"></i> Added: <?php echo date('d M Y', strtotime($product['id'])); ?>
                        </span>
                    </div>
                    
                    <form action="add_to_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" name="add_to_cart" class="btn-add-cart" <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i> 
                            <?php echo ($product['stock'] > 0) ? 'Add to Cart' : 'Out of Stock'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== RELATED PRODUCTS ===== -->
<?php if($related_result->num_rows > 0): ?>
<div class="container mt-5">
    <div class="section-header">
        <h3><i class="fas fa-random"></i> Related Products</h3>
        <a href="category.php?slug=<?php echo $category['slug']; ?>" class="btn btn-outline-primary btn-sm rounded-pill">
            View All <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
    
    <div class="row g-4">
        <?php while ($rel = $related_result->fetch_assoc()): ?>
            <div class="col-lg-3 col-md-6">
                <div class="related-card">
                    <img src="../upload/<?php echo $rel['image']; ?>" 
                         class="product-image" 
                         alt="<?php echo $rel['name']; ?>">
                    <div class="product-body">
                        <a href="product_detail.php?id=<?php echo $rel['id']; ?>" class="product-name">
                            <?php echo $rel['name']; ?>
                        </a>
                        <div class="product-price">Rs. <?php echo number_format($rel['price'], 2); ?></div>
                        <form action="add_to_cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $rel['id']; ?>">
                            <button type="submit" name="add_to_cart" class="btn-sm-cart" <?php echo ($rel['stock'] <= 0) ? 'disabled' : ''; ?>>
                                <i class="fas fa-shopping-cart"></i> 
                                <?php echo ($rel['stock'] > 0) ? 'Add to Cart' : 'Out of Stock'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>

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