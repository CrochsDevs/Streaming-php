<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streamly | Login</title>
    <link rel="icon" type="image/png" href="../images/fav-logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Style -->
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

            <form class="auth-form">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" id="password" class="form-control" placeholder="Create a password" required>
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" id="confirmPassword" class="form-control" placeholder="Confirm your password" required>
                    <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                </div>

                <div class="terms-check">
                    <input type="checkbox" id="terms" required>
                    <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                </div>

                <button type="submit" class="auth-btn" style="margin-top:30px;">Create Account</button>

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
    <script>
        // Toggle password visibility
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
        const confirmPassword = document.querySelector('#confirmPassword');
        
        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
      

        // Form submission
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add your form submission logic here
            console.log('Form submitted');
        });
    </script>
</body>
</html>