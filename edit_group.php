<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_id = $_POST['group_id'];
    $group_name = $_POST['group_name'];
    $total_account_balance = $_POST['total_account_balance'];

    // Validate inputs
    if (empty($group_name) || empty($total_account_balance)) {
        echo "Group name and account balance are required.";
        exit;
    }

    // Update group name and account balance without modifying the logo
    $sql = "UPDATE groups SET group_name = ?, total_account_balance = ? WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdi", $group_name, $total_account_balance, $group_id);

    if ($stmt->execute()) {
        echo "Group updated successfully.";
        header("Location: manage_groups.php");
    } else {
        echo "Error updating group: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
