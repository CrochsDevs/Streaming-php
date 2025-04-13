<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Get destination from query parameter
$destination = isset($_GET['destination']) ? $_GET['destination'] : 'dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streamly | Loading</title>
    <link rel="icon" type="image/png" href="../images/fav-logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="loading-page">
    <div class="loading-container">
        <div class="loading-spinner"></div>
        <div class="loading-text">Loading Admin Dashboard...</div>
    </div>

    <script>
        // Redirect after 2 seconds
        setTimeout(function() {
            window.location.href = '<?php echo $destination; ?>';
        }, 2000);
    </script>
</body>
</html>