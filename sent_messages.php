<?php
require 'db.php';           // Database connection
include 'infobip_api.php';  // Infobip API functions

// Ensure DB connection
if (!isset($conn)) {
    die("Database connection error.");
}

// Capture form inputs
$template_id     = $_POST['template_id'] ?? null;
$recipient_type  = $_POST['recipient_type'] ?? null; // all, role, group, individual
$custom_message  = $_POST['custom_message'] ?? '';
$user_id         = $_POST['user'] ?? null;           // for individual recipient
$requisition_id  = $_POST['requisition_id'] ?? null;
$status_type     = $_POST['status_type'] ?? 'approval'; // 'approval' or 'disapproval'

// Check for individual recipient
if ($recipient_type === 'individual' && empty($user_id)) {
    die("User ID not provided for individual recipient type.");
}

// Fetch the template text
$template_stmt = $conn->prepare("SELECT template_text FROM message_templates WHERE id = ?");
$template_stmt->bind_param("i", $template_id);
$template_stmt->execute();
$template_stmt->bind_result($template_text);
$template_stmt->fetch();
$template_stmt->close();

if (!$template_text) {
    die("Template not found.");
}

// Log message function
function logMessage($conn, $userId, $messageText, $status = 'sent') {
    $stmt = $conn->prepare("INSERT INTO message_logs (user_id, message_text, status) VALUES (?, ?, ?)");
    if (!$stmt) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        return;
    }
    $stmt->bind_param("iss", $userId, $messageText, $status);
    $stmt->execute();
    $stmt->close();
}

// Determine recipients
$recipients = [];
switch ($recipient_type) {
    case 'all':
        $res = $conn->query("SELECT users.id AS user_id, first_name, last_name, phone_number, role, group_name 
                             FROM users 
                             JOIN groups ON users.group_id = groups.id");
        $recipients = $res->fetch_all(MYSQLI_ASSOC);
        break;

    case 'role':
        $role = $_POST['role'] ?? 'treasurer';
        $stmt = $conn->prepare("SELECT users.id AS user_id, first_name, last_name, phone_number, role, group_name 
                                FROM users 
                                JOIN groups ON users.group_id = groups.id 
                                WHERE role = ?");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $recipients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        break;

    case 'group':
        $group_id = $_POST['group_id'] ?? 0;
        $stmt = $conn->prepare("SELECT users.id AS user_id, first_name, last_name, phone_number, role, group_name 
                                FROM users 
                                JOIN groups ON users.group_id = groups.id 
                                WHERE group_id = ?");
        $stmt->bind_param("i", $group_id);
        $stmt->execute();
        $recipients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        break;

    case 'individual':
        $stmt = $conn->prepare("SELECT users.id AS user_id, first_name, last_name, phone_number, role, group_name 
                                FROM users 
                                JOIN groups ON users.group_id = groups.id 
                                WHERE users.id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $recipients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        break;

    default:
        die("Invalid recipient type.");
}

// Send messages
foreach ($recipients as $recipient) {
    // Format phone number to international format (+254...)
    $phone = $recipient['phone_number'];
    if (substr($phone, 0, 1) === '0') {
        $phone = '+254' . substr($phone, 1);
    }

    // Replace template placeholders
    $message = str_replace(
        ['{{first_name}}', '{{last_name}}', '{{group_name}}', '{{role}}', '{{message_content}}', '{{requisition_id}}'],
        [$recipient['first_name'], $recipient['last_name'], $recipient['group_name'], $recipient['role'], $custom_message, $requisition_id],
        $template_text
    );

    // Send via Infobip
    $sendStatus = sendMessage($phone, $message) ? 'sent' : 'failed';

    // Log message
    logMessage($conn, $recipient['user_id'], $message, $sendStatus);
}

// Update requisition status if required
if ($requisition_id) {
    if ($status_type === 'approval') {
        $stmt = $conn->prepare("UPDATE requisitions SET status='approved', updated_at=NOW() WHERE id=?");
    } else if ($status_type === 'disapproval') {
        $stmt = $conn->prepare("UPDATE requisitions SET status='disapproved', updated_at=NOW() WHERE id=?");
    }
    $stmt->bind_param("i", $requisition_id);
    $stmt->execute();
    $stmt->close();
}

echo "Messages sent and logged successfully.";

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
