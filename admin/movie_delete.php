<?php
session_start();
require_once '../include/db.php';

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

// Get movie data to delete files
$stmt = $conn->prepare("SELECT thumbnail, video_file FROM movies WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();

if ($movie) {
    // Delete files
    if (file_exists('../uploads/thumbnails/' . $movie['thumbnail'])) {
        unlink('../uploads/thumbnails/' . $movie['thumbnail']);
    }
    if (file_exists('../uploads/videos/' . $movie['video_file'])) {
        unlink('../uploads/videos/' . $movie['video_file']);
    }

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

$_SESSION['movie_message'] = 'Movie deleted successfully';
$_SESSION['movie_message_type'] = 'success';
header('Location: movies.php');
exit();
?>