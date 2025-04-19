<?php
session_start();
require_once '../include/db.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Find user in database
    $stmt = $conn->prepare("SELECT id, username, email, password, full_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_full_name'] = $user['full_name'];
            $_SESSION['user_logged_in'] = true;
            
            // Return JSON response for AJAX
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'redirect' => 'index.php']);
                exit();
            } else {
                // Redirect to home page
                header('Location: index.php');
                exit();
            }
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Invalid email or password";
    }
}

// Return JSON error for AJAX
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $error ?? 'Login failed']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streamly | Login</title>
    <link rel="icon" type="image/png" href="../images/fav-logo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Style -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <!-- Login/Signup Container -->
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">Stream<span>ly</span></div>
                <h2 class="auth-title">Welcome Back</h2>
                <p class="auth-subtitle">Sign in to continue to your account</p>
            </div>

            <form class="auth-form" method="POST" action="login-user.php" id="loginForm">
                <?php if (isset($error) && empty($_SERVER['HTTP_X_REQUESTED_WITH'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                </div>

                <div class="auth-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="auth-btn" id="loginBtn">
                    <span id="btnText">Sign In</span>
                    <span id="btnSpinner" class="spinner-border spinner-border-sm d-none"></span>
                </button>

                <div class="auth-divider">or continue with</div>

                <div class="social-login">
                    <button type="button" class="social-btn">
                        <i class="fab fa-google"></i> Sign in with Google 
                    </button>
                    <button type="button" class="social-btn">
                        <i class="fab fa-apple"></i> Sign in with Apple
                    </button>
                    <button type="button" class="social-btn">
                        <i class="fab fa-facebook-f"></i> Sign in with Facebook
                    </button>
                </div>

                <div class="auth-footer">
                    Don't have an account? <a href="signup-user.php">Sign up</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    $(document).ready(function() {
        // Show success message if redirected from signup
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            Swal.fire({
                title: 'Success!',
                text: 'Your account has been created. Please log in.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        }

        // Toggle password visibility
        $('#togglePassword').click(function() {
            const password = $('#password');
            const type = password.attr('type') === 'password' ? 'text' : 'password';
            password.attr('type', type);
            $(this).toggleClass('fa-eye-slash');
        });

        // Form submission with loading
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const loginBtn = $('#loginBtn');
            const btnText = $('#btnText');
            const btnSpinner = $('#btnSpinner');
            const loadingOverlay = $('#loadingOverlay');
            
            // Show loading state
            btnText.text('Signing in...');
            btnSpinner.removeClass('d-none');
            loginBtn.prop('disabled', true);
            loadingOverlay.fadeIn();
            
            // Submit form via AJAX
            $.ajax({
                url: form.attr('action'),
                method: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show loading for 2 seconds before redirect
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 2000);
                    } else {
                        loadingOverlay.fadeOut();
                        Swal.fire({
                            title: 'Error!',
                            text: response.error || 'Login failed',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    loadingOverlay.fadeOut();
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred: ' + error,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    btnText.text('Sign In');
                    btnSpinner.addClass('d-none');
                    loginBtn.prop('disabled', false);
                }
            });
        });
    });
</body>
</html>