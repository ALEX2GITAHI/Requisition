<?php
require 'db.php';
include 'header.php';

// Fetch templates from the database
$templates = $conn->query("SELECT id, template_name, template_text FROM message_templates");

// Fetch users and groups for recipient selection
$users = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) AS full_name FROM users");
$groups = $conn->query("SELECT id, group_name FROM groups");
$roles = $conn->query("SELECT DISTINCT role FROM users");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* General Styling */
        body {
            background-color: #f0f4f8;
            font-family: 'Arial', sans-serif;
        }

        .dashboard-container {
            width: 100%;
            padding: 1.5rem;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .header-text {
            text-align: center;
            margin-bottom: 2rem;
            color: #007bff;
        }

        .table th {
            background-color: #007bff;
            color: white;
        }

        .btn-custom,
        .btn-primary,
        .btn-success,
        .btn-warning,
        .btn-danger {
            color: white;
        }

        /* Modal Design */
        .modal-header {
            background-color: #4caf50;
            color: white;
        }

        /* Dashboard Styling */
        .dashboard-buttons {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center bg-light p-3 border-bottom">
                    <h4 class="m-0">
                        <a href="admin_dashboard.php" class="text-decoration-none text-dark">Message Dashboard</a>
                    </h4>
                    <nav>
                        <ul class="nav">
                            <li class="nav-item"><a class="nav-link" href="manage_users.php">Manage Users</a></li>
                            <li class="nav-item"><a class="nav-link" href="manage_groups.php">Manage Groups</a></li>
                            <li class="nav-item"><a class="nav-link" href="view_requisitions.php">View Requisitions</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="message_dashboard.php">Message</a></li>
                            <li class="nav-item"><a class="nav-link" href="sent_messages.php">Message</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <div class="dashboard-buttons">
            <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#composeMessageModal">Compose
                Message</button>
            <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addTemplateModal">Add
                Template</button>
        </div>

        <!-- Templates Table -->
        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Template Name</th>
                            <th>Message</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($template = $templates->fetch_assoc()): ?>
                            <tr>
                                <td><?= $template['id']; ?></td>
                                <td><?= htmlspecialchars($template['template_name']); ?></td>
                                <td><?= htmlspecialchars($template['template_text']); ?></td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center">
                                        <button class="btn btn-warning btn-sm mr-2" data-toggle="modal"
                                            data-target="#editTemplateModal<?= $template['id']; ?>">Edit</button>
                                        <a href="delete_template.php?id=<?= $template['id']; ?>"
                                            class="btn btn-danger btn-sm">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Compose Message Modal -->
    <div class="modal fade" id="composeMessageModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="composeMessageForm" action="send_message.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Compose Message</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="recipient_type">Select Recipient Type:</label>
                            <select id="recipient_type" name="recipient_type" class="form-control" required
                                onchange="showRecipientOptions(this.value)">
                                <option value="">Select Recipient Type</option>
                                <option value="all">All Users</option>
                                <option value="role">By Role</option>
                                <option value="group">By Group</option>
                                <option value="individual">Individual User</option>
                            </select>
                        </div>

                        <!-- Recipient Selection -->
                        <div id="recipientOptions" class="form-group" style="display: none;">
                            <label for="recipient">Select Specific Recipient(s):</label>
                            <div id="recipientContainer"></div>
                        </div>

                        <!-- Template Selection -->
                        <div id="templateSelection" class="form-group">
                            <label for="template_select">Select Message Template:</label>
                            <select id="template_select" name="template_id" class="form-control" required
                                onchange="populateTemplateText()">
                                <option value="">Select Template</option>
                                <?php
                                // Reload templates for selection
                                $templates->data_seek(0);
                                while ($template = $templates->fetch_assoc()) {
                                    echo "<option value='{$template['id']}' data-text='{$template['template_text']}'>{$template['template_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Compose Message Text -->
                        <div class="form-group">
                            <label>Compose Message:</label>
                            <textarea name="custom_message" id="message_text" class="form-control" rows="4"
                                required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Template Modal -->
    <div class="modal fade" id="addTemplateModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="add_template.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Template</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="template_name">Template Name</label>
                            <input type="text" class="form-control" name="template_name" required>
                        </div>
                        <div class="form-group">
                            <label for="template_text">Template Text</label>
                            <textarea class="form-control" name="template_text" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Add Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Script to Handle Dynamic Template Text Population -->
    <script>
        function showRecipientOptions(recipientType) {
            let recipientContainer = document.getElementById('recipientContainer');
            let recipientOptionsDiv = document.getElementById('recipientOptions');

            recipientOptionsDiv.style.display = 'none';
            document.getElementById('template_select').value = ''; // Clear previous template

            if (recipientType === 'role') {
                recipientContainer.innerHTML = `
                    <select class="form-control" name="role" required>
                        <option value="">Select Role</option>
                        <?php $roles->data_seek(0);
                        while ($role = $roles->fetch_assoc()): ?>
                                    <option value="<?= $role['role']; ?>"><?= $role['role']; ?></option>
                        <?php endwhile; ?>
                    </select>
                `;
            } else if (recipientType === 'group') {
                recipientContainer.innerHTML = `
                    <select class="form-control" name="group" required>
                        <option value="">Select Group</option>
                        <?php $groups->data_seek(0);
                        while ($group = $groups->fetch_assoc()): ?>
                                    <option value="<?= $group['id']; ?>"><?= $group['group_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                `;
            } else if (recipientType === 'individual') {
                recipientContainer.innerHTML = `
                    <select class="form-control" name="user" required>
                        <option value="">Select User</option>
                        <?php $users->data_seek(0);
                        while ($user = $users->fetch_assoc()): ?>
                                    <option value="<?= $user['id']; ?>"><?= $user['full_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                `;
            }

            recipientOptionsDiv.style.display = recipientType === 'all' ? 'none' : 'block';
        }

        function populateTemplateText() {
            const templateSelect = document.getElementById("template_select");
            const selectedOption = templateSelect.options[templateSelect.selectedIndex];
            document.getElementById("message_text").value = selectedOption.dataset.text || '';
        }

    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>