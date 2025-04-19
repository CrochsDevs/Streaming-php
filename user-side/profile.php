<?php
session_start();
require_once '../include/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login-user.php');
    exit();
}

// Get user data from database
$userId = $_SESSION['user_id'] ?? null;

// Fetch user data from database
$userQuery = "SELECT username, email, full_name, avatar, created_at FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($userResult)) {
    $username = $user['username'];
    $fullName = $user['full_name'];
    $email = $user['email'];
    $avatar = !empty($user['avatar']) ? '../' . $user['avatar'] : '../images/default-profile.png';
    $joinDate = $user['created_at'];
} else {
    // User not found in database
    session_destroy();
    header('Location: login-user.php');
    exit();
}

// Initialize stats with default values
$stats = [
    'movies_watched' => 0,
    'tv_shows' => 0,
    'hours_watched' => 0,
    'in_list' => 0
];

// Check if watch_history table exists before querying
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'watch_history'");
if (mysqli_num_rows($tableCheck) > 0) {
    // Get user stats from database
    $statsQuery = "SELECT 
        (SELECT COUNT(*) FROM watch_history WHERE user_id = $userId) AS movies_watched,
        (SELECT COUNT(DISTINCT content_id) FROM watch_history WHERE user_id = $userId AND content_type = 'tv') AS tv_shows,
        (SELECT IFNULL(SUM(duration), 0) FROM watch_history WHERE user_id = $userId) AS hours_watched,
        (SELECT COUNT(*) FROM user_lists WHERE user_id = $userId) AS in_list";
    $statsResult = mysqli_query($conn, $statsQuery);
    if ($statsResult) {
        $stats = mysqli_fetch_assoc($statsResult);
    }
}

// Get recently watched items
$recentItems = [];
$tableCheck1 = mysqli_query($conn, "SHOW TABLES LIKE 'watch_history'");
$tableCheck2 = mysqli_query($conn, "SHOW TABLES LIKE 'contents'");
if (mysqli_num_rows($tableCheck1) > 0 && mysqli_num_rows($tableCheck2) > 0) {
    $recentQuery = "SELECT c.id, c.title, c.poster_path, c.type 
                   FROM watch_history wh
                   JOIN contents c ON wh.content_id = c.id
                   WHERE wh.user_id = $userId
                   ORDER BY wh.watched_at DESC
                   LIMIT 4";
    $recentResult = mysqli_query($conn, $recentQuery);
    if ($recentResult) {
        $recentItems = mysqli_fetch_all($recentResult, MYSQLI_ASSOC);
    }
}

