<?php
include('db.php');

if (isset($_POST['group_id'])) {
    $group_id = intval($_POST['group_id']);
    $group_name = $_POST['group_name'];
    $account_balance = $_POST['total_account_balance'];
    
    // Check if a new logo is uploaded
    if (isset($_FILES['group_logo']) && $_FILES['group_logo']['size'] > 0) {
        $logo = file_get_contents($_FILES['group_logo']['tmp_name']);
        // Update with logo
        $query = "UPDATE groups SET group_name = ?, total_account_balance = ?, group_logo = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sdsi", $group_name, $account_balance, $logo, $group_id);
    } else {
        // Update without logo
        $query = "UPDATE groups SET group_name = ?, total_account_balance = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sdi", $group_name, $account_balance, $group_id);
    }

    if ($stmt->execute()) {
        header("Location: manage_groups.php?success=Group updated successfully");
        exit();
    } else {
        header("Location: manage_groups.php?error=Error updating group");
        exit();
    }
}
?>
