<?php
session_start();
include('db.php');
require('fpdf.php'); // Ensure FPDF is included

// Check if the user is logged in and is allowed to approve (e.g., Treasurer, Secretary, etc.)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['treasurer', 'secretary', 'chairperson', 'patron', 'lcc_treasurer', 'lcc_secretary', 'lcc_chair'])) {
    header('Location: login.php');
    exit();
}

// Function to redirect based on the user's role
function redirectToDashboard($role) {
    switch ($role) {
        case 'treasurer':
            header('Location: treasurer_dashboard.php');
            break;
        case 'secretary':
            header('Location: secretary_dashboard.php');
            break;
        case 'chairperson':
            header('Location: chairperson_dashboard.php');
            break;
        case 'patron':
            header('Location: patron_dashboard.php');
            break;
        case 'lcc_treasurer':
            header('Location: lcc_treasurer_dashboard.php');
            break;
        case 'lcc_secretary':
            header('Location: lcc_secretary_dashboard.php');
            break;
        case 'lcc_chair':
            header('Location: lcc_chair_dashboard.php');
            break;
        default:
            header('Location: user_dashboard.php');
            break;
    }
    exit();
}

if (isset($_POST['requisition_id'])) {
    $requisition_id = $_POST['requisition_id'];
    $current_role = $_SESSION['role'];
    $current_user_id = $_SESSION['user_id'];

    // Fetch current requisition status and group_id
    $query = "SELECT status, group_id FROM requisitions WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requisition_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $current_status = $row['status'];
    $group_id = $row['group_id'];  // Fetching group_id
    $stmt->close();

    // Determine the next approval stage
    $next_stage = '';
    switch ($current_role) {
        case 'treasurer':
            if ($current_status == 'Pending') {
                $next_stage = 'Treasurer Approved';
            }
            break;
        case 'secretary':
            if ($current_status == 'Treasurer Approved') {
                $next_stage = 'Secretary Approved';
            }
            break;
        case 'chairperson':
            if ($current_status == 'Secretary Approved') {
                $next_stage = 'Chairperson Approved';
            }
            break;
        case 'patron':
            if ($current_status == 'Chairperson Approved') {
                $next_stage = 'Patron Approved';
            }
            break;
        case 'lcc_treasurer':
            if ($current_status == 'Patron Approved') {
                $next_stage = 'LCC Treasurer Approved';
            }
            break;
        case 'lcc_secretary':
            if ($current_status == 'LCC Treasurer Approved') {
                $next_stage = 'LCC Secretary Approved';
            }
            break;
        case 'lcc_chair':
            if ($current_status == 'LCC Secretary Approved') {
                $next_stage = 'LCC Chairperson Approved';
            }
            break;
    }

    // Update the requisition and insert into approvals table
    if (!empty($next_stage)) {
        // Update the requisition status
        $query = "UPDATE requisitions SET status = ?, approved_by = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $next_stage, $current_user_id, $requisition_id);
        
        if ($stmt->execute()) {
            // Insert approval record into approvals table with group_id
            $query_approval = "INSERT INTO approvals (requisition_id, group_id, role, approved_by, status) VALUES (?, ?, ?, ?, 'approved')";
            $stmt_approval = $conn->prepare($query_approval);
            $stmt_approval->bind_param("iisi", $requisition_id, $group_id, $current_role, $current_user_id);  // Adding group_id to the query
            $stmt_approval->execute();
            $stmt_approval->close();

            // Redirect to the appropriate dashboard
            redirectToDashboard($current_role);
        } else {
            echo "Error: Could not approve requisition. " . $stmt->error; // Added error details for debugging
        }

        $stmt->close();
    } else {
        echo "Error: Invalid status transition. Please check the current status.";
    }

    $conn->close();
}
?>
