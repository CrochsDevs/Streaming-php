// Custom JavaScript for Streamly
document.addEventListener('DOMContentLoaded', function() {
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Content card hover effect for touch devices
    const contentCards = document.querySelectorAll('.content-card');
    
    contentCards.forEach(card => {
        card.addEventListener('touchstart', function() {
            this.classList.add('hover');
        });
        
        card.addEventListener('touchend', function() {
            setTimeout(() => {
                this.classList.remove('hover');
            }, 1000);
        });
    });

    // Hero buttons click handlers
    const watchNowBtn = document.querySelector('.hero-buttons .btn-primary');
    const moreInfoBtn = document.querySelector('.hero-buttons .btn-outline-light');
    
    if (watchNowBtn) {
        watchNowBtn.addEventListener('click', function() {
            alert('Playback started! This would normally open the video player.');
        });
    }
    
    if (moreInfoBtn) {
        moreInfoBtn.addEventListener('click', function() {
            alert('More information about this title would be displayed here.');
        });
    }

    // Card overlay buttons
    document.querySelectorAll('.card-overlay .btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const card = this.closest('.content-card');
            const img = card.querySelector('img');
            const title = img.alt;
            
            if (this.querySelector('.fa-play')) {
                alert(`Now playing: ${title}`);
            } else if (this.querySelector('.fa-plus')) {
                alert(`${title} added to your list`);
            }
        });
    });

      // Optional: Logo click animation effect
      const logo = document.querySelector('.logo-img');
      if (logo) {
          logo.addEventListener('click', () => {
              logo.style.transform = 'scale(1.2)';
              setTimeout(() => {
                  logo.style.transform = 'scale(1)';
              }, 300);
          });
      }

      document.addEventListener('DOMContentLoaded', function() {
        const signInBtn = document.getElementById('signInBtn');
        const signInText = document.getElementById('signInText');
        const signInSpinner = document.getElementById('signInSpinner');
        const loadingScreen = document.getElementById('loadingScreen');
    
        signInBtn.addEventListener('click', function() {
            // Show loading state on button
            signInText.textContent = 'Signing In...';
            signInSpinner.classList.remove('d-none');
            signInBtn.disabled = true;
    
            // Show full-page loading screen
            loadingScreen.style.display = 'flex';
    
            // Simulate navigation delay (replace with actual navigation)
            setTimeout(function() {
                // In a real app, this would be your actual navigation code
                window.location.href = 'login.html'; // Replace with your login page URL
                
                // If navigation fails, you should reset the button state
                // signInText.textContent = 'Sign In';
                // signInSpinner.classList.add('d-none');
                // signInBtn.disabled = false;
                // loadingScreen.style.display = 'none';
            }, 1500); // 1.5 second delay for demonstration
        });
    });

    // Preloader
window.addEventListener('load', function() {
    const preloader = document.querySelector('.preloader');
    
    // Add fade-out class after 1.5 seconds (adjust as needed)
    setTimeout(function() {
        preloader.classList.add('fade-out');
        
        // Remove preloader from DOM after animation completes
        setTimeout(function() {
            preloader.style.display = 'none';
        }, 500); // Match this with your CSS transition time
    }, 1500);
});

});