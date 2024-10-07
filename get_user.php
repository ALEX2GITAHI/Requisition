<?php
require 'db.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $sql = "SELECT id, username, role, first_name, last_name, phone_number, group_id FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode($user);
    } else {
        echo json_encode([]);
    }
}
?>
