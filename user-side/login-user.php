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

    <!-- Login/Signup Container -->
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">Stream<span>ly</span></div>
                <h2 class="auth-title">Welcome Back</h2>
                <p class="auth-subtitle">Sign in to continue to your account</p>
            </div>

            <form class="auth-form">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" id="password" class="form-control" placeholder="Enter your password" required>
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                </div>

                <div class="auth-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="auth-btn">Sign In</button>

                <div class="auth-divider">or continue with</div>

                <div class="social-login">
                    <button type="button" class="social-btn">
                        <i class="fab fa-google"></i> Signin with Google 
                    </button>
                    <button type="button" class="social-btn">
                        <i class="fab fa-apple"></i> Signin with Apple
                    </button>
                    <button type="button" class="social-btn">
                        <i class="fab fa-facebook-f"></i> Signin with Facebook
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
    <script>
        // Toggle password visibility
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
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