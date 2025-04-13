<?php
session_start();
require_once '../include/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Initialize default theme if not set
if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'light';
}

// Handle profile updates
$profile_error = '';
$profile_success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    
    // Validate inputs
    if (empty($new_username) || empty($new_email)) {
        $profile_error = 'Username and email cannot be empty';
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $profile_error = 'Invalid email format';
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM admins WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->bind_param("ssi", $new_username, $new_email, $_SESSION['admin_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $profile_error = 'Username or email already exists';
        } else {
            // Update profile
            $update_stmt = $conn->prepare("UPDATE admins SET username = ?, email = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $new_username, $new_email, $_SESSION['admin_id']);
            
            if ($update_stmt->execute()) {
                // Update session variables
                $_SESSION['admin_username'] = $new_username;
                $_SESSION['admin_email'] = $new_email;
                $profile_success = 'Profile updated successfully';
            } else {
                $profile_error = 'Failed to update profile';
            }
        }
    }
}

// Handle password change
$password_error = '';
$password_success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    
    if (!password_verify($current_password, $admin['password'])) {
        $password_error = 'Current password is incorrect';
    } elseif ($new_password !== $confirm_password) {
        $password_error = 'New passwords do not match';
    } elseif (strlen($new_password) < 8) {
        $password_error = 'Password must be at least 8 characters';
    } else {
        // Update password
        $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $update_stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_hashed_password, $_SESSION['admin_id']);
        
        if ($update_stmt->execute()) {
            $password_success = 'Password changed successfully';
        } else {
            $password_error = 'Failed to change password';
        }
    }
}

// Handle display settings
$display_error = '';
$display_success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_display'])) {
    $theme = $_POST['theme'];
    $results_per_page = (int)$_POST['results_per_page'];
    
    // Validate inputs
    if (!in_array($theme, ['light', 'dark', 'system'])) {
        $display_error = 'Invalid theme selection';
    } elseif (!in_array($results_per_page, [10, 25, 50, 100])) {
        $display_error = 'Invalid results per page value';
    } else {
        // Save to session (in a real app, you would save to database)
        $_SESSION['theme'] = $theme;
        $_SESSION['results_per_page'] = $results_per_page;
        
        // Also save to database for persistence
        $update_stmt = $conn->prepare("UPDATE admins SET theme = ?, results_per_page = ? WHERE id = ?");
        $update_stmt->bind_param("sii", $theme, $results_per_page, $_SESSION['admin_id']);
        
        if ($update_stmt->execute()) {
            $display_success = 'Display settings saved successfully';
        } else {
            $display_error = 'Failed to save display settings';
        }
    }
}

// Handle notification settings
$notification_error = '';
$notification_success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_notifications'])) {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $push_notifications = isset($_POST['push_notifications']) ? 1 : 0;
    $notification_frequency = $_POST['notification_frequency'];
    
    // Validate inputs
    if (!in_array($notification_frequency, ['instant', 'daily', 'weekly'])) {
        $notification_error = 'Invalid notification frequency';
    } else {
        // Save to session (in a real app, you would save to database)
        $_SESSION['email_notifications'] = $email_notifications;
        $_SESSION['push_notifications'] = $push_notifications;
        $_SESSION['notification_frequency'] = $notification_frequency;
        
        // Also save to database for persistence
        $update_stmt = $conn->prepare("UPDATE admins SET email_notifications = ?, push_notifications = ?, notification_frequency = ? WHERE id = ?");
        $update_stmt->bind_param("iisi", $email_notifications, $push_notifications, $notification_frequency, $_SESSION['admin_id']);
        
        if ($update_stmt->execute()) {
            $notification_success = 'Notification settings saved successfully';
        } else {
            $notification_error = 'Failed to save notification settings';
        }
    }
}

// Get current admin data including settings
$stmt = $conn->prepare("SELECT username, email, theme, results_per_page, email_notifications, push_notifications, notification_frequency FROM admins WHERE id = ?");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();
$admin_data = $result->fetch_assoc();

