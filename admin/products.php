<?php
session_start();
include("../include/config.php");

if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare('SELECT image FROM products WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($row = $result->fetch_assoc()) {
        $image = "../upload/".$row['image'];
        if(file_exists($image)){
            unlink($image);
        }
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param('i', $id);
    if($stmt->execute()){
        header("Location: product.php");
        exit();
    } else {
        $error_msg = "Delete failed!";
    }
}

if(isset($_POST['add_product'])) {
    $category = $_POST['Category'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];

    if($_FILES['image']['error'] == 0) {
        $target_dir = "../upload/";
        if(!is_dir($target_dir)){
            mkdir($target_dir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];

        if(in_array($ext, $allowed)) {
            $image_name = time()."_".rand(1000,9999).".".$ext;
            $target_file = $target_dir.$image_name;

            if(move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $stmt = $conn->prepare("INSERT INTO products(cat_id, name, price, stock, image, description) VALUES(?,?,?,?,?,?)");
                $stmt->bind_param("isdiss", $category, $name, $price, $stock, $image_name, $description);
                if($stmt->execute()) {
                    $success_msg = "Product added successfully!";
                } else {
                    $error_msg = "Database Error: ". $stmt->error;
                }
            } else {
                $error_msg = "Image upload failed!";
            }
        } else {
            $error_msg = "Only JPG, JPEG, PNG and GIF files are allowed.";
        }
    } else {
        $error_msg = "Please select an image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - StoreAdmin</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
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
            width: 260px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px 0;
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
            margin-left: 260px;
            padding: 30px;
        }
        
        /* ===== STATS CARDS ===== */
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
            transition: all 0.3s;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #fff;
            margin-bottom: 12px;
        }
        
        .stat-icon.purple { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-icon.blue { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .stat-icon.orange { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .stat-icon.green { background: linear-gradient(135deg, #43e97b, #38f9d7); }
        
        .stat-number {
            font-size: 28px;
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
        
        /* ===== FORM ===== */
        .form-card {
            background: #fff;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
            margin-bottom: 30px;
        }
        
        .form-card .form-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }
        
        .form-card .form-header .icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
        }
        
        .form-card .form-header h5 {
            font-weight: 600;
            color: #1a1a2e;
            margin: 0;
        }
        
        .form-card .form-header p {
            color: #6c757d;
            font-size: 13px;
            margin: 0;
        }
        
        .form-card .form-label {
            font-weight: 600;
            font-size: 13px;
            color: #1a1a2e;
        }
        
        .form-card .form-control, .form-card .form-select {
            border-radius: 10px;
            padding: 10px 16px;
            border: 2px solid #e9ecef;
            font-size: 14px;
            transition: all 0.3s;
            background: #fafbfc;
        }
        
        .form-card .form-control:focus, .form-card .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            background: #fff;
        }
        
        .form-card .form-control.is-invalid {
            border-color: #dc3545;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            padding: 10px 28px;
            border-radius: 10px;
            font-weight: 600;
            color: #fff;
            transition: all 0.3s;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.35);
            color: #fff;
        }
        
        .btn-gradient i {
            margin-right: 8px;
        }
        
        /* ===== TABLE ===== */
        .table-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
        }
        
        .table-card .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .table-card .table-header h5 {
            font-weight: 600;
            color: #1a1a2e;
            margin: 0;
        }
        
        .table-card .table-header .search-box {
            position: relative;
        }
        
        .table-card .table-header .search-box input {
            border-radius: 10px;
            padding: 8px 16px 8px 40px;
            border: 2px solid #e9ecef;
            font-size: 14px;
            transition: all 0.3s;
            width: 250px;
            background: #fafbfc;
        }
        
        .table-card .table-header .search-box input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            outline: none;
            background: #fff;
        }
        
        .table-card .table-header .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
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
            width: 50px;
            height: 50px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #f0f2f5;
        }
        
        .badge-stock {
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-stock.bg-success { background: #dcfce7; color: #166534; }
        .badge-stock.bg-warning { background: #fef3c7; color: #92400e; }
        .badge-stock.bg-danger { background: #fee2e2; color: #991b1b; }
        
        .action-btn {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .action-btn.edit {
            background: #e7f3ff;
            color: #4facfe;
        }
        
        .action-btn.edit:hover {
            background: #4facfe;
            color: #fff;
        }
        
        .action-btn.delete {
            background: #ffe7e7;
            color: #f5576c;
        }
        
        .action-btn.delete:hover {
            background: #f5576c;
            color: #fff;
        }
        
        /* ===== QUICK ACTIONS ===== */
        .quick-actions {
            background: #fff;
            border-radius: 16px;
            padding: 20px 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .quick-actions h5 {
            font-weight: 600;
            color: #1a1a2e;
            margin: 0;
        }
        
        .quick-actions h5 i {
            color: #f59e0b;
        }
        
        /* ===== ALERTS ===== */
        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 16px 20px;
        }
        
        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
        }
        
        .empty-state i {
            font-size: 56px;
            color: #d1d5db;
            margin-bottom: 16px;
            display: block;
        }
        
        .empty-state h6 {
            color: #1a1a2e;
            font-weight: 600;
        }
        
        .empty-state p {
            color: #6c757d;
            font-size: 14px;
        }
        
        /* ===== FOOTER ===== */
        .footer {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
            margin-top: 30px;
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
            
            .table-card .table-header .search-box input {
                width: 100%;
            }
            
            .quick-actions {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
            
            .quick-actions .btn {
                width: 100%;
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
    <a href="dashboard.php" class="nav-item">
        <i class="fas fa-chart-pie"></i> Dashboard
    </a>
    <a href="product.php" class="nav-item active">
        <i class="fas fa-box"></i> Products
        <span class="badge">
            <?php 
                $count = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
                echo $count; 
            ?>
        </span>
    </a>
    <a href="category.php" class="nav-item">
        <i class="fas fa-tags"></i> Categories
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

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Product Management</h4>
            <p class="text-muted small">Manage your store products</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary rounded-pill px-3 py-2">
                <i class="far fa-calendar-alt me-1"></i> <?php echo date('F j, Y'); ?>
            </span>
            <button onclick="toggleForm()" class="btn btn-gradient">
                <i class="fas fa-plus-circle"></i> Add Product
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <?php
        $total_products = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
        $low_stock = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock < 10")->fetch_assoc()['total'];
        $out_of_stock = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock <= 0")->fetch_assoc()['total'];
    ?>
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-cubes"></i></div>
                <h2 class="stat-number"><?php echo $total_products; ?></h2>
                <p class="stat-label">Total Products</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-triangle-exclamation"></i></div>
                <h2 class="stat-number"><?php echo $low_stock; ?></h2>
                <p class="stat-label">Low Stock (< 10)</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                <h2 class="stat-number"><?php echo $total_products - $out_of_stock; ?></h2>
                <p class="stat-label">In Stock</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-dollar-sign"></i></div>
                <h2 class="stat-number"><?php echo $out_of_stock; ?></h2>
                <p class="stat-label">Out of Stock</p>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <?php if(isset($success_msg)): ?>
        <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($error_msg)): ?>
        <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Add Product Form -->
    <div id="productForm" style="display: none;">
        <div class="form-card">
            <div class="form-header">
                <div class="icon-circle">
                    <i class="fas fa-plus"></i>
                </div>
                <div>
                    <h5>Add New Product</h5>
                    <p>Fill in the details to add a new product</p>
                </div>
            </div>
            
            <form action="" method="post" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label"><i class="fas fa-tag me-1 text-primary"></i> Category</label>
                        <select name="Category" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <?php
                            $query = mysqli_query($conn, "SELECT id, name FROM categories ORDER BY name");
                            while($row = mysqli_fetch_assoc($query)){
                            ?>
                                <option value="<?php echo $row['id']; ?>">
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label"><i class="fas fa-font me-1 text-primary"></i> Product Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter product name" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-rupee-sign me-1 text-primary"></i> Price (Rs.)</label>
                        <input type="number" name="price" class="form-control" placeholder="0.00" step="0.01" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-boxes me-1 text-primary"></i> Stock Quantity</label>
                        <input type="number" name="stock" class="form-control" placeholder="Enter stock quantity" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-image me-1 text-primary"></i> Product Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label"><i class="fas fa-align-left me-1 text-primary"></i> Description</label>
                        <textarea name="description" class="form-control" placeholder="Enter product description" rows="3"></textarea>
                    </div>

                    <div class="col-12">
                        <button type="submit" name="add_product" class="btn btn-gradient">
                            <i class="fas fa-save"></i> Save Product
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleForm()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="table-card">
        <div class="table-header">
            <h5><i class="fas fa-list me-2 text-primary"></i> All Products</h5>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchTable" placeholder="Search products..." onkeyup="filterTable()">
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="productTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 70px;">Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT p.*, c.name as category_name 
                              FROM products p 
                              LEFT JOIN categories c ON p.cat_id = c.id 
                              ORDER BY p.id DESC";
                    $result = $conn->query($query);
                    
                    if($result->num_rows > 0) {
                        $sn = 1;
                        while($row = $result->fetch_assoc()) {
                    ?>
                    <tr>
                        <td><?php echo $sn++; ?></td>
                        <td>
                            <img src="../upload/<?php echo $row['image']; ?>" class="product-img" alt="<?php echo $row['name']; ?>">
                        </td>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                         
                    </td>
                        <td><span class="fw-bold text-primary">Rs. <?php echo number_format($row['price'], 2); ?></span></td>
                        <td>
                            <?php if($row['stock'] <= 0): ?>
                                <span class="badge-stock bg-danger">Out of Stock</span>
                            <?php elseif($row['stock'] < 10): ?>
                                <span class="badge-stock bg-warning">Low: <?php echo $row['stock']; ?></span>
                            <?php else: ?>
                                <span class="badge-stock bg-success"><?php echo $row['stock']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="action-btn edit">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                <a href="?delete=<?php echo $row['id']; ?>" class="action-btn delete" 
                                   onclick="return confirm('Are you sure you want to delete this product?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <h6>No Products Found</h6>
                                <p>Click "Add Product" to get started with your store.</p>
                            </div>
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
        <div class="d-flex gap-2 flex-wrap">
            <button onclick="toggleForm()" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Add Product
            </button>
            <a href="category.php" class="btn btn-primary">
                <i class="fas fa-folder-plus"></i> Add Category
            </a>
            <a href="../public/index.php" class="btn btn-outline-secondary" target="_blank">
                <i class="fas fa-eye"></i> View Store
            </a>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> <strong>StoreAdmin</strong>. All rights reserved.</p>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript -->
<script>
function toggleForm() {
    var form = document.getElementById('productForm');
    if(form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        form.style.display = 'none';
    }
}

function filterTable() {
    var input = document.getElementById("searchTable");
    var filter = input.value.toLowerCase();
    var table = document.getElementById("productTable");
    var rows = table.getElementsByTagName("tr");
    
    for (var i = 1; i < rows.length; i++) {
        var cells = rows[i].getElementsByTagName("td");
        var found = false;
        for (var j = 0; j < cells.length - 1; j++) {
            var text = cells[j].textContent.toLowerCase();
            if (text.indexOf(filter) > -1) {
                found = true;
                break;
            }
        }
        rows[i].style.display = found ? "" : "none";
    }
}
</script>

</body>
</html>