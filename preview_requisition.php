<?php
include('db.php');

// Get the requisition ID from the URL
if (isset($_GET['id'])) {
    $requisition_id = $_GET['id'];

    // Fetch the requisition's PDF from the database
    $stmt = $conn->prepare("SELECT requisition_pdf FROM requisitions WHERE id = ?");
    $stmt->bind_param("i", $requisition_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($pdf_data);
    $stmt->fetch();

    // Check if PDF exists
    if ($stmt->num_rows > 0 && !empty($pdf_data)) {
        // Set headers to output PDF in the browser
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="requisition_' . $requisition_id . '.pdf"');

        // Output the PDF binary data
        echo $pdf_data;
    } else {
        echo "PDF not found.";
    }

    $stmt->close();
} else {
    echo "Invalid requisition ID.";
}

$conn->close();
?>
