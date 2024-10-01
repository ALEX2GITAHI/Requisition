<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_name = $_POST['group_name'];
    $total_account_balance = $_POST['total_account_balance'];

    // Handle file upload
    if (isset($_FILES['group_logo']) && $_FILES['group_logo']['error'] === UPLOAD_ERR_OK) {
        $logo = file_get_contents($_FILES['group_logo']['tmp_name']);
    } else {
        $logo = null;
    }

    // Insert group into the database
    $stmt = $conn->prepare("INSERT INTO groups (group_name, total_account_balance, group_logo) VALUES (?, ?, ?)");
    $stmt->bind_param('sds', $group_name, $total_account_balance, $logo);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_groups.php");
}
?>
