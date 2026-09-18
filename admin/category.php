<?php
session_start();
include("../include/config.php");

if(isset($_POST['add_category'])){
    $name = $_POST['name'];
    $slug = $_POST['slug'];
    
    // Fix: Corrected variable name from $slu to $slug
    $stmt = $conn->prepare("INSERT INTO categories(name, slug) VALUES(?, ?)");
    $stmt->bind_param("ss", $name, $slug);
    if($stmt->execute()){
        $success_msg = "Category added successfully!";
    }else{
        $error_msg = "Database error: " . $stmt->error;
    }
}

if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param('i', $id);
    if($stmt->execute()){
        header("Location: category.php");
        exit();
    }else{
        $error_msg = "Delete failed!";
    }
}

// Fetch all categories
$stmt = $conn->prepare("SELECT * FROM categories ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management</title>
    
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
        
        /* ===== FORM CARD ===== */
        .form-card {
            background: #fff;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
        }
        
        .form-card .form-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .form-card .form-header .icon-circle {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
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
        
        .form-card .form-control {
            border-radius: 10px;
            padding: 10px 16px;
            border: 2px solid #e9ecef;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-card .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
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
        
        /* ===== TABLE CARD ===== */
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
        }
        
        .table-card .table-header .search-box input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            outline: none;
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
        
        .table .badge-id {
            background: #f0f2f5;
            color: #6c757d;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        
        .action-btn {
            padding: 6px 14px;
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
        
        /* ===== ALERTS ===== */
        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 16px 20px;
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
    <a href="category.php" class="nav-item active">
        <i class="fas fa-tags"></i> Categories
    </a>
    <a href="product.php" class="nav-item">
        <i class="fas fa-box"></i> Products
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
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h4 class="fw-bold text-dark mb-1">Category Management</h4>
            <p class="text-muted small">Manage your product categories</p>
        </div>
        <span class="badge bg-primary rounded-pill px-3 py-2">
            <i class="far fa-calendar-alt me-1"></i> <?php echo date('F j, Y'); ?>
        </span>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-tags"></i></div>
                <h2 class="stat-number"><?php echo $result->num_rows; ?></h2>
                <p class="stat-label">Total Categories</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-plus-circle"></i></div>
                <h2 class="stat-number">Add New</h2>
                <p class="stat-label">Create categories</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-arrow-trend-up"></i></div>
                <h2 class="stat-number">Active</h2>
                <p class="stat-label">All categories active</p>
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

    <div class="row g-4">
        <!-- Add Category Form -->
        <div class="col-lg-4">
            <div class="form-card">
                <div class="form-header">
                    <div class="icon-circle">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div>
                        <h5>Add Category</h5>
                        <p>Create a new category</p>
                    </div>
                </div>
                
                <form action="" method="post">
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-tag me-1 text-primary"></i> Category Name
                        </label>
                        <input type="text" name="name" class="form-control" 
                               placeholder="Enter category name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-link me-1 text-primary"></i> Slug
                        </label>
                        <input type="text" name="slug" class="form-control" 
                               placeholder="e.g. electronics" required>
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle me-1"></i> Used in URLs, keep it short
                        </small>
                    </div>
                    
                    <button type="submit" name="add_category" class="btn-gradient w-100">
                        <i class="fas fa-plus-circle"></i> Add Category
                    </button>
                </form>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="col-lg-8">
            <div class="table-card">
                <div class="table-header">
                    <h5><i class="fas fa-list me-2 text-primary"></i> All Categories</h5>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchTable" placeholder="Search categories..." onkeyup="filterTable()">
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="categoryTable">
                        <thead>
                            <tr>
                                <th style="width: 60px;">#ID</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><span class="badge-id">#<?php echo $row['id']; ?></span></td>
                                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                        <td><code class="text-muted"><?php echo htmlspecialchars($row['slug']); ?></code></td>
                                        <td>
                                            <a href="edit_category.php?id=<?php echo $row['id']; ?>" 
                                               class="action-btn edit">
                                                <i class="fas fa-pen"></i> Edit
                                            </a>
                                            <a href="?delete=<?php echo $row['id']; ?>" 
                                               class="action-btn delete" 
                                               onclick="return confirm('Are you sure you want to delete this category?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                        No categories found. Add your first category!
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Table Search Filter -->
<script>
function filterTable() {
    var input = document.getElementById("searchTable");
    var filter = input.value.toLowerCase();
    var table = document.getElementById("categoryTable");
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