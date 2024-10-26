<?php
session_start();
include('header.php');
include('navbar.php');
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Get current user's information
$current_user_id = $_SESSION['user_id'];

// Retrieve the user's group ID and role
$user_query = "SELECT group_id, role FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $current_user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_info = $user_result->fetch_assoc();
$current_user_group_id = $user_info['group_id'];
$current_user_role = $user_info['role'];

// Fetch requisition data for the user's group
$query = "SELECT r.id, g.group_name, r.total_amount, r.status, r.disapproval_comment, u.role AS disapprover_role 
          FROM requisitions r 
          JOIN groups g ON r.group_id = g.id 
          LEFT JOIN users u ON u.id = r.updated_by
          WHERE r.group_id = ?";  // Filter by the user's group
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $current_user_group_id);
$stmt->execute();
$requisitions = $stmt->get_result();
?>

<!-- Navigation Bar -->
<link href="assets/img/log2.png" rel="shortcut icon">
<nav class="navbar navbar-expand-lg navbar-light bg-light mt-9">
    <div class="container-fluid">
        <a class="navbar-brand" href="main_dashboard.php">Dashboard</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="treasurer_dashboard.php">Create Requisition</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Table for displaying requisition statuses -->
<div class="card mt-2">
    <div class="card-header">
        <h4>Requisition Statuses</h4>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Group Name</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Actions</th> <!-- Updated to include Actions -->
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $requisitions->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['group_name'] ?></td>
                    <td><?= number_format($row['total_amount'], 2) ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td>
                        <!-- All actions are aligned on one line -->
                        <div style="display: flex; align-items: center;">

                            <!-- Disapproval comment with the role of the disapprover -->
                            <?php if ($row['status'] == 'Disapproved' && !empty($row['disapproval_comment'])) { ?>
                                <div class="alert alert-warning" role="alert" style="margin-right: 10px;">
                                    <strong>Disapproval Comment (<?= htmlspecialchars($row['disapprover_role']) ?>):</strong> <?= htmlspecialchars($row['disapproval_comment']); ?>
                                </div>
                            <?php } ?>
                            
                            <!-- Approve and Disapprove buttons for Pending requisitions -->
                            <?php if ($row['status'] == 'Pending') { ?>
                                <form action="approve_requisition.php" method="POST" style="margin-right: 10px;">
                                    <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
                                <form action="disapprove_requisition.php" method="POST" style="margin-right: 10px;">
                                    <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                    <input type="text" name="comment" placeholder="Reason for disapproval" required>
                                    <button type="submit" class="btn btn-danger btn-sm">Disapprove</button>
                                </form>
                            <?php } ?>

                            <!-- View PDF button -->
                            <form action="view_pdf.php" method="GET" style="margin-right: 10px;">
                                <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-primary btn-sm">View PDF</button>
                            </form>

                            <!-- Delete Requisition button -->
                            <form action="delete_requisition.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this requisition?')" style="margin-right: 10px;">
                                <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('footer.php'); ?>
