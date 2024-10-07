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

// Get current user's role
$current_user_id = $_SESSION['user_id'];
$role_query = "SELECT role FROM users WHERE id = ?";
$role_stmt = $conn->prepare($role_query);
$role_stmt->bind_param("i", $current_user_id);
$role_stmt->execute();
$role_result = $role_stmt->get_result();
$current_user_role = $role_result->fetch_assoc()['role'];

// Fetch requisition data
$query = "SELECT r.id, g.group_name, r.total_amount, r.status, r.disapproval_comment 
          FROM requisitions r 
          JOIN groups g ON r.group_id = g.id";
$stmt = $conn->prepare($query);
$stmt->execute();
$requisitions = $stmt->get_result();
?>

<!-- Navigation Bar -->
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
                    <th>Actions</th> <!-- Update header to include Actions -->
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
                        <!-- View PDF button -->
                        <form action="view_pdf.php" method="GET" style="display:inline;">
                            <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-primary btn-sm">View PDF</button>
                        </form>

                        <!-- Inside the while loop of requisition data -->
<?php
if ($current_user_role == 'treasurer') {
    if ($row['status'] == 'Pending') { ?>
        <!-- Approve and Disapprove buttons for Pending -->
        <form action="approve_requisition.php" method="POST" style="display:inline;">
            <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
            <button type="submit" class="btn btn-success btn-sm">Approve</button>
        </form>
        <form action="disapprove_requisition.php" method="POST" style="display:inline;">
            <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
            <input type="text" name="comment" placeholder="Reason for disapproval" required>
            <button type="submit" class="btn btn-danger btn-sm">Disapprove</button>
        </form>
    <?php } elseif ($row['status'] == 'Secretary Review') { ?>
        <!-- Treasurer gets options again after secretary disapproval -->
        <form action="approve_requisition.php" method="POST" style="display:inline;">
            <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
            <button type="submit" class="btn btn-success btn-sm">Approve</button>
        </form>
        <form action="disapprove_requisition.php" method="POST" style="display:inline;">
            <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
            <input type="text" name="comment" placeholder="Reason for disapproval" required>
            <button type="submit" class="btn btn-danger btn-sm">Disapprove</button>
        </form>
    <?php }
}
?>

                        <!-- Delete Requisition button -->
                        <form action="delete_requisition.php" method="POST" style="display:inline;">
                            <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this requisition?')">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('footer.php'); ?>
