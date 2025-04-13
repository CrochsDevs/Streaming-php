<?php
// theme.php - This will be included in all pages

// Initialize theme if not set
if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'light';
}

// Function to get current theme
function getCurrentTheme() {
    if ($_SESSION['theme'] === 'system') {
        // Check system preference
        if (isset($_COOKIE['prefersDark'])) {
            return $_COOKIE['prefersDark'] === 'true' ? 'dark' : 'light';
        }
        return 'light'; // Default if cookie not set
    }
    return $_SESSION['theme'];
}
?>
