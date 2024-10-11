<?php
session_start();
require 'db.php'; // Include the database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Fetch the user record by username
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Check if user exists and verify the hashed password
        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, proceed with session setup
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];

            // Fetch group name by group_id
            $group_id = $user['group_id'];
            $group_query = "SELECT group_name FROM groups WHERE id = ?";
            $group_stmt = $conn->prepare($group_query);
            $group_stmt->bind_param('i', $group_id);
            $group_stmt->execute();
            $group_result = $group_stmt->get_result();

            if ($group_result->num_rows === 1) {
                $group = $group_result->fetch_assoc();
                $_SESSION['group_name'] = $group['group_name'];
            } else {
                $_SESSION['group_name'] = 'Default Group';
            }

            // Redirect based on user role
            switch ($user['role']) {
                case 'lcc_treasurer': header('Location: lcc_treasurer_dashboard.php'); exit;
                case 'lcc_secretary': header('Location: lcc_secretary_dashboard.php'); exit;
                case 'lcc_chair': header('Location: lcc_chair_dashboard.php'); exit;
                case 'patron': header('Location: patron_dashboard.php'); exit;
                case 'treasurer': header('Location: treasurer_dashboard.php'); exit;
                case 'secretary': header('Location: secretary_dashboard.php'); exit;
                case 'chairperson': header('Location: chair_dashboard.php'); exit;
                case 'admin': header('Location: admin_dashboard.php'); exit;
                default: header('Location: index.php?error=Invalid role'); exit;
            }
        } else {
            // Invalid username or password
            header('Location: index.php?error=Invalid username or password');
            exit;
        }
    } else {
        die("Error preparing statement: " . $conn->error);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/img/log2.png" rel="shortcut icon">
    <style>
        body {
            background-image: url('assets/img/Screenshot 2024-04-17 121029.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Arial', sans-serif;
            color: #fff;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        .logo img {
            max-width: 100px;
            margin-bottom: 0 rem;
        }
        .form-control {
            border-radius: 50px;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid #ddd;
        }
        .btn-primary {
            border-radius: 50px;
            padding: 0.75rem;
            width: 100%;
            background-color: #007bff;
            border: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .alert {
            border-radius: 50px;
            padding: 0.75rem;
        }
        .card-header h2 {
            color: #90e0ef;
            font-size: 1.75rem;
        }
        @media (max-width: 768px) {
            .login-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo">
        <img src="assets/img/log2.png" alt="Logo">
    </div>
    <h2 class="card-header">PCEA MUKINYI</h2>
    <form method="POST" action="index.php">
        <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger text-center">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php } ?>
        <div class="mb-3">
            <input type="text" class="form-control" name="username" placeholder="Username" required>
        </div>
        <div class="mb-3">
            <input type="password" class="form-control" name="password" placeholder="Password" required>
        </div>
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
    </form>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
