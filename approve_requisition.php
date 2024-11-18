<?php
session_start();
include('db.php');
require('fpdf.php'); // Ensure FPDF is included
include('Infobip_API.php'); // Include Infobip API for sending messages

// Check if the user is logged in and allowed to approve
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['treasurer', 'secretary', 'chairperson', 'patron', 'lcc_treasurer', 'lcc_secretary', 'lcc_chair'])) {
    header('Location: index.php');
    exit();
}

// Redirect based on the user's role
function redirectToDashboard($role) {
    switch ($role) {
        case 'treasurer':
            header('Location: main_dashboard.php');
            break;
        case 'secretary':
            header('Location: secretary_dashboard.php');
            break;
        case 'chairperson':
            header('Location: chair_dashboard.php');
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

// Function to fetch and format a message template
function getFormattedMessage($templateName, $data, $conn) {
    $query = "SELECT template_text FROM message_templates WHERE template_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $templateName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $message = $row['template_text'];
        foreach ($data as $key => $value) {
            $message = str_replace("{{{$key}}}", $value, $message);
        }
        return $message;
    } else {
        return false;
    }
}

// Function to send approval notification
function sendApprovalNotification($phoneNumber, $templateName, $data, $conn) {
    $message = getFormattedMessage($templateName, $data, $conn);
    if ($message) {
        sendMessage($phoneNumber, $message);
    } else {
        echo "Error: Template not found.";
    }
}

// Approval process
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
    $group_id = $row['group_id'];
    $stmt->close();

    // Determine the next approval stage
    $next_stage = '';
    $next_approver_role = '';
    switch ($current_role) {
        case 'treasurer':
            if ($current_status == 'Pending' || $current_status == 'Disapproved by Secretary') {
                $next_stage = 'Treasurer Approved';
                $next_approver_role = 'secretary';
            }
            break;
        case 'secretary':
            if ($current_status == 'Treasurer Approved' || $current_status == 'Disapproved by Chairperson') {
                $next_stage = 'Secretary Approved';
                $next_approver_role = 'chairperson';
            }
            break;
        case 'chairperson':
            if ($current_status == 'Secretary Approved' || $current_status == 'Disapproved by Patron') {
                $next_stage = 'Chairperson Approved';
                $next_approver_role = 'patron';
            }
            break;
        case 'patron':
            if ($current_status == 'Chairperson Approved' || $current_status == 'Disapproved by LCC Treasurer') {
                $next_stage = 'Patron Approved';
                $next_approver_role = 'lcc_treasurer';
            }
            break;
        case 'lcc_treasurer':
            if ($current_status == 'Patron Approved' || $current_status == 'Disapproved by LCC Secretary') {
                $next_stage = 'LCC Treasurer Approved';
                $next_approver_role = 'lcc_secretary';
            }
            break;
        case 'lcc_secretary':
            if ($current_status == 'LCC Treasurer Approved' || $current_status == 'Disapproved by LCC Chairperson') {
                $next_stage = 'LCC Secretary Approved';
                $next_approver_role = 'lcc_chair';
            }
            break;
        case 'lcc_chair':
            if ($current_status == 'LCC Secretary Approved') {
                $next_stage = 'LCC Chairperson Approved';
            }
            break;
    }

    // Update the requisition status and approval records
    if (!empty($next_stage)) {
        $query = "UPDATE requisitions SET status = ?, approved_by = ?, updated_by = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $next_stage, $current_user_id, $current_user_id, $requisition_id);

        if ($stmt->execute()) {
            $query_approval = "UPDATE approvals SET status = 'approved', approved_by = ?, role = ? 
                               WHERE requisition_id = ? AND group_id = ?";
            $stmt_approval = $conn->prepare($query_approval);
            $stmt_approval->bind_param("isii", $current_user_id, $current_role, $requisition_id, $group_id);

            if ($stmt_approval->execute() && $stmt_approval->affected_rows == 0) {
                $query_insert_approval = "INSERT INTO approvals (requisition_id, group_id, role, approved_by, status) 
                                          VALUES (?, ?, ?, ?, 'approved')";
                $stmt_insert = $conn->prepare($query_insert_approval);
                $stmt_insert->bind_param("iisi", $requisition_id, $group_id, $current_role, $current_user_id);
                $stmt_insert->execute();
                $stmt_insert->close();
            }
            $stmt_approval->close();

            if (!empty($next_approver_role)) {
                $query_next_approver = "SELECT u.phone_number, u.first_name, g.group_name
                                        FROM users u
                                        JOIN groups g ON u.group_id = g.id
                                        WHERE u.role = ? AND u.group_id = ?";
                $stmt_next = $conn->prepare($query_next_approver);
                $stmt_next->bind_param("si", $next_approver_role, $group_id);
                $stmt_next->execute();
                $result_next = $stmt_next->get_result();

                if ($row_next = $result_next->fetch_assoc()) {
                    $data = [
                        'first_name' => $row_next['first_name'],
                        'group_name' => $row_next['group_name']
                    ];
                    sendApprovalNotification($row_next['phone_number'], 'Approval Request Notification', $data, $conn);
                }
                $stmt_next->close();
            }

            redirectToDashboard($current_role);
        } else {
            echo "Error: Could not approve requisition. " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: Invalid status transition.";
    }

    $conn->close();
}
?>
