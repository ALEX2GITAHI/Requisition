<?php
include('db.php'); // Include your database connection

// Check if an ID is passed
if (isset($_GET['id'])) {
    $group_id = intval($_GET['id']); // Get the group ID and ensure it's an integer

    // Fetch the group data
    $query = "SELECT id, group_name, total_account_balance, group_logo FROM groups WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $group_id); // Bind the group ID to the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a group was found
    if ($result->num_rows > 0) {
        $group = $result->fetch_assoc(); // Fetch the group data
        echo json_encode($group); // Return the data as JSON
    } else {
        echo json_encode(['error' => 'Group not found']);
    }
} else {
    echo json_encode(['error' => 'No group ID provided']);
}
?>
