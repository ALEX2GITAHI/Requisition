<?php
session_start();
require 'db.php';
include 'header.php';
include 'navbar.php';

// Ensure the user is the LCC Treasurer
if ($_SESSION['role'] != 'lcc_treasurer') {
    header("Location: index.php");
    exit;
}

// Fetch all requisitions that are approved by patrons and pending LCC Treasurer approval
$sql = "SELECT r.id, r.requisition_id, g.group_name, r.status, r.disapproval_comment 
        FROM requisitions r
        JOIN groups g ON r.group_id = g.id
        WHERE r.status = 'Patron Approved' OR r.status = 'LCC Secretary Disapproved'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

?>
<link href="assets/img/log2.png" rel="shortcut icon">
<div class="container">
    <h1 class="mb-4">LCC Treasurer Dashboard</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Requisition ID</th>
                <th>Group</th>
                <th>Status</th>
                <th>Comment</th> <!-- Added comment column -->
                <th>PDF</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['requisition_id'] ?></td>
                    <td><?= $row['group_name'] ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td>
                        <?php if ($row['status'] == 'LCC Secretary Disapproved'): ?>
                            <?= htmlspecialchars($row['disapproval_comment']) ?>
                        <?php endif; ?>
                    </td>
                    <td><a href="view_pdf.php?requisition_id=<?= $row['requisition_id'] ?>" target="_blank">View PDF</a></td>
                    <td>
                        <!-- Approve Requisition -->
                        <form action="approve_requisition.php" method="POST" style="display:inline;">
                            <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-success">Approve</button>
                        </form>

                        <!-- Reject Requisition with comment -->
                        <form action="disapprove_requisition.php" method="POST" style="display:inline;">
                            <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                            <input type="text" name="comment" placeholder="Enter reason for rejection" required>
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php
include 'footer.php';
?>
