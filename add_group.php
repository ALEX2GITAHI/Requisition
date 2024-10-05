<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_name = $_POST['group_name'];
    $total_account_balance = $_POST['total_account_balance'];

    // Handle file upload for group logo
    $group_logo = null;
    if (isset($_FILES['group_logo']) && $_FILES['group_logo']['error'] == UPLOAD_ERR_OK) {
        $group_logo = file_get_contents($_FILES['group_logo']['tmp_name']);
    }

    // Insert query
    $stmt = $conn->prepare("INSERT INTO groups (group_name, total_account_balance, group_logo) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $group_name, $total_account_balance, $group_logo);
    if ($stmt->execute()) {
        // Redirect or provide success message
        header("Location: manage_groups.php?success=1");
        exit();
    } else {
        // Handle error
        echo "Error adding group: " . $conn->error;
    }
}
?>
