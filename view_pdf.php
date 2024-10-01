<?php
session_start();
include('db.php');

// Check if requisition_id is provided
if (!isset($_GET['requisition_id'])) {
    echo "No requisition ID provided.";
    exit();
}

$requisition_id = $_GET['requisition_id'];

// Fetch the PDF data from the database based on the requisition ID
$query = "SELECT requisition_pdf FROM requisitions WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $requisition_id);
$stmt->execute();
$stmt->store_result();

// Check if a PDF is found
if ($stmt->num_rows === 0) {
    echo "No requisition found with that ID.";
    exit();
}

// Bind the result
$stmt->bind_result($pdf_data);
$stmt->fetch();

// Set the correct headers to display the PDF in the browser
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="requisition.pdf"');

// Output the PDF content
echo $pdf_data;

$stmt->close();
$conn->close();
?>
