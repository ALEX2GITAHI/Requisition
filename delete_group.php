<?php
session_start();
require 'db.php';

// 1️⃣ Check if logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Unauthorized access");
    exit();
}

// 2️⃣ Check if ID exists
if (!isset($_GET['id'])) {
    header("Location: manage_groups.php?error=No group ID provided");
    exit();
}

$group_id = (int) $_GET['id'];

// 3️⃣ Prevent deleting group if it has users
$userCheck = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE group_id = ?");
$userCheck->bind_param("i", $group_id);
$userCheck->execute();
$userResult = $userCheck->get_result()->fetch_assoc();

if ($userResult['total'] > 0) {
    header("Location: manage_groups.php?error=Cannot delete group. It has users assigned.");
    exit();
}

// 4️⃣ Prevent deleting group if it has requisitions
$reqCheck = $conn->prepare("SELECT COUNT(*) as total FROM requisitions WHERE group_id = ?");
$reqCheck->bind_param("i", $group_id);
$reqCheck->execute();
$reqResult = $reqCheck->get_result()->fetch_assoc();

if ($reqResult['total'] > 0) {
    header("Location: manage_groups.php?error=Cannot delete group. It has requisitions.");
    exit();
}

// 5️⃣ Delete group
$stmt = $conn->prepare("DELETE FROM groups WHERE id = ?");
$stmt->bind_param("i", $group_id);

if ($stmt->execute()) {
    header("Location: manage_groups.php?message=Group deleted successfully");
    exit();
} else {
    header("Location: manage_groups.php?error=Error deleting group");
    exit();
}
?>