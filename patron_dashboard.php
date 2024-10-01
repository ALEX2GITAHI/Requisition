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

// Fetch requisitions for the patron's group that are approved by chairperson and pending approval by patron
$group_id = $_SESSION['group_id'];
$sql = "SELECT * FROM requisitions WHERE group_id = ? AND status = 'approved_by_chair'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $group_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<div class="container">
    <h1 class="mb-4">Patron Dashboard</h1>
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
                    <td><?= $row['status'] ?></td>
                    <td><a href="pdfs/requisition_<?= $row['requisition_id'] ?>.pdf" target="_blank">View PDF</a></td>
                    <td>
                        <?php if ($row['status'] == 'approved_by_chair') { ?>
                            <a href="approve_requisition.php?id=<?= $row['id'] ?>" class="btn btn-success">Approve</a>
                            <a href="reject_requisition.php?id=<?= $row['id'] ?>" class="btn btn-danger">Reject</a>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php
include 'footer.php';
?>
