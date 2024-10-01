<?php
session_start();
require 'db.php'; // Include the database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the submitted username and password
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Prepare SQL query to fetch user by username
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind the username parameter and execute the query
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // If user is found, check the password
        if ($user && $password === $user['password']) {
            // Set session variables for the logged-in user
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username']; 

            // Fetch the group name
            $group_id = $user['group_id']; // Assuming 'group_id' exists in the users table
            $group_query = "SELECT group_name FROM groups WHERE id = ?";
            $group_stmt = $conn->prepare($group_query);
            $group_stmt->bind_param('i', $group_id);
            $group_stmt->execute();
            $group_result = $group_stmt->get_result();

            if ($group_result->num_rows === 1) {
                $group = $group_result->fetch_assoc();
                $_SESSION['group_name'] = $group['group_name']; // Set group name in session
            } else {
                $_SESSION['group_name'] = 'Default Group'; // Default if no group found
            }

            // Redirect based on role
            switch ($user['role']) {
                case 'lcc_treasurer':
                    header('Location: lcc_treasurer_dashboard.php');
                    exit;
                case 'lcc_secretary':
                    header('Location: lcc_secretary_dashboard.php');
                    exit;
                case 'lcc_chair':
                    header('Location: lcc_chair_dashboard.php');
                    exit;
                case 'patron':
                    header('Location: patron_dashboard.php');
                    exit;
                case 'treasurer':
                    header('Location: treasurer_dashboard.php');
                    exit;
                case 'secretary':
                    header('Location: secretary_dashboard.php');
                    exit;
                case 'chair':
                    header('Location: chair_dashboard.php');
                    exit;
                case 'admin':
                    header('Location: admin_dashboard.php');
                    exit;
                default:
                    header('Location: index.php?error=Invalid role');
                    exit;
            }
        } else {
            // Redirect with an error if the credentials are invalid
            header('Location: login.php?error=Invalid username or password');
            exit;
        }
    } else {
        // If there was an issue with the SQL statement
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
    <style>
        body {
            background-color: #f7f7f7;
            font-family: 'Arial', sans-serif;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('background-image.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            text-align: center;
            font-size: 1.5rem;
            padding: 1.5rem 0;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .form-control {
            border-radius: 30px;
            padding: 0.75rem 1.25rem;
        }
        .btn-primary {
            border-radius: 30px;
            padding: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .login-container .card-body {
            padding: 2rem;
        }
        .alert {
            border-radius: 30px;
        }
        @media (max-width: 768px) {
            .login-container {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h2>Login</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="login.php">
                    <?php if (isset($_GET['error'])) { ?>
                        <div class="alert alert-danger text-center">
                            <?= htmlspecialchars($_GET['error']) ?>
                        </div>
                    <?php } ?>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" placeholder="Enter your username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
