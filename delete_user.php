<?php
session_start();
require 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Unauthorized access");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage_user.php?error=No user ID provided");
    exit();
}

$user_id = (int) $_GET['id'];

// Prevent admin from deleting themselves
if ($user_id === (int) $_SESSION['user_id']) {
    header("Location: manage_user.php?error=You cannot delete your own account");
    exit();
}

// Delete user
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    header("Location: manage_user.php?message=User deleted successfully");
    exit();
} else {
    header("Location: manage_user.php?error=Error deleting user");
    exit();
}
?>