<?php
session_start();
include('db.php');
require('fpdf.php'); // Ensure FPDF is included

// Check if the user is logged in and allowed to disapprove (e.g., Treasurer, Secretary, etc.)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['treasurer', 'secretary', 'chairperson', 'patron', 'lcc_treasurer', 'lcc_secretary', 'lcc_chair'])) {
    header('Location: index.php');
    exit();
}

// Function to redirect based on the user's role
function redirectToDashboard($role) {
    switch ($role) {
        case 'treasurer':
            header('Location: main_dashboard.php');
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
    $disapproval_comment = $_POST['comment']; // Get the comment from the form

    // Fetch current requisition status and group_id
    $query = "SELECT status, group_id FROM requisitions WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requisition_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $current_status = $row['status'];
    $group_id = $row['group_id'];
    $stmt->close();

    // Determine the previous approval stage based on the disapproval logic
    $previous_stage = '';
    switch ($current_role) {
        case 'lcc_chair':
            if ($current_status == 'LCC Secretary Approved') {
                $previous_stage = 'LCC Secretary';
            }
            break;
        case 'lcc_secretary':
            if ($current_status == 'LCC Treasurer Approved') {
                $previous_stage = 'LCC Treasurer';
            }
            break;
        case 'lcc_treasurer':
            if ($current_status == 'Patron Approved') {
                $previous_stage = 'Patron';
            }
            break;
        case 'patron':
            if ($current_status == 'Chairperson Approved') {
                $previous_stage = 'Chairperson';
            }
            break;
        case 'chairperson':
            if ($current_status == 'Secretary Approved') {
                $previous_stage = 'Secretary';
            }
            break;
        case 'secretary':
            if ($current_status == 'Treasurer Approved') {
                $previous_stage = 'Treasurer';
            }
            break;
    }

    // Update the requisition status to 'Disapproved', set the disapproval comment, and update the `updated_by` column
    if (!empty($previous_stage)) {
        // Update requisition status, disapproval comment, and updated_by
        $query = "UPDATE requisitions 
                  SET status = 'Disapproved', 
                      disapproval_comment = ?, 
                      updated_by = ?,
                      approved_by = NULL 
                  WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sii", $disapproval_comment, $current_user_id, $requisition_id);

        if ($stmt->execute()) {
            // Update the existing approval record to reflect the disapproval
            $query_disapproval = "UPDATE approvals 
                                  SET status = 'disapproved', 
                                      approved_by = ?, 
                                      comment = ? 
                                  WHERE requisition_id = ? 
                                  AND group_id = ? 
                                  AND role = ?";
            $stmt_disapproval = $conn->prepare($query_disapproval);
            $stmt_disapproval->bind_param("isisi", $current_user_id, $disapproval_comment, $requisition_id, $group_id, $current_role);

            if ($stmt_disapproval->execute()) {
                // If approval record is successfully updated, redirect to the appropriate dashboard
                redirectToDashboard($current_role);
            } else {
                echo "Error: Could not update the approval record. " . $stmt_disapproval->error;
            }

            $stmt_disapproval->close();
        } else {
            echo "Error: Could not disapprove requisition. " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: Invalid status transition. Please check the current status.";
    }

    $conn->close();
}
?>
