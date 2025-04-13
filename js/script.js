document.addEventListener('DOMContentLoaded', function() {
    // Common elements and functions
    function initPasswordToggle(inputId, toggleId, iconId) {
        const passwordInput = document.getElementById(inputId);
        const togglePassword = document.getElementById(toggleId);
        const eyeIcon = document.getElementById(iconId);

        if (passwordInput && togglePassword && eyeIcon) {
            // Initialize with eye-slash icon (password hidden)
            eyeIcon.classList.add("fa-eye-slash");
            eyeIcon.classList.remove("fa-eye");

            togglePassword.addEventListener("click", function() {
                const isPassword = passwordInput.type === "password";
                passwordInput.type = isPassword ? "text" : "password";
                eyeIcon.classList.toggle("fa-eye");
                eyeIcon.classList.toggle("fa-eye-slash");
            });
        }
    }

    function setupInputAnimations() {
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentNode.style.transform = 'scale(1)';
            });
        });
    }

    // Login Page Specific
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        initPasswordToggle("password", "togglePassword", "eyeIcon");

        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                alert('Please fill in all fields');
                return;
            }
            
            console.log('Login attempt with:', { username, password });
            alert('Login successful! (This is a demo)');
        });
    }

    // Signup Page Specific
    const signupForm = document.getElementById("signupForm");
    if (signupForm) {
        initPasswordToggle("password", "togglePassword", "eyeIcon");
        initPasswordToggle("confirmPassword", "toggleConfirmPassword", "confirmEyeIcon");

        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }
            
            const formData = {
                fullname: document.getElementById('fullname').value.trim(),
                email: document.getElementById('email').value.trim(),
                username: document.getElementById('username').value.trim(),
                password: password
            };
            
            console.log('Signup data:', formData);
            alert('Account created successfully! (This is a demo)');
        });
    }

    // Common setup
    setupInputAnimations();

});

