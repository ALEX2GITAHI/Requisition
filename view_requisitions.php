<?php
include('header.php');
include('db.php');
?>

<div class="container-fluid p-0"> <!-- Full-width container with no padding -->
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center bg-light p-3 border-bottom">
                <!-- View Requisitions title with a link -->
                <h4 class="m-0">
                    <a href="view_requisitions.php" class="text-decoration-none text-dark">All Requisitions</a>
                </h4>
                
                <!-- Navbar links -->
                <nav>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_users.php">Manage Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_groups.php">Manage Groups</a>
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

    <!-- Main content -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">All Requisitions</div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Group Name</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch requisitions from the database
                            $result = $conn->query("SELECT r.id, g.group_name, r.total_amount, r.status, r.requisition_pdf 
                            FROM requisitions r 
                            JOIN groups g ON r.group_id = g.id");

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                        <td>{$row['id']}</td>
                                        <td>{$row['group_name']}</td>
                                        <td>{$row['total_amount']}</td>
                                        <td>{$row['status']}</td>
                                        <td>{$row['updated_at']}</td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No requisitions found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>
