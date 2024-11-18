<?php
// Assuming you're using MySQLi to connect to the database
if (isset($_GET['id'])) {
    $template_id = $_GET['id'];

    // Fetch the template text from the database
    $query = "SELECT template_text FROM message_templates WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $template_id);
    $stmt->execute();
    $stmt->bind_result($template_text);
    $stmt->fetch();

    echo $template_text; // Return the template text
}
?>
