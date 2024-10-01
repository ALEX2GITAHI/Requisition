<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
} else {
    // Redirect to dashboard based on user role
    switch ($_SESSION['role']) {
        case 'treasurer':
            header("Location: treasurer_dashboard.php");
            break;
        case 'secretary':
            header("Location: secretary_dashboard.php");
            break;
        case 'chairperson':
            header("Location: chair_dashboard.php");
            break;
        case 'patron':
            header("Location: patron_dashboard.php");
            break;
        case 'lcc_treasurer':
            header("Location: lcc_treasurer_dashboard.php");
            break;
        // Add other cases for different roles
        default:
            echo "Unknown role, please contact admin.";
            break;
    }
}
?>
