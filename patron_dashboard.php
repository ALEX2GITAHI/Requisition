<?php
session_start();
require 'db.php';
include 'header.php';
include 'navbar.php';

// Ensure the user is the patron
if ($_SESSION['role'] != 'patron') {
    header("Location: index.php");
    exit;
}

// Fetch requisitions for the patron's group
$group_id = $_SESSION['group_id'];
$sql = "SELECT r.id, r.requisition_id, g.group_name, r.total_amount, r.status, r.disapproval_comment
        FROM requisitions r
        JOIN groups g ON r.group_id = g.id
        WHERE r.group_id = ? AND (r.status = 'approved_by_chair' OR r.status = 'Disapproved by LCC Treasurer')";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $group_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Table for displaying requisition statuses -->
<link href="assets/img/log2.png" rel="shortcut icon">
<div class="card mt-2">
    <div class="card-header">
        <h4>Patron Dashboard</h4>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Requisition ID</th>
                    <th>Group</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>PDF</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['requisition_id'] ?></td>
                        <td><?= $row['group_name'] ?></td>
                        <td><?= number_format($row['total_amount'], 2) ?></td>
                        <td><?= ucfirst($row['status']) ?></td>
                        <td><a href="pdfs/requisition_<?= $row['requisition_id'] ?>.pdf" target="_blank">View PDF</a></td>
                        <td>
                            <?php if ($row['status'] == 'approved_by_chair') { ?>
                                <!-- Approve and Disapprove options for requisitions approved by chair -->
                                <form action="approve_requisition.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
                                <form action="disapprove_requisition.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                    <input type="text" name="comment" placeholder="Reason for disapproval" required>
                                    <button type="submit" class="btn btn-danger btn-sm">Disapprove</button>
                                </form>
                            <?php } elseif ($row['status'] == 'Disapproved by LCC Treasurer') { ?>
                                <!-- Show LCC Treasurer's disapproval comment and options to approve or reject -->
                                <div class="alert alert-warning" role="alert">
                                    <strong>Disapproval Comment from LCC Treasurer:</strong> <?= htmlspecialchars($row['disapproval_comment']); ?>
                                </div>
                                <form action="approve_requisition.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Approve Again</button>
                                </form>
                                <form action="reject_requisition.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                    <input type="text" name="comment" placeholder="Reason for disapproval" required>
                                    <button type="submit" class="btn btn-danger btn-sm">Reject Again</button>
                                </form>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include 'footer.php';
?>
