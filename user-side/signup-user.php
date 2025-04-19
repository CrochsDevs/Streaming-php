<?php
session_start();
require_once '../include/db.php';

// Set content type to JSON for AJAX requests
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
}

// Initialize response array
$response = ['success' => false, 'error' => ''];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($email)) {
        $response['error'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = "Invalid email format";
    } elseif (empty($password)) {
        $response['error'] = "Password is required";
    } elseif ($password !== $confirmPassword) {
        $response['error'] = "Passwords don't match";
    } elseif (strlen($password) < 8) {
        $response['error'] = "Password must be at least 8 characters";
    } elseif (empty($_POST['terms'])) {
        $response['error'] = "You must agree to the terms and conditions";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $response['error'] = "Email already registered";
        } else {
            // Hash password and create user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $username = explode('@', $email)[0]; // Simple username from email
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $username, $email, $hashedPassword, $username);
            
            if ($stmt->execute()) {
                // Don't log in automatically - just redirect to login page
                $response['success'] = true;
                $response['redirect'] = 'login-user.php?signup=success';
            } else {
                $response['error'] = "Registration failed. Please try again. Error: " . $conn->error;
            }
        }
    }
    
    // If this is an AJAX request, return JSON and exit
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode($response);
        exit();
    }
    
    // For non-AJAX submissions
    if ($response['success']) {
        header('Location: login-user.php?signup=success');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streamly | Sign Up</title>
    <link rel="icon" type="image/png" href="../images/fav-logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">Stream<span>ly</span></div>
                <h2 class="auth-title">Create Account</h2>
                <p class="auth-subtitle">Join Streamly to enjoy unlimited content</p>
            </div>

            <?php if (!empty($response['error']) && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($response['error']); ?></div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="signup-user.php" id="signupForm">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" 
                           placeholder="Create a password (min 8 characters)" required minlength="8">
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    <div class="password-strength mt-1">
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small id="passwordHelp" class="form-text text-muted">Password strength</small>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirmPassword" class="form-control" 
                           placeholder="Confirm your password" required minlength="8">
                    <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                    <div id="passwordMatch" class="mt-1"></div>
                </div>

                <div class="terms-check">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a></label>
                </div>

                <button type="submit" class="auth-btn" id="submitBtn" style="margin-top:30px;">
                    <span id="btnText">Create Account</span>
                    <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>

                <div class="auth-divider">or sign up with</div>

                <div class="social-login">
                    <button type="button" class="social-btn">
                        <i class="fab fa-google"></i> Google
                    </button>
                    <button type="button" class="social-btn">
                        <i class="fab fa-apple"></i> Apple
                    </button>
                    <button type="button" class="social-btn">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </button>
                </div>
                
                <div class="auth-footer">
                    Already have an account? <a href="login-user.php">Sign in</a>
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
        // Toggle password visibility
        $('#togglePassword').click(function() {
            const password = $('#password');
            const type = password.attr('type') === 'password' ? 'text' : 'password';
            password.attr('type', type);
            $(this).toggleClass('fa-eye-slash');
        });

        $('#toggleConfirmPassword').click(function() {
            const confirmPassword = $('#confirmPassword');
            const type = confirmPassword.attr('type') === 'password' ? 'text' : 'password';
            confirmPassword.attr('type', type);
            $(this).toggleClass('fa-eye-slash');
        });

        // Password strength indicator
        $('#password').on('input', function() {
            const password = $(this).val();
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 25;
            
            // Character type checks
            if (password.match(/[A-Z]/)) strength += 15;
            if (password.match(/[a-z]/)) strength += 15;
            if (password.match(/[0-9]/)) strength += 10;
            if (password.match(/[^A-Za-z0-9]/)) strength += 10;
            
            // Update progress bar
            $('#passwordStrength').css('width', strength + '%');
            
            // Update color based on strength
            if (strength < 50) {
                $('#passwordStrength').removeClass('bg-warning bg-success').addClass('bg-danger');
            } else if (strength < 75) {
                $('#passwordStrength').removeClass('bg-danger bg-success').addClass('bg-warning');
            } else {
                $('#passwordStrength').removeClass('bg-danger bg-warning').addClass('bg-success');
            }
        });

        // Password match checker
        $('#confirmPassword').on('input', function() {
            const password = $('#password').val();
            const confirmPassword = $(this).val();
            
            if (confirmPassword.length === 0) {
                $('#passwordMatch').html('');
            } else if (password === confirmPassword) {
                $('#passwordMatch').html('<small class="text-success"><i class="fas fa-check-circle"></i> Passwords match</small>');
            } else {
                $('#passwordMatch').html('<small class="text-danger"><i class="fas fa-times-circle"></i> Passwords do not match</small>');
            }
        });

        // Form submission with AJAX
        $('#signupForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = $('#submitBtn');
            const btnText = $('#btnText');
            const btnSpinner = $('#btnSpinner');
            
            // Validate form
            const password = $('#password').val();
            const confirmPassword = $('#confirmPassword').val();
            
            if (password !== confirmPassword) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Passwords do not match',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            if (!$('#terms').is(':checked')) {
                Swal.fire({
                    title: 'Error!',
                    text: 'You must agree to the terms and conditions',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Show loading state
            btnText.text('Creating Account...');
            btnSpinner.removeClass('d-none');
            submitBtn.prop('disabled', true);
            
            // Submit form via AJAX
            $.ajax({
                url: form.attr('action'),
                method: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Account created successfully. Please log in.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = response.redirect;
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.error || 'Registration failed',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Handle JSON parse error
                    if (xhr.responseText && xhr.responseText[0] === '<') {
                        Swal.fire({
                            title: 'Error!',
                            text: 'The server returned an HTML page instead of JSON',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred: ' + error,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                complete: function() {
                    btnText.text('Create Account');
                    btnSpinner.addClass('d-none');
                    submitBtn.prop('disabled', false);
                }
            });
        });
    });
    </script>
</body>
</html>