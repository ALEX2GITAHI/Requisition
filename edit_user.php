<?php
session_start();
require 'db.php';

// Ensure the user is logged in and has admin privileges
if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$user_id = $_GET['id']; // Get the user ID from the URL

// Fetch the user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch all groups
$group_sql = "SELECT id, group_name FROM groups";
$groups = $conn->query($group_sql);

// Update user on form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $role = $_POST['role'];
    $group_id = $_POST['group_id'];
    $password = $_POST['password'];

    // Prepare the update query without password
    $update_sql = "UPDATE users SET username = ?, role = ?, group_id = ? WHERE id = ?";

    // Check if the password field is filled, and if so, update the password as well
    if (!empty($password)) {
        // Update with password included (no hashing)
        $update_sql = "UPDATE users SET username = ?, role = ?, group_id = ?, password = ? WHERE id = ?";
    }

    // Bind the correct number of parameters based on whether the password was provided
    if (!empty($password)) {
        // Password is provided, so bind 5 parameters
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('ssisi', $username, $role, $group_id, $password, $user_id); // 5 params
    } else {
        // No password, bind 4 parameters
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('ssii', $username, $role, $group_id, $user_id); // 4 params
    }

    if ($stmt->execute()) {
        header("Location: manage_users.php?success=User updated");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Edit User</h1>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= $user['username'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="treasurer" <?= $user['role'] == 'treasurer' ? 'selected' : '' ?>>Treasurer</option>
                    <option value="secretary" <?= $user['role'] == 'secretary' ? 'selected' : '' ?>>Secretary</option>
                    <option value="chairperson" <?= $user['role'] == 'chairperson' ? 'selected' : '' ?>>Chairperson</option>
                    <option value="patron" <?= $user['role'] == 'patron' ? 'selected' : '' ?>>Patron</option>
                    <option value="lcc_treasurer" <?= $user['role'] == 'lcc_treasurer' ? 'selected' : '' ?>>LCC Treasurer</option>
                    <option value="lcc_secretary" <?= $user['role'] == 'lcc_secretary' ? 'selected' : '' ?>>LCC Secretary</option>
                    <option value="lcc_chair" <?= $user['role'] == 'lcc_chair' ? 'selected' : '' ?>>LCC Chair</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="group_id" class="form-label">Group</label>
                <select class="form-select" id="group_id" name="group_id" required>
                    <option value="">None</option>
                    <?php while ($group = $groups->fetch_assoc()) { ?>
                        <option value="<?= $group['id'] ?>" <?= $user['group_id'] == $group['id'] ? 'selected' : '' ?>><?= $group['group_name'] ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">New Password (leave blank to keep current password)</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <button type="submit" class="btn btn-primary">Update User</button>
        </form>
    </div>
</body>
</html>
