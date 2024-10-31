<?php
include('header.php');
include('navbar.php');
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Get current user's information (role and group)
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

<!-- Table for displaying requisition statuses -->
<link href="assets/img/log2.png" rel="shortcut icon">
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $requisitions->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['group_name'] ?></td>
                        <td><?= number_format($row['total_amount'], 2) ?></td>
                        <td><?= ucfirst($row['status']) ?></td>
                        <td>
                            <div style="display: flex; align-items: center;">
                                <!-- Disapproval comment with the role of the disapprover -->
                                <?php if ($row['status'] == 'Disapproved' && !empty($row['disapproval_comment'])): ?>
                                    <div class="alert alert-warning" role="alert" style="margin-right: 10px;">
                                        <strong>Disapproval Comment (<?= htmlspecialchars($row['disapprover_role']) ?>):</strong>
                                        <?= htmlspecialchars($row['disapproval_comment']); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Approve and Disapprove buttons for chairperson when secretary has approved -->
                                <?php if ($current_user_role == 'chairperson' && $row['status'] == 'Secretary Approved'): ?>
                                    <form action="approve_requisition.php" method="POST" style="display:inline; margin-right: 5px;">
                                        <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <form action="disapprove_requisition.php" method="POST" style="display:inline; margin-right: 5px;">
                                        <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                        <input type="text" name="comment" placeholder="Reason for disapproval" required style="width: 150px; height: 30px;">
                                        <button type="submit" class="btn btn-danger btn-sm">Disapprove</button>
                                    </form>
                                <?php endif; ?>

                                <!-- View PDF button -->
                                <form action="view_pdf.php" method="GET" style="display:inline; margin-right: 5px;">
                                    <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">View PDF</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('footer.php'); ?>
