<?php
session_start();
require_once '../include/db.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_logged_in']);
$username = $isLoggedIn ? $_SESSION['user_username'] : '';
$fullName = $isLoggedIn ? $_SESSION['user_full_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streamly - Watch Movies & TV Shows</title>
    <link rel="icon" type="image/png" href="../images/fav-logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/index.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
            </a>

            <!-- Search Bar -->
            <div class="search-container mx-3">
                <div class="input-group" >
                    <input type="text" class="form-control search-input" placeholder="Search movies, TV shows..." >
                    <button class="btn btn-primary search-btn" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                    <div class="search-results dropdown-menu"></div>
                </div>
            </div>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="movies.php">Movies</a></li>
                    <li class="nav-item"><a class="nav-link" href="tvshows.php">TV Shows</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="genreDropdown" role="button" data-bs-toggle="dropdown">
                            Genres
                        </a>
                        <ul class="dropdown-menu genre-dropdown">
                            <li><h6 class="dropdown-header">Movie Genres</h6></li>
                            <li><a class="dropdown-item" href="genre.php?type=movie&genre=action">Action</a></li>
                            <li><a class="dropdown-item" href="genre.php?type=movie&genre=comedy">Comedy</a></li>
                            <li><a class="dropdown-item" href="genre.php?type=movie&genre=drama">Drama</a></li>
                            <li><a class="dropdown-item" href="genre.php?type=movie&genre=horror">Horror</a></li>
                            <li><a class="dropdown-item" href="genre.php?type=movie&genre=sci-fi">Sci-Fi</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">TV Show Genres</h6></li>
                            <li><a class="dropdown-item" href="genre.php?type=tv&genre=crime">Crime</a></li>
                            <li><a class="dropdown-item" href="genre.php?type=tv&genre=fantasy">Fantasy</a></li>
                            <li><a class="dropdown-item" href="genre.php?type=tv&genre=romance">Romance</a></li>
                            <li><a class="dropdown-item" href="genre.php?type=tv&genre=thriller">Thriller</a></li>
                            <li><a class="dropdown-item" href="genre.php?type=tv&genre=documentary">Documentary</a></li>
                        </ul>
                    </li>
                </ul>
                <div class="ms-3 d-flex">
                    <?php if ($isLoggedIn): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($username); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" style="background-color: #1a1a1a; color: #fff;">
                                <li><a class="dropdown-item" href="profile.php" style="color: #fff !important;"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="mylist.php" style="color: #fff !important;"><i class="fas fa-list me-2"></i>My List</a></li>
                                <li><hr class="dropdown-divider" style="border-color: #333;"></li>
                                <li><a class="dropdown-item" href="" id="logoutBtn" style="color: #fff !important;"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login-user.php" class="btn btn-outline-light me-2">
                            <i class="fas fa-sign-in-alt me-1"></i> Sign In
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="hero-content">
            <h1 class="hero-title">The Mandalorian</h1>
            <p class="hero-description">After the fall of the Galactic Empire, a lone gunfighter makes his way through the lawless galaxy.</p>
            <div class="hero-buttons">
                <button class="btn btn-primary btn-lg me-3"><i class="fas fa-play"></i> Watch Now</button>
                <button class="btn btn-outline-light btn-lg"><i class="fas fa-info-circle"></i> More Info</button>
            </div>
        </div>
    </section>

    <!-- Content Sections -->
    <div class="container-fluid content-container">
        <!-- Trending Now -->
        <section class="content-section">
            <h2 class="section-title">Trending Now</h2>
            <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-4">
                <?php for($i=1; $i<=6; $i++): ?>
                <div class="col">
                    <div class="content-card">
                        <img src="https://via.placeholder.com/300x450?text=Movie+<?= $i ?>" class="card-img-top" alt="Movie <?= $i ?>">
                        <div class="card-overlay">
                            <button class="btn btn-primary btn-sm"><i class="fas fa-play"></i></button>
                            <button class="btn btn-outline-light btn-sm btn-add-to-list"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </section>

        <!-- Popular Movies -->
        <section class="content-section">
            <h2 class="section-title">Popular Movies</h2>
            <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-4">
                <?php for($i=7; $i<=12; $i++): ?>
                <div class="col">
                    <div class="content-card">
                        <img src="https://via.placeholder.com/300x450?text=Movie+<?= $i ?>" class="card-img-top" alt="Movie <?= $i ?>">
                        <div class="card-overlay">
                            <button class="btn btn-primary btn-sm"><i class="fas fa-play"></i></button>
                            <button class="btn btn-outline-light btn-sm btn-add-to-list"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </section>

        <!-- TV Shows -->
        <section class="content-section">
            <h2 class="section-title">Popular TV Shows</h2>
            <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-4">
                <?php for($i=13; $i<=18; $i++): ?>
                <div class="col">
                    <div class="content-card">
                        <img src="https://via.placeholder.com/300x450?text=TV+Show+<?= $i ?>" class="card-img-top" alt="TV Show <?= $i ?>">
                        <div class="card-overlay">
                            <button class="btn btn-primary btn-sm"><i class="fas fa-play"></i></button>
                            <button class="btn btn-outline-light btn-sm btn-add-to-list"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <h3>Stream<span>ly</span></h3>
                    <p>Your favorite movies and TV shows, all in one place.</p>
                </div>
                <div class="col-md-3">
                    <h5>Navigation</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                        <li class="nav-item"><a href="movies.php" class="nav-link">Movies</a></li>
                        <li class="nav-item"><a href="tvshows.php" class="nav-link">TV Shows</a></li>
                        <li class="nav-item"><a href="my-list.php" class="nav-link">My List</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Legal</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a href="terms.php" class="nav-link">Terms of Service</a></li>
                        <li class="nav-item"><a href="privacy.php" class="nav-link">Privacy Policy</a></li>
                        <li class="nav-item"><a href="cookies.php" class="nav-link">Cookie Policy</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Connect With Us</h5>
                    <div class="social-links">
                        <a href="#" class="me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-0">&copy; 2025 Streamly. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom JS -->
    <script src="../js/index.js"></script>

    <script>
    $(document).ready(function() {
        // Handle logout
        $('#logoutBtn').click(function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Logout?',
                text: 'Are you sure you want to logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, logout!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        });

        // Handle add to list buttons
        $('.btn-add-to-list').click(function(e) {
            e.preventDefault();
            <?php if ($isLoggedIn): ?>
                // Add to list logic for logged in users
                const card = $(this).closest('.content-card');
                const title = card.find('img').attr('alt');
                
                $.ajax({
                    url: 'add-to-list.php',
                    method: 'POST',
                    data: { title: title },
                    success: function(response) {
                        Swal.fire({
                            title: 'Added!',
                            text: title + ' has been added to your list',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to add to list',
                            icon: 'error'
                        });
                    }
                });
            <?php else: ?>
                // Prompt login for guests
                Swal.fire({
                    title: 'Login Required',
                    text: 'You need to be logged in to add items to your list',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Login',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'login-user.php';
                    }
                });
            <?php endif; ?>
        });

        // Hide preloader when page loads
        setTimeout(function() {
            $('.preloader').fadeOut();
        }, 1000);
    });
    </script>
</body>
</html>