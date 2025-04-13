<?php
session_start();
require_once '../include/db.php';
require_once 'theme.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    header('Location: movies.php');
    exit();
}

$id = (int)$_GET['id'];

// Get movie data
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();

if (!$movie) {
    header('Location: movies.php');
    exit();
}

$currentTheme = getCurrentTheme();
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $currentTheme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streamly | View Movie</title>
    <link rel="icon" type="image/png" href="../images/fav-logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="css/admin.css">
    </head>
<body class="admin-dashboard">
    
<body class="admin-dashboard">
<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">Stream<span>ly</span></div>
    </div>
    <div class="sidebar-user">
        <div class="user-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['admin_full_name']); ?></div>
            <div class="user-email"><?php echo htmlspecialchars($_SESSION['admin_email']); ?></div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <!-- Updated active class here for Movies page -->
            <li class="active">
                <a href="movies.php">
                    <i class="fas fa-film"></i>
                    <span>Movies</span>
                </a>
            </li>
            <li>
                <a href="users.php">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</div>

<!-- Main Content -->
<div class="main-content">
    <header class="admin-header">
        <div class="header-left">
            <button class="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <h1>Movies</h1> <!-- Title updated to Movies -->
        </div>
        <div class="header-right">
            <div class="notifications">
                <i class="fas fa-bell"></i>
                <span class="badge">3</span>
            </div>
            <div class="admin-profile">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['admin_full_name']); ?>" alt="Admin">
            </div>
        </div>
    </header>

        <div class="content-wrapper">
            <div class="card">
                <div class="card-header">
                    <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                    <a href="movies.php" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Movies
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="../uploads/thumbnails/<?php echo htmlspecialchars($movie['thumbnail']); ?>" 
                                 class="img-fluid mb-3" 
                                 alt="<?php echo htmlspecialchars($movie['title']); ?>">
                            
                            <div class="mb-3">
                                <strong>Video File:</strong>
                                <div><?php echo htmlspecialchars($movie['video_file']); ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Status:</strong>
                                <span class="badge bg-<?php echo $movie['featured'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $movie['featured'] ? 'Featured' : 'Regular'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="mb-3">
                                <strong>Description:</strong>
                                <p><?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Release Year:</strong>
                                    <div><?php echo htmlspecialchars($movie['release_year']); ?></div>
                                </div>
                                <div class="col-md-6">
                                    <strong>Duration:</strong>
                                    <div><?php echo htmlspecialchars($movie['duration']); ?> minutes</div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Genre:</strong>
                                    <div><?php echo htmlspecialchars($movie['genre']); ?></div>
                                </div>
                                <div class="col-md-6">
                                    <strong>Director:</strong>
                                    <div><?php echo htmlspecialchars($movie['director']); ?></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Cast:</strong>
                                <div><?php echo nl2br(htmlspecialchars($movie['cast'])); ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Uploaded At:</strong>
                                <div><?php echo date('M d, Y H:i', strtotime($movie['uploaded_at'])); ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Views:</strong>
                                <div><?php echo htmlspecialchars($movie['views']); ?></div>
                            </div>
                            
                            <div class="mt-4">
                                <a href="movie_edit.php?id=<?php echo $movie['id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit Movie
                                </a>
                                <a href="movie_delete.php?id=<?php echo $movie['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this movie?')">
                                    <i class="fas fa-trash"></i> Delete Movie
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Admin JS -->
    <script src="js/admin.js"></script>
</body>
</html>