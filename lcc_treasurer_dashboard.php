<?php
include('header.php');
include('navbar.php');
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Get current user's information (role)
$current_user_id = $_SESSION['user_id'];

// Fetch all requisition data for the LCC Treasurer
$query = "SELECT r.id, g.group_name, r.total_amount, r.status, r.disapproval_comment, u.role AS disapprover_role 
          FROM requisitions r 
          JOIN groups g ON r.group_id = g.id 
          LEFT JOIN users u ON u.id = r.disapproved_by
          WHERE r.status IN ('Patron Approved', 'Disapproved by LCC Secretary', 'Disapproved by LCC Treasurer')"; // Adjust statuses if necessary

$stmt = $conn->prepare($query);
$stmt->execute();
$requisitions = $stmt->get_result();

// Debugging: Check if any requisitions were fetched
if ($requisitions->num_rows === 0) {
    echo "<div class='alert alert-warning'>No requisitions found for the current status.</div>";
}
?>

<div class="container-fluid p-0"> <!-- Full-width container with no padding -->
    <div class="row mt-0"> <!-- Set margin-top to 0 -->
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center bg-light p-3 border-bottom">
                <!-- Admin Dashboard title with a link -->
                <h4 class="m-0">
                    <a href="lcc_treasurer_dashboard.php" class="text-decoration-none text-dark">LCC Treasurer Dashboard</a>
                </h4>
                
                <!-- Navbar links -->
                <nav>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link" href="manage_users.php">Approved Requisitions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_groups.php">Manage Groups</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="view_requisitions.php">View Requisitions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="logout.php">Logout</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="card-body mt-0"> <!-- Set margin-top to 0 -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Requisition ID</th>
                    <th>Group</th>
                    <th>Status</th>                    
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $requisitions->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['id'] ?></td> <!-- Assuming 'id' is the requisition ID -->
                        <td><?= $row['group_name'] ?></td>
                        <td><?= ucfirst($row['status']) ?></td>
                        <td>
                            <div style="display: flex; align-items: center;">
                                
                            <!-- Disapproval comment with the role of the disapprover -->                                
                                <?php if (strpos($row['status'], 'Disapproved') !== false && !empty($row['disapproval_comment'])) { ?>
                                    <div class="alert alert-warning" role="alert" style="margin-right: 10px;">
                                        <strong>Disapproval Comment (<?= htmlspecialchars($row['disapprover_role']) ?>):</strong>
                                        <?= htmlspecialchars($row['disapproval_comment']); ?>
                                    </div>
                                <?php } ?>
                                
                                <!-- Approve and Disapprove buttons for treasurer -->
                            
                                    <?php if ($row['status'] == 'Patron Approved') { ?>
                                        <form action="approve_requisition.php" method="POST" style="display:inline;">
 <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                        <form action="disapprove_requisition.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="requisition_id" value="<?= $row['id'] ?>">
                                            <input type="text" name="comment" placeholder="Reason for disapproval" required>
                                            <button type="submit" class="btn btn-danger btn-sm">Disapprove</button>
                                        </form>
                                    <?php } elseif ($row['status'] == 'Disapproved by LCC Secretary') { ?>
                                        <form action="approve_requisition.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="re quisition_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                        <form action="disapprove_requisition.php" method="POST" style="display:inline;">
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
                            </div>
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