// Set session variables from database if they exist
if ($admin_data) {
    $_SESSION['theme'] = $admin_data['theme'] ?? 'light';
    $_SESSION['results_per_page'] = $admin_data['results_per_page'] ?? 10;
    $_SESSION['email_notifications'] = $admin_data['email_notifications'] ?? 1;
    $_SESSION['push_notifications'] = $admin_data['push_notifications'] ?? 1;
    $_SESSION['notification_frequency'] = $admin_data['notification_frequency'] ?? 'instant';
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Streamly | Admin Settings</title>
    <link rel="icon" type="image/png" href="../images/fav-logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="css/admin.css">
    <style>
 
    [data-theme="dark"] {
        --bg-color: #1a1a1a;
        --text-color: #e0e0e0;  
        --card-bg: #2d2d2d;
        --header-bg: #212529;
        --sidebar-bg: #343a40;
        --sidebar-text: #e0e0e0; 
        --border-color: #495057;
        --link-color: #7fa3ff;  
        --alert-text: #ffffff;  

    [data-theme="dark"] body {
        background-color: var(--bg-color);
        color: var(--text-color);
    }

    [data-theme="dark"] .card {
        background-color: var(--card-bg);
        border-color: var(--border-color);
        color: var(--text-color);
    }

    [data-theme="dark"] .admin-header {
        background-color: var(--header-bg);
        border-bottom-color: var(--border-color);
        color: var(--text-color);
    }

    [data-theme="dark"] .sidebar {
        background-color: var(--sidebar-bg);
        color: var(--sidebar-text);
    }

    [data-theme="dark"] .sidebar a {
        color: var(--sidebar-text);
    }

    [data-theme="dark"] .sidebar a:hover {
        color: white;
    }

    [data-theme="dark"] .nav-tabs .nav-link.active {
        background-color: var(--card-bg);
        border-color: var(--border-color) var(--border-color) var(--card-bg);
        color: var(--text-color);
    }

    [data-theme="dark"] .form-control,
    [data-theme="dark"] .form-select {
        background-color: #3a3a3a;
        border-color: #4a4a4a;
        color: #f8f9fa;
    }

    [data-theme="dark"] .alert {
        color: var(--alert-text);
    }

    [data-theme="dark"] a {
        color: var(--link-color);
    }

    [data-theme="dark"] .text-muted {
        color: #b3b3b3 !important;
    }

    [data-theme="dark"] .table {
        color: var(--text-color);
    }

    [data-theme="dark"] .table-hover tbody tr:hover {
        color: var(--text-color);
        background-color: rgba(255, 255, 255, 0.075);
    }
    </style>
</head>
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
                <li>
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
                <li class="active">
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
                <h1>Settings</h1>
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
            <!-- Settings Tabs -->
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">Profile</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">Password</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="display-tab" data-bs-toggle="tab" data-bs-target="#display" type="button" role="tab">Display</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">Notifications</button>
                </li>
            </ul>

            <div class="tab-content" id="settingsTabsContent">
                <!-- Profile Settings Tab -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Profile Settings</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($profile_error): ?>
                                <div class="alert alert-danger"><?php echo $profile_error; ?></div>
                            <?php endif; ?>
                            <?php if ($profile_success): ?>
                                <div class="alert alert-success"><?php echo $profile_success; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="form-group mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($admin_data['username']); ?>" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin_data['email']); ?>" required>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Password Settings Tab -->
                <div class="tab-pane fade" id="password" role="tabpanel">
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Change Password</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($password_error): ?>
                                <div class="alert alert-danger"><?php echo $password_error; ?></div>
                            <?php endif; ?>
                            <?php if ($password_success): ?>
                                <div class="alert alert-success"><?php echo $password_success; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="form-group mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-control" required>
                                    <small class="text-muted">Minimum 8 characters</small>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Display Settings Tab -->
                <div class="tab-pane fade" id="display" role="tabpanel">
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Display Settings</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($display_error): ?>
                                <div class="alert alert-danger"><?php echo $display_error; ?></div>
                            <?php endif; ?>
                            <?php if ($display_success): ?>
                                <div class="alert alert-success"><?php echo $display_success; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="form-group mb-3">
                                    <label class="form-label">Theme</label>
                                    <select name="theme" class="form-select">
                                        <option value="light" <?php echo ($_SESSION['theme'] == 'light') ? 'selected' : ''; ?>>Light</option>
                                        <option value="dark" <?php echo ($_SESSION['theme'] == 'dark') ? 'selected' : ''; ?>>Dark</option>
                                        <option value="system" <?php echo ($_SESSION['theme'] == 'system') ? 'selected' : ''; ?>>System Default</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">Results Per Page</label>
                                    <select name="results_per_page" class="form-select">
                                        <option value="10" <?php echo ($_SESSION['results_per_page'] == 10) ? 'selected' : ''; ?>>10</option>
                                        <option value="25" <?php echo ($_SESSION['results_per_page'] == 25) ? 'selected' : ''; ?>>25</option>
                                        <option value="50" <?php echo ($_SESSION['results_per_page'] == 50) ? 'selected' : ''; ?>>50</option>
                                        <option value="100" <?php echo ($_SESSION['results_per_page'] == 100) ? 'selected' : ''; ?>>100</option>
                                    </select>
                                </div>
                                
                                <button type="submit" name="save_display" class="btn btn-primary">Save Settings</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings Tab -->
                <div class="tab-pane fade" id="notifications" role="tabpanel">
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Notification Settings</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($notification_error): ?>
                                <div class="alert alert-danger"><?php echo $notification_error; ?></div>
                            <?php endif; ?>
                            <?php if ($notification_success): ?>
                                <div class="alert alert-success"><?php echo $notification_success; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="email_notifications" id="email_notifications" 
                                        <?php echo ($_SESSION['email_notifications'] == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="email_notifications">
                                        Email Notifications
                                    </label>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="push_notifications" id="push_notifications" 
                                        <?php echo ($_SESSION['push_notifications'] == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="push_notifications">
                                        Push Notifications
                                    </label>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">Notification Frequency</label>
                                    <select name="notification_frequency" class="form-select">
                                        <option value="instant" <?php echo ($_SESSION['notification_frequency'] == 'instant') ? 'selected' : ''; ?>>Instant</option>
                                        <option value="daily" <?php echo ($_SESSION['notification_frequency'] == 'daily') ? 'selected' : ''; ?>>Daily Digest</option>
                                        <option value="weekly" <?php echo ($_SESSION['notification_frequency'] == 'weekly') ? 'selected' : ''; ?>>Weekly Digest</option>
                                    </select>
                                </div>
                                
                                <button type="submit" name="save_notifications" class="btn btn-primary">Save Settings</button>
                            </form>
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
    <script>
        // Apply theme immediately on page load and detect system preference
    document.addEventListener('DOMContentLoaded', function() {
        // Check system color scheme preference
        const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.cookie = `prefersDark=${systemDark}; path=/`;
        
        // Apply theme
        applyTheme(document.documentElement.getAttribute('data-theme'));
        
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            document.cookie = `prefersDark=${e.matches}; path=/`;
            if (document.documentElement.getAttribute('data-theme') === 'system') {
                applyTheme('system');
            }
        });
    });

    // Function to apply theme
    function applyTheme(theme) {
        if (theme === 'system') {
            const prefersDark = document.cookie.split('; ').find(row => row.startsWith('prefersDark='));
            const isDark = prefersDark ? prefersDark.split('=')[1] === 'true' : false;
            document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
        } else {
            document.documentElement.setAttribute('data-theme', theme);
        }
        
        // Store the theme preference in a cookie for other pages
        document.cookie = `currentTheme=${theme}; path=/`;
    }
    </script>
</body>
</html>