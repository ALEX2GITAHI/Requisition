<?php
// Include database connection and Infobip API functions
require 'db.php';
include 'infobip_api.php';

// Ensure database connection is established
if (!isset($conn)) {
    die("Database connection error.");
}

// Capture form inputs and verify template_id
$template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : null;
$recipient_type = $_POST['recipient_type'] ?? null;
$custom_message = $_POST['custom_message'] ?? '';
$user_id = isset($_POST['user']) ? intval($_POST['user']) : null; // For individual user selection

// Ensure `template_id` is provided
if (!$template_id) {
    die("Template ID is required.");
}

// Fetch the template text
$template_stmt = $conn->prepare("SELECT template_text FROM message_templates WHERE id = ?");
$template_stmt->bind_param("i", $template_id);
$template_stmt->execute();
$template_stmt->bind_result($template_text);
$template_stmt->fetch();
$template_stmt->close();

if (!$template_text) {
    die("Template not found. Please check the selected template.");
}
// Function to log sent messages
function logMessage($conn, $userId, $messageText, $status = 'sent') {
    $stmt = $conn->prepare("INSERT INTO message_log (user_id, message_text, status) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $messageText, $status);
    $stmt->execute();
    $stmt->close();
}
// Determine recipients based on recipient type
$recipients = [];
switch ($recipient_type) {
    case 'all':
        $recipients_query = "SELECT id, first_name, last_name, phone_number, role, group_name FROM users JOIN groups ON users.group_id = groups.id";
        $recipients_result = $conn->query($recipients_query);
        $recipients = $recipients_result->fetch_all(MYSQLI_ASSOC);
        break;
    case 'role':
        $role = $_POST['role'] ?? 'treasurer'; // Set role dynamically as needed
        $stmt = $conn->prepare("SELECT id, first_name, last_name, phone_number, role, group_name FROM users JOIN groups ON users.group_id = groups.id WHERE role = ?");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $recipients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        break;
    case 'group':
        $group_id = $_POST['group_id'] ?? 2; // Set group_id dynamically as needed
        $stmt = $conn->prepare("SELECT id, first_name, last_name, phone_number, role, group_name FROM users JOIN groups ON users.group_id = groups.id WHERE group_id = ?");
        $stmt->bind_param("i", $group_id);
        $stmt->execute();
        $recipients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        break;
    case 'individual':
        if (empty($user_id)) {
            die("User ID not provided for individual recipient type.");
        }
        $stmt = $conn->prepare("SELECT id, first_name, last_name, phone_number, role, group_name FROM users JOIN groups ON users.group_id = groups.id WHERE users.id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $recipients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        break;
    default:
        die("Invalid recipient type.");
}

// Process each recipient and log the message
foreach ($recipients as $recipient) {
    $message = str_replace(
        ['{{first_name}}', '{{last_name}}', '{{group_name}}', '{{role}}', '{{message_content}}'],
        [$recipient['first_name'], $recipient['last_name'], $recipient['group_name'], $recipient['role'], $custom_message],
        $template_text
    );

    // Send the message and capture the status
    $status = sendMessage($recipient['phone_number'], $message) ? 'sent' : 'not sent';

    // Log message in `message_log` table
    $stmt = $conn->prepare("INSERT INTO message_log (user_id, message_text, sent_at, status) VALUES (?, ?, NOW(), ?)");
    $stmt->bind_param("iss", $recipient['id'], $message, $status);
    $stmt->execute();
    $stmt->close();
}

echo "Messages processed and logged successfully.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Dashboard</title>
    <link rel="stylesheet" href="path/to/your/styles.css">
</head>
<body>

<div class="dashboard-container">
    <h2>Sent Messages Dashboard</h2>
    <table>
        <thead>
            <tr>
                <th>User Name</th>
                <th>Group</th>
                <th>Role</th>
                <th>Message Text</th>
                <th>Sent At</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch sent messages for the dashboard
            $query = "
                SELECT 
                    m.id,
                    u.first_name,
                    u.last_name,
                    g.group_name,
                    u.role,
                    m.message_text,
                    m.sent_at,
                    m.status
                FROM 
                    message_log AS m
                JOIN 
                    users AS u ON m.user_id = u.id
                JOIN 
                    groups AS g ON u.group_id = g.id
                ORDER BY 
                    m.sent_at DESC
            ";
            $result = $conn->query($query);

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['group_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                echo "<td>" . htmlspecialchars($row['message_text']) . "</td>";
                echo "<td>" . htmlspecialchars($row['sent_at']) . "</td>";
                echo "<td>" . htmlspecialchars(ucfirst($row['status'])) . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
