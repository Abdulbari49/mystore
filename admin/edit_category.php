<?php

include('../include/config.php');

$id = $_GET['id'];
$stmt =  $conn->prepare("SELECT * FROM categories where id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();

$result = $stmt->get_result();
$categories = $result->fetch_assoc();

if(isset($_POST['update'])){
    $id = $_POST['id'];
    $name = $_POST['name'];
    $slug = $_POST['slug'];

    $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ? where id = ?");
    $stmt->bind_param('ssi', $name, $slug, $id);
    if($stmt->execute()){
        header("Location: category.php?msg=updated");
        exit;
    }else{
        die("Error: " . $stmt->error);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
    
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
        
        /* ===== NAVBAR ===== */
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px 30px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .navbar-custom .brand {
            color: #fff;
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
        }
        
        .navbar-custom .brand i {
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
        
        /* ===== FORM CONTAINER ===== */
        .form-wrapper {
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        
        .form-card {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            max-width: 520px;
            width: 100%;
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
        
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
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
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .form-footer .text-muted {
            font-size: 13px;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 576px) {
            .form-card {
                padding: 30px 20px;
            }
            
            .navbar-custom .brand {
                font-size: 20px;
            }
            
            .form-footer {
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
</head>
<body>

    <!-- ===== NAVBAR ===== -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="brand" href="dashboard.php">
                <i class="fas fa-store-alt"></i> StoreAdmin
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-chart-pie"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="category.php">
                            <i class="fas fa-tags"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="product.php">
                            <i class="fas fa-box"></i> Products
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ===== FORM ===== -->
    <div class="form-wrapper">
        <div class="form-card">
            <div class="form-header">
                <div class="icon-circle">
                    <i class="fas fa-pen-to-square"></i>
                </div>
                <h3>Edit Category</h3>
                <p>Update your category information</p>
            </div>

            <form action="" method="post">
                <input type="hidden" name="id" value="<?php echo $categories['id']; ?>">

                <!-- Category Name -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-tag me-1 text-primary"></i> Category Name
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-font"></i></span>
                        <input type="text" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($categories['name']); ?>" 
                               placeholder="Enter category name" required>
                    </div>
                </div>

                <!-- Slug -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-link me-1 text-primary"></i> Slug
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                        <input type="text" name="slug" class="form-control" 
                               value="<?php echo htmlspecialchars($categories['slug']); ?>" 
                               placeholder="e.g. electronics" required>
                    </div>
                    <small class="text-muted mt-1 d-block">
                        <i class="fas fa-info-circle me-1"></i> Used in URLs, keep it short and descriptive
                    </small>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="update" class="btn-submit">
                    <i class="fas fa-arrow-right"></i> Update Category
                </button>

                <div class="form-footer">
                    <a href="category.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Categories
                    </a>
                    <span class="text-muted">
                        <i class="far fa-clock me-1"></i> Last updated: <?php echo date('d M Y'); ?>
                    </span>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>