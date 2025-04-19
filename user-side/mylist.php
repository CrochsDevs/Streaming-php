<?php
session_start();
require_once '../include/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login-user.php');
    exit();
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['user_username'];
$error = '';
$success = '';

// Check if user_lists table exists
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'user_lists'");

if (mysqli_num_rows($tableCheck) > 0) {
    // Handle remove from list if table exists
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_item'])) {
        $contentId = mysqli_real_escape_string($conn, $_POST['content_id']);

        $deleteQuery = "DELETE FROM user_lists WHERE user_id = ? AND content_id = ?";
        $stmt = mysqli_prepare($conn, $deleteQuery);
        mysqli_stmt_bind_param($stmt, 'ii', $userId, $contentId);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Item removed from your list";
            header("Refresh:0");
            exit();
        } else {
            $error = "Error removing item: " . mysqli_error($conn);
        }
    }

    // Check if contents table exists before querying
    $contentsTableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'contents'");
    if (mysqli_num_rows($contentsTableCheck) > 0) {
        // Get user's list items
        $listQuery = "SELECT c.id, c.title, c.poster_path, c.type, c.release_year, c.genre, ul.added_at 
                      FROM user_lists ul
                      JOIN contents c ON ul.content_id = c.id
                      WHERE ul.user_id = ?
                      ORDER BY ul.added_at DESC";
        $stmt = mysqli_prepare($conn, $listQuery);
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $listItems = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $listItems = [];
        $error = "Contents table not found in database";
    }
} else {
    $listItems = [];
    $error = "Your list is not available at this time. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My List - Streamly</title>
    <link rel="icon" type="image/png" href="../images/fav-logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/index.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --dark-color: #1a1a1a;
            --light-color: #f8f9fa;
            --success-color: #198754;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--dark-color) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand img {
            height: 40px;
        }
        
        .list-header {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.8), rgba(25, 135, 84, 0.8)), 
                        url('../images/list-bg.jpg');
            background-size: cover;
            background-position: center;
            padding: 6rem 0;
            color: white;
            margin-bottom: 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .list-header h1 {
            font-weight: 700;
            font-size: 3rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .list-header p.lead {
            font-size: 1.25rem;
            opacity: 0.9;
        }
        
        .content-card {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background: white;
            height: 100%;
        }
        
        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .card-img-top {
            height: 350px;
            object-fit: cover;
            width: 100%;
        }
        
        .card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .content-card:hover .card-overlay {
            opacity: 1;
        }
        
        .badge-type {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 5px 8px;
            border-radius: 4px;
        }
        
        .empty-list {
            text-align: center;
            padding: 5rem 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }
        
        .empty-list i {
            font-size: 5rem;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            opacity: 0.7;
        }
        
        .empty-list h3 {
            color: var(--dark-color);
            margin-bottom: 1rem;
        }
        
        .empty-list p.lead {
            color: var(--secondary-color);
            margin-bottom: 2rem;
        }
        
        .sort-options {
            margin-bottom: 2rem;
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }
        
        .input-group-text {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .form-select {
            border-left: none;
        }
        
        .item-added {
            font-size: 0.8rem;
            color: var(--secondary-color);
        }
        
        .content-title {
            font-weight: 600;
            margin-top: 0.5rem;
            color: var(--dark-color);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .content-meta {
            padding: 0.75rem;
        }
        
        footer {
            background-color: var(--dark-color);
            color: white;
            padding: 3rem 0;
            margin-top: 4rem;
        }
        
        footer h5 {
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        footer a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        footer a:hover {
            color: white;
        }
        
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--dark-color);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .preloader-content {
            text-align: center;
        }
        
        .preloader-logo {
            height: 60px;
            margin-bottom: 20px;
            animation: pulse 1.5s infinite;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .list-header {
                padding: 4rem 0;
            }
            
            .list-header h1 {
                font-size: 2.5rem;
            }
            
            .card-img-top {
                height: 300px;
            }
        }
    </style>
</head>
<body>

    <!-- Preloader -->
    <div class="preloader">
        <div class="preloader-content">
            <img src="../images/fav-logo.png" alt="Streamly Logo" class="preloader-logo">
            <div class="spinner"></div>
        </div>
    </div>

    <!-- Header/Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="../images/logo.png" alt="Streamly Logo" class="logo-img me-2">
                <span class="fw-bold">Streamly</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="movies.php">Movies</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tvshows.php">TV Shows</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($username); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="mylist.php"><i class="fas fa-list me-2"></i>My List</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- List Header -->
    <section class="list-header">
        <div class="container">
            <h1>My List</h1>
            <p class="lead">All your saved movies and TV shows in one place</p>
        </div>
    </section>

    <!-- List Content -->
    <div class="container mb-5">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (empty($listItems)): ?>
            <div class="empty-list">
                <i class="fas fa-list"></i>
                <h3>Your list is empty</h3>
                <p class="lead">Start adding movies and TV shows to your list</p>
                <div class="d-flex justify-content-center">
                    <a href="movies.php" class="btn btn-primary btn-lg mt-3 me-3">
                        <i class="fas fa-film me-2"></i> Browse Movies
                    </a>
                    <a href="tvshows.php" class="btn btn-success btn-lg mt-3">
                        <i class="fas fa-tv me-2"></i> Browse TV Shows
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="sort-options">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-filter"></i></span>
                            <select class="form-select" id="filterType">
                                <option value="all">All Items</option>
                                <option value="movie">Movies Only</option>
                                <option value="tv">TV Shows Only</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-sort"></i></span>
                            <select class="form-select" id="sortBy">
                                <option value="recent">Recently Added</option>
                                <option value="oldest">Oldest Added</option>
                                <option value="title">Title (A-Z)</option>
                                <option value="year">Release Year</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4" id="listContainer">
                <?php foreach ($listItems as $item): ?>
                    <div class="col" data-type="<?php echo $item['type']; ?>" data-added="<?php echo strtotime($item['added_at']); ?>" data-title="<?php echo strtolower($item['title']); ?>" data-year="<?php echo $item['release_year']; ?>">
                        <div class="content-card h-100">
                            <img src="<?php echo htmlspecialchars($item['poster_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <span class="badge bg-<?php echo $item['type'] == 'movie' ? 'primary' : 'success'; ?> badge-type">
                                <?php echo strtoupper($item['type']); ?>
                            </span>
                            <div class="card-overlay">
                                <a href="watch.php?id=<?php echo $item['id']; ?>&type=<?php echo $item['type']; ?>" class="btn btn-primary btn-sm me-2">
                                    <i class="fas fa-play"></i>
                                </a>
                                <form method="POST" action="" class="d-inline">
                                    <input type="hidden" name="content_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="remove_item" class="btn btn-outline-light btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="content-meta">
                                <h6 class="content-title"><?php echo htmlspecialchars($item['title']); ?></h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><?php echo $item['release_year']; ?></span>
                                    <span class="item-added small">Added <?php echo date('M j, Y', strtotime($item['added_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="text-uppercase mb-4">Streamly</h5>
                    <p class="text-muted">Your favorite streaming platform for movies and TV shows. Enjoy unlimited entertainment anytime, anywhere.</p>
                    <div class="mt-4">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-uppercase mb-4">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-muted">Home</a></li>
                        <li class="mb-2"><a href="movies.php" class="text-muted">Movies</a></li>
                        <li class="mb-2"><a href="tvshows.php" class="text-muted">TV Shows</a></li>
                        <li class="mb-2"><a href="new-releases.php" class="text-muted">New Releases</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-uppercase mb-4">Account</h5>
                    <ul class="dropdown-menu dropdown-menu-end" style="background-color: #1a1a1a; color: #fff;">
                        <li><a class="dropdown-item" href="profile.php" style="color: #fff !important;"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="mylist.php" style="color: #fff !important;"><i class="fas fa-list me-2"></i>My List</a></li>
                        <li><hr class="dropdown-divider" style="border-color: #333;"></li>
                        <li><a class="dropdown-item" href="" id="logoutBtn" style="color: #fff !important;"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="text-uppercase mb-4">Newsletter</h5>
                    <p class="text-muted">Subscribe to our newsletter for the latest updates.</p>
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Your email" aria-label="Your email">
                        <button class="btn btn-primary" type="button">Subscribe</button>
                    </div>
                </div>
            </div>
            <hr class="my-4 bg-secondary">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="small text-muted mb-0">&copy; <?php echo date('Y'); ?> Streamly. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <ul class="list-inline small mb-0">
                        <li class="list-inline-item"><a href="#" class="text-muted">Privacy Policy</a></li>
                        <li class="list-inline-item"><a href="#" class="text-muted">Terms of Service</a></li>
                        <li class="list-inline-item"><a href="#" class="text-muted">Contact Us</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    $(document).ready(function() {
        // Hide preloader when page loads
        setTimeout(function() {
            $('.preloader').fadeOut();
        }, 1000);

        // Filter and sort functionality
        $('#filterType, #sortBy').change(function() {
            const filterType = $('#filterType').val();
            const sortBy = $('#sortBy').val();
            
            // Get all items
            const items = $('#listContainer .col').get();
            
            // Filter items
            const filteredItems = items.filter(item => {
                if (filterType === 'all') return true;
                return $(item).data('type') === filterType;
            });
            
            // Sort items
            filteredItems.sort((a, b) => {
                if (sortBy === 'recent') {
                    return $(b).data('added') - $(a).data('added');
                } else if (sortBy === 'oldest') {
                    return $(a).data('added') - $(b).data('added');
                } else if (sortBy === 'title') {
                    return $(a).data('title').localeCompare($(b).data('title'));
                } else if (sortBy === 'year') {
                    return $(b).data('year') - $(a).data('year');
                }
                return 0;
            });
            
            // Re-append items in new order
            $('#listContainer').empty().append(filteredItems);
        });

        // Confirm before removing item
        $('form[method="POST"]').submit(function(e) {
            e.preventDefault();
            const form = this;
            
            Swal.fire({
                title: 'Remove from list?',
                text: "Are you sure you want to remove this item from your list?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, remove it!',
                cancelButtonText: 'Cancel',
                backdrop: 'rgba(0,0,0,0.7)'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
    </script>
</body>
</html>