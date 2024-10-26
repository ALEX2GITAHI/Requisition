<?php
include('header.php');
include('db.php');

// Pagination setup
$limit = 4; // Number of results per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_sql = $search ? "WHERE group_name LIKE '%$search%'" : '';

// Sorting functionality
$order_by = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';
$valid_columns = ['id', 'group_name', 'total_account_balance']; // Valid columns for sorting
if (!in_array($order_by, $valid_columns)) {
    $order_by = 'id';
}

// Fetching groups with pagination, search, and sorting
$result = $conn->query("SELECT id, group_name, total_account_balance, group_logo FROM groups $search_sql ORDER BY $order_by $order LIMIT $limit OFFSET $offset");
$total_results = $conn->query("SELECT COUNT(*) as count FROM groups $search_sql")->fetch_assoc()['count'];
$total_pages = ceil($total_results / $limit);
?>

<link href="assets/img/log2.png" rel="shortcut icon">
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center bg-light p-3 border-bottom">
                <h4 class="m-0">
                    <a href="manage_groups.php" class="text-decoration-none text-dark">Manage Groups</a>
                </h4>

                <nav>
                    <ul class="nav">
                        <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="manage_users.php">Manage Users</a></li>
                        <li class="nav-item"><a class="nav-link" href="view_requisitions.php">View Requisitions</a></li>
                        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span>Manage Groups</span>
                    <div class="d-flex">
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search Groups" value="<?php echo htmlspecialchars($search); ?>">
                        
                    </div>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal"
                        data-bs-target="#addGroupModal">
                        Add New Group
                    </button>

                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th><a
                                        href="?sort=id&order=<?php echo $order === 'ASC' ? 'desc' : 'asc'; ?>&search=<?php echo htmlspecialchars($search); ?>">#
                                        <?php echo ($order_by === 'id' && $order === 'asc') ? '↑' : ''; ?><?php echo ($order_by === 'id' && $order === 'desc') ? '↓' : ''; ?></a>
                                </th>
                                <th><a
                                        href="?sort=group_name&order=<?php echo $order === 'ASC' ? 'desc' : 'asc'; ?>&search=<?php echo htmlspecialchars($search); ?>">Group
                                        Name
                                        <?php echo ($order_by === 'group_name' && $order === 'asc') ? '↑' : ''; ?><?php echo ($order_by === 'group_name' && $order === 'desc') ? '↓' : ''; ?></a>
                                </th>
                                <th><a
                                        href="?sort=total_account_balance&order=<?php echo $order === 'ASC' ? 'desc' : 'asc '; ?>&search=<?php echo htmlspecialchars($search); ?>">Account
                                        Balance
                                        <?php echo ($order_by === 'total_account_balance' && $order === 'asc') ? '↑' : ''; ?><?php echo ($order_by === 'total_account_balance' && $order === 'desc') ? '↓' : ''; ?></a>
                                </th>
                                <th>Logo</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $logo = base64_encode($row['group_logo']);
                                    echo "<tr>
                                        <td>{$row['id']}</td>
                                        <td>{$row['group_name']}</td>
                                        <td>{$row['total_account_balance']}</td>
                                        <td><img src='data:image/jpeg;base64,{$logo}' alt='Group Logo' width='50'></td>
                                        <td>
                                            <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editGroupModal' onclick='loadGroupData({$row['id']})'>Edit</button>
                                            <a href='delete_group.php?id={$row['id']}' class='btn btn-danger btn-sm'>Delete</a>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No groups found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm justify-content-end">
                                <?php if ($page > 1) { ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>&sort=<?php echo $order_by; ?>&order=<?php echo $order; ?>">Previous</a>
                                </li>
                                <?php } ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                                <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                    <a class="page-link"
                                        href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>&sort=<?php echo $order_by; ?>&order=<?php echo $order; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php } ?>

                                <?php if ($page < $total_pages) { ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>&sort=<?php echo $order_by; ?>&order=<?php echo $order; ?>">Next</a>
                                </li>
                                <?php } ?>
                            </ul>
                        </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Group Modal -->
    <div class="modal fade" id="addGroupModal" tabindex="-1" aria-labelledby="addGroupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addGroupModalLabel">Add New Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="add_group.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="group_name" class="form-label">Group Name</label>
                            <input type="text" class="form-control" id="group_name" name="group_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="total_account_balance" class="form-label">Account Balance</label>
                            <input type="number" class="form-control" id="total_account_balance"
                                name="total_account_balance" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="group_logo" class="form-label">Group Logo</label>
                            <input type="file" class="form-control" id="group_logo" name="group_logo" accept="image/*"
                                required>
                            <img id="logoPreview" src="" alt="Logo Preview" style="display:none;" width="100"
                                class="mt-2">
                        </div>
                        <button type="submit" class="btn btn-primary">Add Group</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Group Modal -->
    <div class="modal fade" id="editGroupModal" tabindex="-1" aria-labelledby="editGroupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editGroupModalLabel">Edit Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="edit_group.php" enctype="multipart/form-data" id="edit GroupForm">
                        <input type="hidden" name="group_id" id="group_id" value="">
                        <div class="mb-3">
                            <label for="edit_group_name" class="form-label">Group Name</label>
                            <input type="text" class="form-control" id="edit_group_name" name="group_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_total_account_balance" class="form-label">Account Balance</label>
                            <input type="number" class="form-control" id="edit_total_account_balance"
                                name="total_account_balance" step="0.01" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Function to load group data into the edit modal
    function loadGroupData(id) {
        fetch('get_group.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error); // Show an error if group is not found or another issue arises
                } else {
                    // Populate the form fields with the fetched data
                    document.getElementById('group_id').value = data.id;
                    document.getElementById('edit_group_name').value = data.group_name;
                    document.getElementById('edit_total_account_balance').value = data.total_account_balance;
                }
            })
            .catch(error => {
                console.error('Error fetching group data:', error);
                alert('Error fetching group data');
            });
    }

    // Automatic search functionality
    document.getElementById('search').addEventListener('input', function() {
        const searchValue = this.value.trim();
        if (searchValue) {
            window.location.href =
                `?search=${searchValue}&sort=<?php echo $order_by; ?>&order=<?php echo $order; ?>`;
        } else {
            window.location.href = `?sort=<?php echo $order_by; ?>&order=<?php echo $order; ?>`;
        }
    });

    document.getElementById('searchBtn').addEventListener('click', function(event) {
        event.preventDefault();
        const searchValue = document.getElementById('search').value.trim();
        if (searchValue) {
            window.location.href =
                `?search=${searchValue}&sort=<?php echo $order_by; ?>&order=<?php echo $order; ?>`;
        } else {
            window.location.href = `?sort=<?php echo $order_by; ?>&order=<?php echo $order; ?>`;
        }
    });
    </script>

    <?php include('footer.php'); ?>