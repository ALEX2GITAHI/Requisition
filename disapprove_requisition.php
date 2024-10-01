<?php
session_start(); // Start the session to access $_SESSION variables
include('db.php'); // Include the database connection file

// Check if user is logged in and the necessary session variables are set
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo "Error: User not logged in.";
    exit();
}

// Retrieve user information from session
$current_user_id = $_SESSION['user_id'];
$current_role = $_SESSION['role'];

// Check if requisition ID is provided
if (isset($_POST['requisition_id'])) {
    $requisition_id = $_POST['requisition_id'];
    $disapproval_comment = isset($_POST['comment']) ? $_POST['comment'] : ''; // Get disapproval comment

    // Prepare your SQL query to fetch current requisition status and group_id
    $query = "SELECT status, group_id FROM requisitions WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requisition_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Error: Requisition not found.";
        exit();
    }

    $row = $result->fetch_assoc();
    $current_status = $row['status'];
    $group_id = $row['group_id']; // Fetching group_id
    $stmt->close();

    // Determine who should review the requisition after disapproval
    $previous_role = '';
    switch ($current_role) {
        case 'secretary':
            $previous_role = 'treasurer';
            break;
        case 'chairperson':
            $previous_role = 'secretary';
            break;
        case 'patron':
            $previous_role = 'chairperson';
            break;
        case 'lcc_treasurer':
            $previous_role = 'patron';
            break;
        case 'lcc_secretary':
            $previous_role = 'lcc_treasurer';
            break;
        case 'lcc_chair':
            $previous_role = 'lcc_secretary';
            break;
        default:
            $previous_role = 'treasurer';
            break;
    }

    // Update requisition status and insert disapproval record
    if ($current_status == 'Pending' || strpos($current_status, 'Approved') !== false) {
        $new_status = ucfirst($previous_role) . " Review"; // Set new status for the previous role

        // Start a transaction to ensure both updates are successful
        $conn->begin_transaction();

        try {
            // Update the requisition status in the requisitions table
            $update_query = "UPDATE requisitions SET status = ?, disapproval_comment = ?, updated_by = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssii", $new_status, $disapproval_comment, $current_user_id, $requisition_id);
            if (!$stmt->execute()) {
                throw new Exception("Error updating requisition status: " . $stmt->error);
            }
            $stmt->close();

            // Insert disapproval record into approvals table
            $insert_query = "INSERT INTO approvals (requisition_id, group_id, role, approved_by, status, comment) VALUES (?, ?, ?, ?, 'rejected', ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iissi", $requisition_id, $group_id, $current_role, $current_user_id, $disapproval_comment);
            if (!$stmt->execute()) {
                throw new Exception("Error inserting disapproval record: " . $stmt->error);
            }
            $stmt->close();

            // Commit the transaction
            $conn->commit();

            // Redirect back to the previous user's dashboard for re-approval
            switch ($previous_role) {
                case 'treasurer':
                    header("Location: treasurer_dashboard.php?message=Requisition sent back for re-approval");
                    break;
                case 'secretary':
                    header("Location: secretary_dashboard.php?message=Requisition sent back for re-approval");
                    break;
                case 'chairperson':
                    header("Location: chairperson_dashboard.php?message=Requisition sent back for re-approval");
                    break;
                case 'patron':
                    header("Location: patron_dashboard.php?message=Requisition sent back for re-approval");
                    break;
                case 'lcc_treasurer':
                    header("Location: lcc_treasurer_dashboard.php?message=Requisition sent back for re-approval");
                    break;
                case 'lcc_secretary':
                    header("Location: lcc_secretary_dashboard.php?message=Requisition sent back for re-approval");
                    break;
                case 'lcc_chair':
                    header("Location: lcc_chair_dashboard.php?message=Requisition sent back for re-approval");
                    break;
                default:
                    header("Location: main_dashboard.php?message=Requisition sent back for re-approval");
                    break;
            }
            exit(); // Make sure to stop script execution after redirection

        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            $conn->rollback();
            echo "Error: " . $e->getMessage(); // Show detailed error message for debugging
        }
    } else {
        echo "Error: Invalid status for disapproval. Current status: $current_status";
    }

    $conn->close();
} else {
    echo "Error: No requisition ID provided.";
}
