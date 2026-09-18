<?php
session_start();
include("../include/config.php");

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if(isset($_POST['update'])) {
    $categories = $_POST['category'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];
    $product_id = $_POST['id'];

    // If new image is uploaded
    if($_FILES['image']['error'] == 0) {
        $target_dir = "../upload/";
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if(in_array($ext, $allowed)) {
            $image_name = time()."_".rand(1000,9999).".".$ext;
            $image_file = $target_dir.$image_name;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $image_file)) {
                // Delete old image
                $old_image = "../upload/".$product['image'];
                if(file_exists($old_image)) {
                    unlink($old_image);
                }
                
                $stmt = $conn->prepare("UPDATE products SET cat_id=?, name=?, price=?, stock=?, image=?, description=? WHERE id=?");
                $stmt->bind_param('isdissi', $categories, $name, $price, $stock, $image_name, $description, $product_id);
                if($stmt->execute()) {
                    header("Location: product.php?msg=updated");
                    exit;
                } else {
                    $error = "Database Error: ".$conn->error;
                }
            }
        }
    } else {
        // Update without changing image
        $stmt = $conn->prepare("UPDATE products SET cat_id=?, name=?, price=?, stock=?, description=? WHERE id=?");
        $stmt->bind_param('isdiss', $categories, $name, $price, $stock, $description, $product_id);
        if($stmt->execute()) {
            header("Location: product.php?msg=updated");
            exit;
        } else {
            $error = "Database Error: ".$conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - StoreAdmin</title>
    
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
        
        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: 260px;
            padding: 30px;
            min-height: 100vh;
        }
        
        /* ===== FORM CARD ===== */
        .form-card {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            max-width: 700px;
            margin: 0 auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.04);
            transition: all 0.3s;
        }
        
        .form-card:hover {
            box-shadow: 0 15px 50px rgba(0,0,0,0.12);
        }
        
        .form-card .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-card .form-header .icon-circle {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 28px;
            color: #fff;
        }
        
        .form-card .form-header h3 {
            font-weight: 700;
            color: #1a1a2e;
            margin: 0;
        }
        
        .form-card .form-header p {
            color: #6c757d;
            font-size: 14px;
            margin: 5px 0 0;
        }
        
        .form-card .form-label {
            font-weight: 600;
            color: #1a1a2e;
            font-size: 13px;
            margin-bottom: 6px;
        }
        
        .form-card .form-label .required {
            color: #dc3545;
            margin-left: 3px;
        }
        
        .form-card .form-control, .form-card .form-select {
            border-radius: 10px;
            padding: 12px 16px;
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
        
        .form-card .form-control::placeholder {
            color: #adb5bd;
            font-size: 13px;
        }
        
        .form-card .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px 0 0 10px;
            color: #6c757d;
            font-weight: 500;
        }
        
        .form-card .input-group .form-control {
            border-radius: 0 10px 10px 0;
        }
        
        /* Current Image Preview */
        .current-image {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 16px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        
        .current-image img {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #dee2e6;
        }
        
        .current-image .image-info {
            flex: 1;
        }
        
        .current-image .image-info .file-name {
            font-weight: 600;
            color: #1a1a2e;
            font-size: 14px;
        }
        
        .current-image .image-info .file-size {
            color: #6c757d;
            font-size: 12px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            color: #fff;
            width: 100%;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.35);
            color: #fff;
        }
        
        .btn-submit i {
            margin-right: 8px;
        }
        
        .btn-back {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14px;
            border: 2px solid #e9ecef;
            color: #6c757d;
            background: transparent;
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
        
        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .form-footer .text-muted {
            font-size: 13px;
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
            
            .form-card {
                padding: 30px 20px;
            }
            
            .form-footer {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
            
            .current-image {
                flex-direction: column;
                text-align: center;
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
    <button class="btn btn-light mb-4 d-md-none" onclick="document.getElementById('sidebar').classList.toggle('show')" style="border-radius: 10px;">
        <i class="fas fa-bars"></i> Menu
    </button>

    <!-- Form Card -->
    <div class="form-card">
        <div class="form-header">
            <div class="icon-circle">
                <i class="fas fa-pen-to-square"></i>
            </div>
            <h3>Edit Product</h3>
            <p>Update your product information</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

            <!-- Category -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-tag me-1 text-primary"></i> Category <span class="required">*</span>
                </label>
                <select name="category" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    <?php
                    $cat_query = $conn->query("SELECT id, name FROM categories ORDER BY name");
                    while($row = $cat_query->fetch_assoc()) {
                        $selected = ($row['id'] == $product['cat_id']) ? 'selected' : '';
                    ?>
                        <option value="<?php echo $row['id']; ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($row['name']); ?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </div>

            <!-- Product Name -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-font me-1 text-primary"></i> Product Name <span class="required">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-box"></i></span>
                    <input type="text" name="name" class="form-control" 
                           value="<?php echo htmlspecialchars($product['name']); ?>" 
                           placeholder="Enter product name" required>
                </div>
            </div>

            <!-- Price & Stock -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fas fa-rupee-sign me-1 text-primary"></i> Price (Rs.) <span class="required">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                        <input type="number" name="price" class="form-control" 
                               value="<?php echo $product['price']; ?>" 
                               placeholder="0.00" step="0.01" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fas fa-boxes me-1 text-primary"></i> Stock <span class="required">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-cubes"></i></span>
                        <input type="number" name="stock" class="form-control" 
                               value="<?php echo $product['stock']; ?>" 
                               placeholder="Enter stock quantity" required>
                    </div>
                </div>
            </div>

            <!-- Current Image -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-image me-1 text-primary"></i> Current Image
                </label>
                <div class="current-image">
                    <img src="../upload/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    <div class="image-info">
                        <div class="file-name"><?php echo $product['image']; ?></div>
                        <div class="file-size">Current image</div>
                    </div>
                    <span class="badge bg-primary rounded-pill">Active</span>
                </div>
            </div>

            <!-- New Image -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-upload me-1 text-primary"></i> Change Image (Optional)
                </label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <small class="text-muted mt-1 d-block">
                    <i class="fas fa-info-circle me-1"></i> Leave empty to keep current image. 
                    Allowed: JPG, JPEG, PNG, GIF
                </small>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-align-left me-1 text-primary"></i> Description
                </label>
                <textarea name="description" class="form-control" rows="4" 
                          placeholder="Enter product description"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" name="update" class="btn-submit">
                <i class="fas fa-save"></i> Update Product
            </button>

            <div class="form-footer">
                <a href="product.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
                <span class="text-muted">
                    <i class="far fa-clock me-1"></i> Last updated: <?php echo date('d M Y, h:i A'); ?>
                </span>
            </div>
        </form>
    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>