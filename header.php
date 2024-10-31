<?php
// Start the session to access session variables
session_start();
// Ensure the user is logged in, else redirect to the login page
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header('Location: index.php'); // Redirect to login if session is not set
    exit(); // Stop further script execution
}

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
    <link href="assets/img/log2.png" rel="shortcut icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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

                <!-- User Info: Date, Day, Time, and Username -->
                <div class="header-middle">
                    <span>Welcome, <?= htmlspecialchars($username) ?> (Role: <?= htmlspecialchars($role) ?>, Group: <?= htmlspecialchars($group_name) ?>)</span>
                    <span><strong></strong> <span id="datetime"></span></span>
                </div>

                <!-- Logout Link -->
                <div class="header-right">
                    <a href="logout.php" class="logout-link">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <script>
        // Function to display Nairobi time with the day of the week, date, hour, and minute
        function updateTime() {
            const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const now = new Date(new Date().toLocaleString('en-US', { timeZone: 'Africa/Nairobi' }));

            const dayName = daysOfWeek[now.getDay()]; // Get day of the week

            // Get the current date in YYYY-MM-DD format
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
            const date = String(now.getDate()).padStart(2, '0'); // Day of the month

            // Format the time as HH:MM
            const hours = now.getHours().toString().padStart(2, '0'); // Get hours, padded to 2 digits
            const minutes = now.getMinutes().toString().padStart(2, '0'); // Get minutes, padded to 2 digits

            // Format as "YYYY-MM-DD, Day, HH:MM"
            const formattedDateTime = `${year}-${month}-${date}, ${dayName}, ${hours}:${minutes}`;

            document.getElementById('datetime').textContent = formattedDateTime; // Display time in HTML
        }

        setInterval(updateTime, 1000); // Update the time every second

        // Idle timeout logic
        let idleTime = 0;

        // Increment the idle time counter every minute
        function timerIncrement() {
            idleTime += 1;
            if (idleTime >= 5) { // 5 minutes of inactivity
                window.location.href = 'logout.php'; // Redirect to logout page
            }
        }

        // Reset idle timer on user activity
        function resetIdleTime() {
            idleTime = 0;
        }

        // Track various user activities
        window.onload = function() {
            // Increment the idle time every minute
            setInterval(timerIncrement, 60000); // 1 minute = 60000 ms

            // Reset the idle timer on any of these events
            document.onmousemove = resetIdleTime;
            document.onkeypress = resetIdleTime;
            document.onscroll = resetIdleTime;
            document.onclick = resetIdleTime;
        };
    </script>

    <!-- Optional: Bootstrap JavaScript for responsive features -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
