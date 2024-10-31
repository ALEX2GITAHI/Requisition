<?php
session_start();
include('db.php');
require('fpdf.php'); // Ensure FPDF is included

// Check if the user is logged in and is allowed to approve or disapprove
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

if (isset($_POST['requisition_id'])) {
    $requisition_id = $_POST['requisition_id'];
    $current_role = $_SESSION['role'];
    $current_user_id = $_SESSION['user_id'];
    $comment = $_POST['comment']; // Get the disapproval comment

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

    // Determine the disapproval stage based on the current role and status
    $disapproval_stage = '';
    switch ($current_role) {
        case 'secretary':
            if ($current_status == 'Treasurer Approved' || $current_status == 'Disapproved by Chairperson') {
                $disapproval_stage = 'Disapproved by Secretary';
            }
            break;
        case 'chairperson':
            if ($current_status == 'Secretary Approved' || $current_status == 'Disapproved by Patron') {
                $disapproval_stage = 'Disapproved by Chairperson';
            }
            break;
        case 'patron':
            if ($current_status == 'Chairperson Approved' || $current_status == 'Disapproved by LCC Treasurer') {
                $disapproval_stage = 'Disapproved by Patron';
            }
            break;
        case 'lcc_treasurer':
            if ($current_status == 'Patron Approved' || $current_status == 'Disapproved by LCC Secretary') {
                $disapproval_stage = 'Disapproved by LCC Treasurer';
            }
            break;
        case 'lcc_secretary':
            if ($current_status == 'LCC Treasurer Approved' || $current_status == 'Disapproved by LCC Chairperson') {
                $disapproval_stage = 'Disapproved by LCC Secretary';
            }
            break;
        case 'lcc_chair':
            if ($current_status == ' LCC Secretary Approved' || $current_status == 'Disapproved by Treasurer') {
                $disapproval_stage = 'Disapproved by LCC Chairperson';
            }
            break;
        default:
            redirectToDashboard($current_role);
            break;
    }

    // Update the requisition status and add disapproval comment
    $query = "UPDATE requisitions SET status = ?, disapproval_comment = ?, disapproved_by = ?, updated_by = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssiii", $disapproval_stage, $comment, $current_user_id, $current_user_id, $requisition_id);
    $stmt->execute();
    $stmt->close();

    // Generate PDF for the disapproved requisition
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Disapproved Requisition', 1, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'Requisition ID: ' . $requisition_id, 0, 1);
    $pdf->Cell(0, 10, 'Disapproved by: ' . $_SESSION['username'], 0, 1);
    $pdf->Cell(0, 10, 'Disapproval Comment: ' . $comment, 0, 1);
    $pdf->Output('D', 'disapproved_requisition_' . $requisition_id . '.pdf', true);

    redirectToDashboard($current_role);
} else {
    header('Location: index.php');
    exit();
}
?>