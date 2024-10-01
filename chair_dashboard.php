<?php
session_start();
include('header.php');
include('navbar.php');
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get current user's role and group ID
$current_user_id = $_SESSION['user_id'];
$role_query = "SELECT role, group_id FROM users WHERE id = ?";
$role_stmt = $conn->prepare($role_query);
$role_stmt->bind_param("i", $current_user_id);
$role_stmt->execute();
$role_result = $role_stmt->get_result();
$user_data = $role_result->fetch_assoc();
$current_user_role = $user_data['role'];
$group_id = $user_data['group_id'];

// Fetch requisition data for the user's group
$query = "SELECT r.id, g.group_name, r.total_amount, r.status 
          FROM requisitions r 
          JOIN groups g ON r.group_id = g.id
          WHERE r.group_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$requisitions = $stmt->get_result();
?>

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
                            <!-- View PDF button -->
                            <form action="view_pdf.php" method="GET" style="display:inline;">
                                <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-primary btn-sm">View PDF</button>
                            </form>

                            <!-- Approve and Disapprove buttons for the chairperson role -->
                            <?php if ($current_user_role == 'chairperson' && $row['status'] == 'Secretary Approved'): ?>
                                <form action="approve_requisition.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
                                <form action="disapprove_requisition.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                    <input type="text" name="comment" placeholder="Reason for disapproval" required>
                                    <button type="submit" class="btn btn-danger btn-sm">Disapprove</button>
                                </form>
                            <?php endif; ?>

                            <!-- Delete Requisition button -->
                            <form action="delete_requisition.php" method="POST" style="display:inline;">
                                <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this requisition?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('footer.php'); ?>