// Get user list items
$listItems = [];
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'user_lists'");
if (mysqli_num_rows($tableCheck) > 0) {
    $listQuery = "SELECT c.id, c.title, c.poster_path, c.type 
                FROM user_lists ul
                JOIN contents c ON ul.content_id = c.id
                WHERE ul.user_id = $userId
                ORDER BY ul.added_at DESC
                LIMIT 4";
    $listResult = mysqli_query($conn, $listQuery);
    if ($listResult) {
        $listItems = mysqli_fetch_all($listResult, MYSQLI_ASSOC);
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $newFullName = mysqli_real_escape_string($conn, $_POST['full_name']);
    $newUsername = mysqli_real_escape_string($conn, $_POST['username']);
    $newEmail = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Check if username or email already exists
    $checkQuery = "SELECT id FROM users WHERE (username = '$newUsername' OR email = '$newEmail') AND id != $userId";
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if (mysqli_num_rows($checkResult) > 0) {
        $error = "Username or email already exists";
    } else {
        $updateQuery = "UPDATE users SET full_name = '$newFullName', username = '$newUsername', email = '$newEmail' WHERE id = $userId";
        if (mysqli_query($conn, $updateQuery)) {
            // Update session variables
            $_SESSION['user_full_name'] = $newFullName;
            $_SESSION['user_username'] = $newUsername;
            $_SESSION['user_email'] = $newEmail;
            
            $success = "Profile updated successfully!";
            // Refresh page to show updated data
            header("Refresh:0");
        } else {
            $error = "Error updating profile: " . mysqli_error($conn);
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Verify current password
    $userQuery = "SELECT password FROM users WHERE id = $userId";
    $userResult = mysqli_query($conn, $userQuery);
    $user = mysqli_fetch_assoc($userResult);
    
    if (password_verify($currentPassword, $user['password'])) {
        if ($newPassword === $confirmPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE users SET password = '$hashedPassword' WHERE id = $userId";
            if (mysqli_query($conn, $updateQuery)) {
                $success = "Password changed successfully!";
            } else {
                $error = "Error changing password: " . mysqli_error($conn);
            }
        } else {
            $error = "New passwords don't match";
        }
    } else {
        $error = "Current password is incorrect";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Streamly</title>
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
            --dark-bg: #1a1a1a;
            --darker-bg: #121212;
            --card-bg: #222;
            --text-color: #ffffff;
            --text-muted: #adb5bd;
        }
        
        body {
            background-color: var(--darker-bg);
            color: var(--text-color);
            padding-top: 56px;
        }
        
        .profile-header {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../images/profile-bg.jpg');
            background-size: cover;
            background-position: center;
            padding: 5rem 0;
            color: white;
            margin-bottom: 3rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .profile-card {
            background-color: var(--dark-bg);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .stats-card {
            background-color: var(--card-bg);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .edit-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        
        .content-card {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            transition: transform 0.3s;
            aspect-ratio: 2/3;
        }
        
        .content-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .content-card:hover {
            transform: scale(1.05);
        }
        
        .content-card:hover img {
            transform: scale(1.1);
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
            transition: opacity 0.3s;
        }
        
        .content-card:hover .card-overlay {
            opacity: 1;
        }
        
        .badge-type {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.7rem;
            z-index: 2;
        }
        
        .navbar {
            background-color: rgba(0, 0, 0, 0.8) !important;
            backdrop-filter: blur(10px);
        }
        
        .modal-content {
            background-color: var(--dark-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .form-control, .form-control:focus {
            background-color: var(--card-bg);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--text-color);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: white;
        }
        
        .text-muted {
            color: var(--text-muted) !important;
        }
        
        .empty-state {
            padding: 2rem;
            text-align: center;
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <!-- Header/Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="../images/logo.png" alt="Streamly Logo" class="logo-img me-2">
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
                        <a class="nav-link" href="tv-shows.php">TV Shows</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="profile.php" class="btn btn-outline-light me-2">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Profile Header -->
    <section class="profile-header text-center">
        <div class="container">
            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Profile Picture" class="profile-pic mb-3">
            <h1><?php echo htmlspecialchars($fullName); ?></h1>
            <p class="text-muted">@<?php echo htmlspecialchars($username); ?></p>
            <div class="d-flex justify-content-center gap-2 mt-3">
                <a href="watch-history.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-history"></i> Watch History
                </a>
                <a href="mylist.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-list"></i> My List
                </a>
            </div>
        </div>
    </section>

    <!-- Profile Content -->
    <div class="container mb-5">
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-4">
                <!-- Profile Info Card -->
                <div class="profile-card position-relative">
                    <button class="btn btn-sm btn-outline-primary edit-btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <h4 class="mb-4">Profile Information</h4>
                    <div class="mb-3">
                        <h6>Full Name</h6>
                        <p class="text-muted"><?php echo htmlspecialchars($fullName); ?></p>
                    </div>
                    <div class="mb-3">
                        <h6>Username</h6>
                        <p class="text-muted">@<?php echo htmlspecialchars($username); ?></p>
                    </div>
                    <div class="mb-3">
                        <h6>Email</h6>
                        <p class="text-muted"><?php echo htmlspecialchars($email); ?></p>
                    </div>
                    <div class="mb-3">
                        <h6>Member Since</h6>
                        <p class="text-muted"><?php echo date('F Y', strtotime($joinDate)); ?></p>
                    </div>
                    <button class="btn btn-outline-warning w-100 mt-2" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="fas fa-lock"></i> Change Password
                    </button>
                </div>

                <!-- Stats Card -->
                <div class="profile-card">
                    <h4 class="mb-4">My Stats</h4>
                    <div class="row">
                        <div class="col-6">
                            <div class="stats-card">
                                <div class="stats-number"><?php echo $stats['movies_watched']; ?></div>
                                <div class="text-muted">Movies Watched</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stats-card">
                                <div class="stats-number"><?php echo $stats['tv_shows']; ?></div>
                                <div class="text-muted">TV Shows</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stats-card">
                                <div class="stats-number"><?php echo round($stats['hours_watched'] / 60, 1); ?></div>
                                <div class="text-muted">Hours Watched</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stats-card">
                                <div class="stats-number"><?php echo $stats['in_list']; ?></div>
                                <div class="text-muted">In My List</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-8">
                <!-- Recently Watched -->
                <div class="profile-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4>Recently Watched</h4>
                        <a href="watch-history.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <?php if (!empty($recentItems)): ?>
                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
                            <?php foreach ($recentItems as $item): ?>
                                <div class="col">
                                    <div class="content-card">
                                        <img src="<?php echo htmlspecialchars($item['poster_path']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                        <span class="badge bg-<?php echo $item['type'] == 'movie' ? 'primary' : 'success'; ?> badge-type">
                                            <?php echo strtoupper($item['type']); ?>
                                        </span>
                                        <div class="card-overlay">
                                            <a href="watch.php?id=<?php echo $item['id']; ?>&type=<?php echo $item['type']; ?>" class="btn btn-primary btn-sm me-2" title="Play">
                                                <i class="fas fa-play"></i>
                                            </a>
                                            <button class="btn btn-outline-light btn-sm btn-add-to-list" data-content-id="<?php echo $item['id']; ?>" title="Add to list">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <h6 class="mt-2 text-truncate"><?php echo htmlspecialchars($item['title']); ?></h6>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-film"></i>
                            <h5>No recently watched items</h5>
                            <p class="text-muted">Start watching movies and TV shows to see them here</p>
                            <a href="movies.php" class="btn btn-primary mt-2">Browse Movies</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- My List Preview -->
                <div class="profile-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4>My List</h4>
                        <a href="mylist.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <?php if (!empty($listItems)): ?>
                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
                            <?php foreach ($listItems as $item): ?>
                                <div class="col">
                                    <div class="content-card">
                                        <img src="<?php echo htmlspecialchars($item['poster_path']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                        <span class="badge bg-<?php echo $item['type'] == 'movie' ? 'primary' : 'success'; ?> badge-type">
                                            <?php echo strtoupper($item['type']); ?>
                                        </span>
                                        <div class="card-overlay">
                                            <a href="watch.php?id=<?php echo $item['id']; ?>&type=<?php echo $item['type']; ?>" class="btn btn-primary btn-sm me-2" title="Play">
                                                <i class="fas fa-play"></i>
                                            </a>
                                            <button class="btn btn-outline-light btn-sm btn-remove-from-list" data-content-id="<?php echo $item['id']; ?>" title="Remove from list">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <h6 class="mt-2 text-truncate"><?php echo htmlspecialchars($item['title']); ?></h6>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-list"></i>
                            <h5>Your list is empty</h5>
                            <p class="text-muted">Add movies and TV shows to your watchlist</p>
                            <a href="movies.php" class="btn btn-primary mt-2">Browse Content</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($fullName); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Streamly</h5>
                    <p class="text-muted">Your favorite streaming platform for movies and TV shows.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-muted">Home</a></li>
                        <li><a href="movies.php" class="text-muted">Movies</a></li>
                        <li><a href="tv-shows.php" class="text-muted">TV Shows</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Account</h5>
                    <ul class="list-unstyled">
                        <li><a href="profile.php" class="text-muted">Profile</a></li>
                        <li><a href="logout.php" class="text-muted">Logout</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 bg-secondary">
            <div class="text-center text-muted">
                <small>&copy; <?php echo date('Y'); ?> Streamly. All rights reserved.</small>
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
        // Add to list functionality
        $(document).on('click', '.btn-add-to-list', function(e) {
            e.preventDefault();
            const contentId = $(this).data('content-id');
            const button = $(this);
            
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: 'add-to-list.php',
                method: 'POST',
                data: { content_id: contentId },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            Swal.fire({
                                title: 'Added!',
                                text: result.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: result.message,
                                icon: 'error'
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Invalid response from server',
                            icon: 'error'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to add to list',
                        icon: 'error'
                    });
                },
                complete: function() {
                    button.prop('disabled', false).html('<i class="fas fa-plus"></i>');
                }
            });
        });

        // Remove from list functionality
        $(document).on('click', '.btn-remove-from-list', function(e) {
            e.preventDefault();
            const contentId = $(this).data('content-id');
            const button = $(this);
            
            Swal.fire({
                title: 'Remove from list?',
                text: "Are you sure you want to remove this item from your list?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                    
                    $.ajax({
                        url: 'remove-from-list.php',
                        method: 'POST',
                        data: { content_id: contentId },
                        success: function(response) {
                            const result = JSON.parse(response);
                            if (result.success) {
                                Swal.fire({
                                    title: 'Removed!',
                                    text: result.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: result.message,
                                    icon: 'error'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to remove from list',
                                icon: 'error'
                            });
                        },
                        complete: function() {
                            button.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                        }
                    });
                }
            });
        });
        
        // Validate password change form
        $('form').on('submit', function() {
            if ($(this).find('[name="change_password"]').length > 0) {
                const newPass = $('#new_password').val();
                const confirmPass = $('#confirm_password').val();
                
                if (newPass !== confirmPass) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'New passwords do not match',
                        icon: 'error'
                    });
                    return false;
                }
                
                if (newPass.length < 8) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Password must be at least 8 characters long',
                        icon: 'error'
                    });
                    return false;
                }
            }
            return true;
        });
    });
    </script>
</body>
</html>