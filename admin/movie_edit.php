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
$error = '';
$success = '';

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $release_year = (int)$_POST['release_year'];
    $duration = (int)$_POST['duration'];
    $genre = trim($_POST['genre']);
    $director = trim($_POST['director']);
    $cast = trim($_POST['cast']);
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Validate inputs
    if (empty($title) || empty($description) || empty($genre) || empty($director) || empty($cast)) {
        $error = 'Please fill in all required fields';
    } elseif ($release_year < 1900 || $release_year > date('Y') + 5) {
        $error = 'Invalid release year';
    } elseif ($duration < 1 || $duration > 600) {
        $error = 'Invalid duration (1-600 minutes)';
    } else {
        // Handle file uploads
        $thumbnail = $movie['thumbnail'];
        $video_file = $movie['video_file'];

        // Upload thumbnail if changed
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $thumbnail_name = uniqid() . '_' . basename($_FILES['thumbnail']['name']);
            $thumbnail_path = '../uploads/thumbnails/' . $thumbnail_name;
            
            // Check if image file is a actual image
            $check = getimagesize($_FILES['thumbnail']['tmp_name']);
            if ($check === false) {
                $error = 'Thumbnail file is not an image';
            } elseif (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnail_path)) {
                $error = 'Failed to upload thumbnail';
            } else {
                // Delete old thumbnail
                if (file_exists('../uploads/thumbnails/' . $thumbnail)) {
                    unlink('../uploads/thumbnails/' . $thumbnail);
                }
                $thumbnail = $thumbnail_name;
            }
        }

        // Upload video file if changed
        if (empty($error) && isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $video_name = uniqid() . '_' . basename($_FILES['video_file']['name']);
            $video_path = '../uploads/videos/' . $video_name;
            
            // Check if file is a video
            $allowed_types = ['video/mp4', 'video/webm', 'video/ogg'];
            $file_type = $_FILES['video_file']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error = 'Only MP4, WebM, and OGG video files are allowed';
            } elseif (!move_uploaded_file($_FILES['video_file']['tmp_name'], $video_path)) {
                $error = 'Failed to upload video file';
            } else {
                // Delete old video
                if (file_exists('../uploads/videos/' . $video_file)) {
                    unlink('../uploads/videos/' . $video_file);
                }
                $video_file = $video_name;
            }
        }

        // Update database if no errors
        if (empty($error)) {
            $stmt = $conn->prepare("UPDATE movies SET title = ?, description = ?, release_year = ?, duration = ?, genre = ?, director = ?, cast = ?, thumbnail = ?, video_file = ?, featured = ? WHERE id = ?");
            $stmt->bind_param("ssiisssssii", $title, $description, $release_year, $duration, $genre, $director, $cast, $thumbnail, $video_file, $featured, $id);
            
            if ($stmt->execute()) {
                $_SESSION['movie_message'] = 'Movie updated successfully';
                $_SESSION['movie_message_type'] = 'success';
                header('Location: movies.php');
                exit();
            } else {
                $error = 'Failed to update movie';
            }
        }
    }
}

$currentTheme = getCurrentTheme();
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $currentTheme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streamly | Edit Movie</title>
    <link rel="icon" type="image/png" href="../images/fav-logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-dashboard">
    <!-- Sidebar -->
    <?php include 'partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <header class="admin-header">
            <div class="header-left">
                <button class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Edit Movie</h1>
            </div>
            <div class="header-right">
                <?php include 'partials/header-right.php'; ?>
            </div>
        </header>    </head>
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
                    <h3>Edit Movie Details</h3>
                    <a href="movies.php" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Movies
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mb-3">
                                    <label class="form-label">Title*</label>
                                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($movie['title']); ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">Description*</label>
                                    <textarea name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($movie['description']); ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Release Year*</label>
                                            <input type="number" name="release_year" class="form-control" min="1900" max="<?php echo date('Y') + 5; ?>" value="<?php echo htmlspecialchars($movie['release_year']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Duration (minutes)*</label>
                                            <input type="number" name="duration" class="form-control" min="1" max="600" value="<?php echo htmlspecialchars($movie['duration']); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">Genre*</label>
                                    <input type="text" name="genre" class="form-control" value="<?php echo htmlspecialchars($movie['genre']); ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">Director*</label>
                                    <input type="text" name="director" class="form-control" value="<?php echo htmlspecialchars($movie['director']); ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">Cast*</label>
                                    <textarea name="cast" class="form-control" rows="3" required><?php echo htmlspecialchars($movie['cast']); ?></textarea>
                                    <small class="text-muted">Separate names with commas</small>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="featured" id="featured" <?php echo $movie['featured'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="featured">
                                        Featured Movie
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Current Thumbnail</label>
                                    <img src="../uploads/thumbnails/<?php echo htmlspecialchars($movie['thumbnail']); ?>" class="img-fluid mb-2" alt="Current Thumbnail">
                                    <label class="form-label">Change Thumbnail</label>
                                    <input type="file" name="thumbnail" class="form-control" accept="image/*">
                                    <small class="text-muted">Leave empty to keep current</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">Current Video File</label>
                                    <div class="mb-2"><?php echo htmlspecialchars($movie['video_file']); ?></div>
                                    <label class="form-label">Change Video File</label>
                                    <input type="file" name="video_file" class="form-control" accept="video/*">
                                    <small class="text-muted">Leave empty to keep current</small>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Movie</button>
                    </form>
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