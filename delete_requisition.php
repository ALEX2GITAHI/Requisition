<?php
session_start();
include('db.php');

// Check if the user is logged in and the form was submitted
if (isset($_POST['requisition_id']) && isset($_SESSION['user_id'])) {
    $requisition_id = $_POST['requisition_id'];

    // Delete the requisition from the table
    $query = "DELETE FROM requisitions WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requisition_id);

    if ($stmt->execute()) {
        header('Location: main_dashboard.php?msg=Requisition deleted successfully');
    } else {
        echo "Error deleting requisition: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    header('Location: index.php');
}
?>
