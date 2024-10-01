<?php
session_start();
require 'db.php';
include 'header.php';
include 'navbar.php';

// Ensure the user is the LCC Chairperson
if ($_SESSION['role'] != 'lcc_chair') {
    header("Location: index.php");
    exit;
}

// Fetch all approved requisitions from LCC Secretary for chairperson review
$sql = "SELECT r.id, r.requisition_id, g.group_name, r.status 
        FROM requisitions r
        JOIN groups g ON r.group_id = g.id
        WHERE r.status = 'approved_by_lcc_secretary'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

?>

<div class="container">
    <h1 class="mb-4">LCC Chairperson Dashboard</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Requisition ID</th>
                <th>Group</th>
                <th>Status</th>
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
                    <td><a href="view_pdf.php?requisition_id=<?= $row['requisition_id'] ?>" target="_blank">View PDF</a></td>
                    <td>
                        <!-- Approve Requisition -->
                        <form action="approve_requisition_chair.php" method="POST" style="display:inline;">
                            <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-success">Approve</button>
                        </form>

                        <!-- Reject Requisition with comment -->
                        <form action="reject_requisition_chair.php" method="POST" style="display:inline;">
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
