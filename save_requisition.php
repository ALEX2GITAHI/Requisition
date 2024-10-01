<?php
session_start();
include('db.php');
require('fpdf.php'); // Ensure FPDF is included

if (isset($_POST['submit_requisition'])) {
    // Fetch user ID from session
    $user_id = $_SESSION['user_id'];

    // Fetch user details and group details from the database
    $query = "SELECT users.first_name, users.last_name, users.role, users.group_id, groups.group_name, groups.group_logo 
              FROM users 
              JOIN groups ON users.group_id = groups.id 
              WHERE users.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($first_name, $last_name, $role, $group_id, $group_name, $group_logo);
    $stmt->fetch();
    $stmt->close(); // Close the statement

    // Combine first name and last name
    $full_name = $first_name . ' ' . $last_name;

    // Fetch requisition items from the session
    if (!isset($_SESSION['requisition_items']) || !is_array($_SESSION['requisition_items'])) {
        die("Requisition items are missing.");
    }
    
    $items = $_SESSION['requisition_items'];
    $total_amount = 0;
    foreach ($items as $item) {
        $total_amount += $item['total_cost'];
    }

    // Create a PDF instance
    $pdf = new FPDF();
    $pdf->AddPage();

    // Set font and add church logo
    $pdf->SetFont('Arial', 'B', 16);
    
    // Check if the group logo is not empty and determine the image type
    if (!empty($group_logo)) {
        // Save the logo temporarily and check its type
        $logo_path = 'temp_logo'; // Temporary file path without extension
        file_put_contents($logo_path, $group_logo); // Save logo binary data to a file

        // Validate the image type
        $image_info = getimagesize($logo_path);
        if ($image_info) {
            $mime_type = $image_info['mime'];
            $image_extension = '';

            // Detect the image format based on MIME type
            switch ($mime_type) {
                case 'image/jpeg':
                    $image_extension = 'jpg';
                    break;
                case 'image/png':
                    $image_extension = 'png';
                    break;
                case 'image/gif':
                    $image_extension = 'gif';
                    break;
                default:
                    unlink($logo_path); // Delete invalid file
                    die("Unsupported logo image format. Only JPEG, PNG, and GIF are supported.");
            }

            // Append the correct extension
            $logo_path .= '.' . $image_extension;
            rename('temp_logo', $logo_path); // Rename with proper extension

            // Add the logo to the PDF
            $pdf->Image($logo_path, 170, 6, 25);

            // Optionally delete the temporary logo file after use
            unlink($logo_path);
        } else {
            die("Invalid logo image format.");
        }
    } else {
        die("The group logo is not available or not a valid image.");
    }

    // Set font and add logo
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Image('assets/img/log2.png', 10, 6, 35);

    // Add title and church information
    $pdf->SetTextColor(0, 0, 128);
    $pdf->Cell(80);
    $pdf->Cell(30, 10, 'PCEA MUKINYI CHURCH', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(80);
    $pdf->Cell(30, 10, 'P.O BOX 60081-00200, City Square, Nairobi, Kenya', 0, 1, 'C');
    $pdf->Cell(80);
    $pdf->Cell(30, 10, 'Tel: 0720 510 840, 0756 364 466, Email: pceamukinyi@gmail.com', 0, 1, 'C');
    $pdf->Cell(80);
    $pdf->Cell(30, 10, 'My House will be called a House of Prayer for All Nations.,', 0, 1, 'C');

    // Line break
    $pdf->Ln(10);

    // Add group name, user details, and date
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(0);
    $pdf->SetFillColor(230, 230, 250);
    $pdf->Cell(0, 10, "Requisition Details", 0, 1, 'C', true);

    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(5);
    $pdf->Cell(40, 10, "Group: $group_name", 0, 1);
    $pdf->Cell(40, 10, "Created by: $full_name ($role)", 0, 1);
    $pdf->Cell(40, 10, "Date: " . date('Y-m-d H:i:s'), 0, 1);

    // Line break
    $pdf->Ln(10);

    // Add items table header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(100, 149, 237);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(10, 10, '#', 1, 0, 'C', true); // Added column for numbering
    $pdf->Cell(70, 10, 'Item Name', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Cost', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Quantity', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Total Cost', 1, 1, 'C', true);

    // Add items to the table with numbering
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0);
    $counter = 1; // Initialize the counter
    foreach ($items as $item) {
        $pdf->Cell(10, 10, $counter++, 1); // Number each item
        $pdf->Cell(70, 10, $item['item_name'], 1);
        $pdf->Cell(30, 10, number_format($item['item_cost'], 2), 1);
        $pdf->Cell(30, 10, $item['item_quantity'], 1);
        $pdf->Cell(40, 10, number_format($item['total_cost'], 2), 1);
        $pdf->Ln();
    }

    // Add total amount
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(173, 216, 230);
    $pdf->Cell(70, 10, "Total Amount", 1, 0, 'C', true);
    $pdf->Cell(40, 10, number_format($total_amount, 2), 1, 1, 'C');

    // Add footer
    $pdf->Ln(20);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, 'Footer information here', 0, 0, 'C');

    // Output the PDF to a variable
    $pdf_content = $pdf->Output('', 'S'); // Capture the PDF as a string

    // Save requisition to the database with group_id
    $stmt = $conn->prepare("INSERT INTO requisitions (group_id, created_by, total_amount, requisition_pdf, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param('iids', $group_id, $user_id, $total_amount, $pdf_content);
    $stmt->execute();
    $stmt->close();

    // Redirect to success page
    header("Location: main_dashboard.php?message=Requisition saved successfully");
    exit(); // Always exit after a redirect
}
?>
