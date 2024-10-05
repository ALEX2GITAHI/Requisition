<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['group_id'])) {
    $group_id = $_POST['group_id'];
    $group_name = $_POST['group_name'];
    $total_account_balance = $_POST['total_account_balance'];

    // Update query
    $stmt = $conn->prepare("UPDATE groups SET group_name = ?, total_account_balance = ? WHERE id = ?");
    $stmt->bind_param("ssi", $group_name, $total_account_balance, $group_id);
    if ($stmt->execute()) {
        // Redirect or provide success message
        header("Location: manage_groups.php?success=1");
        exit();
    } else {
        // Handle error
        echo "Error updating group: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
?>
