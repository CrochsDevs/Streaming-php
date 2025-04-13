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
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="../images/logo.png" alt="Streamly Logo" class="logo-img me-2">
        </a>

        <!-- Search Bar -->
        <div class="search-container mx-3">
            <div class="input-group">
                <input type="text" class="form-control search-input" placeholder="Search movies, TV shows...">
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
                <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Movies</a></li>
                <li class="nav-item"><a class="nav-link" href="#">TV Shows</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="genreDropdown" role="button" data-bs-toggle="dropdown">
                        Genres
                    </a>
                    <ul class="dropdown-menu genre-dropdown">
                        <li><h6 class="dropdown-header">Movie Genres</h6></li>
                        <li><a class="dropdown-item" href="#">Action</a></li>
                        <li><a class="dropdown-item" href="#">Comedy</a></li>
                        <li><a class="dropdown-item" href="#">Drama</a></li>
                        <li><a class="dropdown-item" href="#">Horror</a></li>
                        <li><a class="dropdown-item" href="#">Sci-Fi</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">TV Show Genres</h6></li>
                        <li><a class="dropdown-item" href="#">Crime</a></li>
                        <li><a class="dropdown-item" href="#">Fantasy</a></li>
                        <li><a class="dropdown-item" href="#">Romance</a></li>
                        <li><a class="dropdown-item" href="#">Thriller</a></li>
                        <li><a class="dropdown-item" href="#">Documentary</a></li>
                    </ul>
                </li>
            </ul>
            <div class="ms-3 d-flex">
                <button id="signInBtn" class="btn btn-outline-light me-2">
                    <span id="signInText" href="login-user.php">Sign In</span>
                    <span id="signInSpinner" class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
                </button>
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
                            <button class="btn btn-outline-light btn-sm"><i class="fas fa-plus"></i></button>
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
                            <button class="btn btn-outline-light btn-sm"><i class="fas fa-plus"></i></button>
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
                            <button class="btn btn-outline-light btn-sm"><i class="fas fa-plus"></i></button>
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
                        <li class="nav-item"><a href="#" class="nav-link">Home</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Movies</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">TV Shows</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">My List</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Legal</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a href="#" class="nav-link">Terms of Service</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Privacy Policy</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Cookie Policy</a></li>
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
    <!-- Custom JS -->
    <script src="../js/index.js"></script>
</body>

    <!-- Loading Screen (add this before closing </body> tag) -->
    <div id="loadingScreen" class="loading-screen">
        <div class="loading-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Signing you in...</p>
        </div>
    </div>

</html>