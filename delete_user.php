<?php
// delete_user.php

include('header.php');
include('db.php');

// Check if admin is logged in

// Check if the user ID is provided
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Prevent deleting the admin themselves
    if ($user_id == $_SESSION['admin_id']) {
        echo "You cannot delete your own account!";
        exit();
    }

    // Delete the user from the database
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        header('Location: manage_user.php?message=User deleted successfully.');
    } else {
        echo "Error deleting user: " . $stmt->error;
    }
} else {
    echo "No user ID provided.";
}
?>
