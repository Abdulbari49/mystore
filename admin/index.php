<?php
session_start();


include("../include/config.php");

$error = '';
if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email= ?");
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($row = $result->fetch_assoc()){
        if(password_verify($password,$row['password']) && $row['role']=='admin'){
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_name'] = $row['name'];
            header("location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect Password or you are not Admin";
        }
    } else {
        $error = "Email address is not registered";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="login-wrapper">
        <div class="login-box">
            
            <!-- ===== LOGO ===== -->
            <div class="login-logo">
                <div class="icon-wrapper">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1 class="title">Admin Login</h1>
                <p class="subtitle">Sign in to manage your store</p>
            </div>

            <!-- ===== ERROR ===== -->
            <?php if(isset($error)): ?>
                <div class="alert-box alert-danger">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- ===== FORM ===== -->
            <form action="" method="POST" class="login-form" id="loginForm">
                
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" id="email" class="form-input" placeholder="admin@example.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" id="password" class="form-input" placeholder="Enter password" required>
                        <button type="button" class="toggle-btn" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>


                <button type="submit" name="login" class="btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>

            </form>

            <!-- ===== FOOTER ===== -->
            <div class="login-footer">
                <a href="../public/index.php" class="store-link">
                    <i class="fas fa-store"></i> Visit Store
                </a>
            </div>

        </div>
    </div>
</body>
</html>