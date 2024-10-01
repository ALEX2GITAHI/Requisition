<?php
include('header.php');
include('db.php');
?>

<div class="container-fluid p-0"> <!-- Full-width container with no padding -->
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center bg-light p-3 border-bottom">
                <!-- Admin Dashboard title with a link -->
                <h4 class="m-0">
                    <a href="admin_dashboard.php" class="text-decoration-none text-dark">Admin Dashboard</a>
                </h4>
                
                <!-- Navbar links -->
                <nav>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link" href="manage_users.php">Manage Users</a>
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

    <!-- Main content -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">System Overview</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">Total Users</h6>
                                    <p class="card-text">
                                        <?php
                                        // Fetch total users from database
                                        $result = $conn->query("SELECT COUNT(*) AS total_users FROM users");
                                        $row = $result->fetch_assoc();
                                        echo $row['total_users'];
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">Total Groups</h6>
                                    <p class="card-text">
                                        <?php
                                        // Fetch total groups from database
                                        $result = $conn->query("SELECT COUNT(*) AS total_groups FROM groups");
                                        $row = $result->fetch_assoc();
                                        echo $row['total_groups'];
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">Pending Requisitions</h6>
                                    <p class="card-text">
                                        <?php
                                        // Fetch pending requisitions from database
                                        $result = $conn->query("SELECT COUNT(*) AS pending_requisitions FROM requisitions WHERE status = 'Pending'");
                                        $row = $result->fetch_assoc();
                                        echo $row['pending_requisitions'];
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Requisitions Status Table -->
                    <h5 class="mt-4">Requisitions Status</h5>
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
                            // Fetch requisitions and display here
                            $result = $conn->query("SELECT r.id, g.group_name, r.total_amount, r.status, r.requisition_pdf, r.updated_at 
                        FROM requisitions r 
                        JOIN groups g ON r.group_id = g.id");

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                        <td>{$row['id']}</td>
                                        <td>{$row['group_name']}</td>
                                        <td>{$row['total_amount']}</td>
                                        <td>{$row['status']}</td>
                                        <td>" . (isset($row['updated_at']) ? $row['updated_at'] : 'N/A') . "</td>
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
