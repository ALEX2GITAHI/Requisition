<?php
require('fpdf.php');
include('db.php');

if (isset($_GET['requisition_id'])) {
    $requisition_id = $_GET['requisition_id'];

    // Fetch requisition details
    $query = "SELECT r.*, g.group_name, u.name AS created_by_name 
              FROM requisitions r
              JOIN groups g ON r.group_id = g.id
              JOIN users u ON r.created_by = u.id
              WHERE r.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $requisition_id);
    $stmt->execute();
    $requisition = $stmt->get_result()->fetch_assoc();

    // Fetch requisition items
    $query_items = "SELECT * FROM requisition_items WHERE requisition_id = ?";
    $stmt_items = $conn->prepare($query_items);
    $stmt_items->bind_param('i', $requisition_id);
    $stmt_items->execute();
    $items = $stmt_items->get_result();

    // Create PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Add Church Letterhead
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Church Name', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Address Line 1', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Verse: "I can do all things through Christ who strengthens me."', 0, 1, 'C');

    // Add Requisition Details
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Requisition Details', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Group: ' . $requisition['group_name'], 0, 1);
    $pdf->Cell(0, 10, 'Created By: ' . $requisition['created_by_name'], 0, 1);
    $pdf->Cell(0, 10, 'Total Amount: KSh ' . number_format($requisition['total_amount'], 2), 0, 1);
    
    // Add Approval Details
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Approval Status', 0, 1);
    $pdf->SetFont('Arial', '', 12);

    // Check status and display approvals based on requisition status
    switch ($requisition['status']) {
        case 'Pending':
            $pdf->Cell(0, 10, 'Status: Pending Approval', 0, 1);
            break;
        case 'Treasurer Approved':
            $pdf->Cell(0, 10, 'Approved by Treasurer: ' . $requisition['approved_by_treasurer'], 0, 1);
            // Fall through to include other approvals if applicable
        case 'Secretary Approved':
            $pdf->Cell(0, 10, 'Approved by Secretary: ' . $requisition['approved_by_secretary'], 0, 1);
            // Fall through to include other approvals if applicable
        case 'Chairperson Approved':
            $pdf->Cell(0, 10, 'Approved by Chairperson: ' . $requisition['approved_by_chairperson'], 0, 1);
            // Fall through to include other approvals if applicable
        case 'Patron Approved':
            $pdf->Cell(0, 10, 'Approved by Patron: ' . $requisition['approved_by_patron'], 0, 1);
            // Fall through to include other approvals if applicable
        case 'LCC Treasurer Approved':
            $pdf->Cell(0, 10, 'Approved by LCC Treasurer: ' . $requisition['approved_by_lcc_treasurer'], 0, 1);
            // Fall through to include other approvals if applicable
        case 'LCC Secretary Approved':
            $pdf->Cell(0, 10, 'Approved by LCC Secretary: ' . $requisition['approved_by_lcc_secretary'], 0, 1);
            // Fall through to include other approvals if applicable
        case 'LCC Chairperson Approved':
            $pdf->Cell(0, 10, 'Approved by LCC Chairperson: ' . $requisition['approved_by_lcc_chairperson'], 0, 1);
            break;
        default:
            $pdf->Cell(0, 10, 'Status: Unknown', 0, 1);
            break;
    }

    // Add Requisition Items
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Item', 1);
    $pdf->Cell(40, 10, 'Cost', 1);
    $pdf->Cell(40, 10, 'Quantity', 1);
    $pdf->Cell(40, 10, 'Total Cost', 1);
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 12);
    while ($item = $items->fetch_assoc()) {
        $pdf->Cell(40, 10, $item['item_name'], 1);
        $pdf->Cell(40, 10, 'KSh ' . number_format($item['item_cost'], 2), 1);
        $pdf->Cell(40, 10, $item['item_quantity'], 1);
        $pdf->Cell(40, 10, 'KSh ' . number_format($item['total_cost'], 2), 1);
        $pdf->Ln();
    }

    // Output the PDF
    $pdf->Output();
} else {
    echo "Requisition ID not provided.";
}
?>
