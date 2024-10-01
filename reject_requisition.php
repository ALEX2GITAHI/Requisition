<?php
session_start();
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['requisition_id']) && isset($_POST['disapproval_comment'])) {
    $requisition_id = $_POST['requisition_id'];
    $comment = $_POST['disapproval_comment'];
    $current_role = $_SESSION['role'];

    // Determine the previous role based on the current approver
    $previous_stage = '';
    switch ($current_role) {
        case 'secretary':
            $previous_stage = 'Treasurer Pending';
            break;
        case 'chairperson':
            $previous_stage = 'Secretary Pending';
            break;
        case 'patron':
            $previous_stage = 'Chairperson Pending';
            break;
        case 'lcc_treasurer':
            $previous_stage = 'Patron Pending';
            break;
        case 'lcc_secretary':
            $previous_stage = 'LCC Treasurer Pending';
            break;
        case 'lcc_chairperson':
            $previous_stage = 'LCC Secretary Pending';
            break;
    }

    // Update the requisition status and add the disapproval comment
    $query = "UPDATE requisitions SET status = 'Disapproved', disapproval_comment = ?, approved_by = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $comment, $_SESSION['username'], $requisition_id);

    if ($stmt->execute()) {
        header('Location: dashboard.php?message=Requisition disapproved and sent back');
    } else {
        echo "Error: Could not disapprove requisition.";
    }

    $stmt->close();
    $conn->close();
}
?>
