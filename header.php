<?php
// Assuming $_SESSION['username'], $_SESSION['group_name'], and $_SESSION['role'] are set upon login
$username = $_SESSION['username'] ?? 'Guest';
$group_name = $_SESSION['group_name'] ?? 'Default Group';
$role = $_SESSION['role'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css"> <!-- Linking Custom CSS -->
    <style>
        /* Header Styling */
        .custom-header {
            background: linear-gradient(90deg, rgba(0, 123, 255, 1) 0%, rgba(33, 150, 243, 1) 50%, rgba(0, 123, 255, 1) 100%);
            color: white;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .custom-header img {
            width: 40px;
            height: auto;
        }
        .header-left {
            display: flex;
            align-items: center;
        }
        .header-middle {
            flex: 1;
            text-align: center;
            font-size: 1.2rem;
            color: #f8f9fa;
        }
        .header-middle span {
            display: inline-block;
            margin-right: 20px;
        }
        .header-right {
            display: flex;
            align-items: center;
        }
        .logout-link {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 1rem;
            background-color: #dc3545;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .logout-link:hover {
            background-color: #c82333;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <header class="custom-header">
        <div class="container-fluid">
            <div class="header-content">
                <!-- Church Logo -->
                <div class="header-left">
                    <img src="assets/img/log2.png" alt="Church Logo"> <!-- Replace with actual logo path -->
                    <span><strong>PCEA MUKINYI</strong></span>
                </div>

                <!-- User Info: Date, Time, and Username -->
                <div class="header-middle">
                    <span>Welcome, <?= htmlspecialchars($username) ?> (Role: <?= htmlspecialchars($role) ?>, Group: <?= htmlspecialchars($group_name) ?>)</span>
                    <span><strong>Date & Time:</strong> <span id="datetime"></span></span>
                </div>

                <!-- Logout Link -->
                <div class="header-right">
                    <a href="logout.php" class="logout-link">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <script>
        // Function to display Nairobi time
        function updateTime() {
            const now = new Date().toLocaleString('en-US', { timeZone: 'Africa/Nairobi' });
            document.getElementById('datetime').textContent = now;
        }
        setInterval(updateTime, 1000); // Update the time every second
    </script>

    <!-- Optional: Bootstrap JavaScript for responsive features -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
