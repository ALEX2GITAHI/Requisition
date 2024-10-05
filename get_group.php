<?php
include('db.php'); // Include your database connection

if (isset($_GET['id'])) {
    $group_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT id, group_name, total_account_balance FROM groups WHERE id = ?");
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Fetch the group data
        $group = $result->fetch_assoc();
        echo json_encode($group);
    } else {
        echo json_encode(["error" => "Group not found."]);
    }
} else {
    echo json_encode(["error" => "Invalid ID."]);
}
?>
