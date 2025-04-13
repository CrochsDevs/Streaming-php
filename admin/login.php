<?php
session_start();
require_once '../include/db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_full_name'] = $admin['full_name'];
                
                // Update last login
                $update_stmt = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $admin['id']);
                $update_stmt->execute();
                
                // Redirect to loading page then dashboard
                header('Location: loading.php?destination=dashboard.php');
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streamly | Admin Login</title>
    <link rel="icon" type="image/png" href="../images/fav-logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-login">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">Stream<span>ly</span> Admin</div>
                <h2 class="auth-title">Admin Login</h2>
                <p class="auth-subtitle">Access your admin dashboard</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                </div>

                <button type="submit" class="auth-btn">Login</button>
                
                <div class="auth-footer">
                    <a href="forgot-password.php">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Admin JS -->
    <script src="js/admin.js"></script>
</body>
</html>