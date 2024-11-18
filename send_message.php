<?php
require 'db.php';  // Ensure this file initializes $conn
include 'infobip_api.php'; // Infobip API functions

// Ensure $conn is defined and connected
if (!isset($conn)) {
    echo "Database connection error.";
    exit;
}

// Capture form inputs
$template_id = $_POST['template_id'] ?? null;
$recipient_type = $_POST['recipient_type'] ?? null;
$custom_message = $_POST['custom_message'] ?? '';
$user_id = $_POST['user'] ?? null;

// Debugging: Print received POST data (remove in production)
print_r($_POST); 
echo "<br>";

// Check if `user_id` is provided when needed
if ($recipient_type === 'individual' && empty($user_id)) {
    echo "User ID not provided for individual recipient type.";
    exit;
}

// Fetch the template text
$template_stmt = $conn->prepare("SELECT template_text FROM message_templates WHERE id = ?");
$template_stmt->bind_param("i", $template_id);
$template_stmt->execute();
$template_stmt->bind_result($template_text);
$template_stmt->fetch();
$template_stmt->close();

if (!$template_text) {
    echo "Template not found.";
    exit;
}

// Function to log sent messages
function logMessage($conn, $userId, $messageText, $status = 'sent') {
    $stmt = $conn->prepare("INSERT INTO message_logs (user_id, message_text, status) VALUES (?, ?, ?)");
    if (!$stmt) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        return;
    }
    $stmt->bind_param("iss", $userId, $messageText, $status);
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    $stmt->close();
}

// Determine recipients based on recipient type
$recipients = [];
switch ($recipient_type) {
    case 'all':
        $recipients_query = "SELECT users.id AS user_id, first_name, last_name, phone_number, role, group_name 
                             FROM users JOIN groups ON users.group_id = groups.id";
        $recipients_result = $conn->query($recipients_query);
        $recipients = $recipients_result->fetch_all(MYSQLI_ASSOC);
        break;
    case 'role':
        $role = 'treasurer';
        $stmt = $conn->prepare("SELECT users.id AS user_id, first_name, last_name, phone_number, role, group_name 
                                FROM users JOIN groups ON users.group_id = groups.id WHERE role = ?");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $recipients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        break;
    case 'group':
        $group_id = 2;
        $stmt = $conn->prepare("SELECT users.id AS user_id, first_name, last_name, phone_number, role, group_name 
                                FROM users JOIN groups ON users.group_id = groups.id WHERE group_id = ?");
        $stmt->bind_param("i", $group_id);
        $stmt->execute();
        $recipients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        break;
    case 'individual':
        $stmt = $conn->prepare("SELECT users.id AS user_id, first_name, last_name, phone_number, role, group_name 
                                FROM users JOIN groups ON users.group_id = groups.id WHERE users.id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $recipients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        break;
    default:
        echo "Invalid recipient type.";
        exit;
}

// Send messages to each recipient
foreach ($recipients as $recipient) {
    $message = str_replace(
        ['{{first_name}}', '{{last_name}}', '{{group_name}}', '{{role}}', '{{message_content}}'],
        [$recipient['first_name'], $recipient['last_name'], $recipient['group_name'], $recipient['role'], $custom_message],
        $template_text
    );

    // Send message to each recipient and log the result
foreach ($recipients as $recipient) {
    $message = str_replace(
        ['{{first_name}}', '{{last_name}}', '{{group_name}}', '{{role}}', '{{message_content}}'],
        [$recipient['first_name'], $recipient['last_name'], $recipient['group_name'], $recipient['role'], $custom_message],
        $template_text
    );

    // Use Infobip API to send message and get the result
    $sendStatus = sendMessage($recipient['phone_number'], $message) ? 'sent' : 'failed';

    // Log the message with the proper status
    logMessage($conn, $recipient['user_id'], $message, $sendStatus);
}

}

echo "Messages sent and logged successfully.";
?>
