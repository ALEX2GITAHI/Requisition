<?php
include('header.php');
include('db.php');
?>

<style>
    /* Set canvas size for the pie chart (Requisitions Chart) */
    #requisitionsChart {
        max-width: 100%; /* Make sure it is responsive */
        width: 345px;    /* Reduced width for pie chart */
        height: 300px;   /* Adjust height for pie chart */
        margin: 0 auto;  /* Center the canvas */
    }

    /* Set canvas size for the bar chart (Groups Chart) */
    #groupsChart {
        max-width: 100%; /* Make sure it is responsive */
        width: 650px;    /* Slightly reduced width for bar chart */
        height: 700px;   /* Adjust height for bar chart */
        margin: 0 auto;  /* Center the canvas */
    }
</style>

<link href="assets/img/log2.png" rel="shortcut icon">
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
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">System Overview</div>
                <div class="card-body">
                    <!-- Metrics Cards -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white mb-3 shadow">
                                <div class="card-body">
                                    <h6 class="card-title">Total Users</h6>
                                    <p class="card-text">
                                        <?php
                                        // Fetch total users from the database
                                        $result = $conn->query("SELECT COUNT(*) AS total_users FROM users");
                                        $row = $result->fetch_assoc();
                                        echo $row['total_users'];
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white mb-3 shadow">
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
                            <div class="card bg-info text-white mb-3 shadow">
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

                    <!-- Charts Section -->
                    <div class="row mt-1.5">
                        <div class="col-md-6">
                            <canvas id="requisitionsChart"></canvas>
                        </div>
                        <div class="col-md-6">
                            <canvas id="groupsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Data for Requisitions Status
const requisitionsData = {
    labels: ['Pending', 'Approved', 'Disapproved'],
    datasets: [{
        label: 'Requisitions Status',
        data: [<?php
            // Count of requisitions based on their status
            $pending = $conn->query("SELECT COUNT(*) AS count FROM requisitions WHERE status = 'Pending'")->fetch_assoc()['count'];
            $approved = $conn->query("SELECT COUNT(*) AS count FROM requisitions WHERE status LIKE '%Approved%'")->fetch_assoc()['count'];
            $disapproved = $conn->query("SELECT COUNT(*) AS count FROM requisitions WHERE status LIKE '%Disapproved%'")->fetch_assoc()['count']; // Updated to include all disapproved statuses
            echo "$pending, $approved, $disapproved";
        ?>],
        backgroundColor: ['#f39c12', '#2ecc71', '#e74c3c']
    }]
};

    // Data for Groups Chart
    const groupsData = {
        labels: [<?php
            $groupNames = $conn->query("SELECT group_name FROM groups");
            $labels = [];
            while ($row = $groupNames->fetch_assoc()) {
                $labels[] = "'".$row['group_name']."'";
            }
            echo implode(', ', $labels);
        ?>],
        datasets: [{
            label: 'Groups Account Balance',
            data: [<?php
                $groupBalances = $conn->query("SELECT total_account_balance FROM groups");
                $balances = [];
                while ($row = $groupBalances->fetch_assoc()) {
                    $balances[] = $row['total_account_balance'];
                }
                echo implode(', ', $balances);
            ?>],
            backgroundColor: ['#3498db', '#1abc9c', '#9b59b6']
        }]
    };

    // Requisitions Chart (Pie chart)
    const requisitionsChart = new Chart(document.getElementById('requisitionsChart'), {
        type: 'pie',
        data: requisitionsData
    });

    // Groups Chart (Bar chart)
    const groupsChart = new Chart(document.getElementById('groupsChart'), {
        type: 'bar',
        data: groupsData
    });
</script>
<?php include('footer.php'); ?>