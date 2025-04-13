<?php
session_start();
require_once '../include/db.php';
require_once 'theme.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Get all movies
$movies = $conn->query("SELECT * FROM movies ORDER BY uploaded_at DESC")->fetch_all(MYSQLI_ASSOC);

// Set theme
$currentTheme = getCurrentTheme();
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $currentTheme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streamly | Manage Movies</title>
    <link rel="icon" type="image/png" href="../images/fav-logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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
                    <h3>Movies List</h3>
                    <a href="movie_add.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add New Movie
                    </a>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['movie_message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['movie_message_type']; ?>">
                            <?php echo $_SESSION['movie_message']; ?>
                        </div>
                        <?php unset($_SESSION['movie_message']); ?>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table id="moviesTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Thumbnail</th>
                                    <th>Title</th>
                                    <th>Genre</th>
                                    <th>Year</th>
                                    <th>Views</th>
                                    <th>Uploaded</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movies as $movie): ?>
                                <tr>
                                    <td>
                                        <img src="../uploads/thumbnails/<?php echo htmlspecialchars($movie['thumbnail']); ?>" 
                                             alt="<?php echo htmlspecialchars($movie['title']); ?>" 
                                             style="width: 80px; height: auto;">
                                    </td>
                                    <td><?php echo htmlspecialchars($movie['title']); ?></td>
                                    <td><?php echo htmlspecialchars($movie['genre']); ?></td>
                                    <td><?php echo htmlspecialchars($movie['release_year']); ?></td>
                                    <td><?php echo htmlspecialchars($movie['views']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($movie['uploaded_at'])); ?></td>
                                    <td>
                                        <a href="movie_view.php?id=<?php echo $movie['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="movie_edit.php?id=<?php echo $movie['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="movie_delete.php?id=<?php echo $movie['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this movie?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Admin JS -->
    <script src="js/admin.js"></script>
    <script>
        $(document).ready(function() {
            $('#moviesTable').DataTable();
        });
    </script>
</body>
</html>