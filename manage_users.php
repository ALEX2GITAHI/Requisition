<?php
ob_start(); // Start output buffering

include('header.php');
require 'db.php';

// Ensure the user is logged in and has admin privileges
if ($_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Check if there's a success message
$success_message = '';
if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}

// Pagination setup
$limit = 6; // Number of results per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_sql = $search ? "WHERE username LIKE '%$search%' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%'" : '';

// Sorting functionality
$order_by = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';
$valid_columns = ['id', 'username', 'role', 'first_name', 'last_name', 'phone_number']; // Valid columns for sorting
if (!in_array($order_by, $valid_columns)) {
    $order_by = 'id';
    $order = 'ASC';
}

// Fetch users with pagination, search, and sorting
$user_sql = "SELECT users.*, groups.group_name FROM users 
             LEFT JOIN groups ON users.group_id = groups.id 
             $search_sql ORDER BY $order_by $order LIMIT $limit OFFSET $offset";
$result = $conn->query($user_sql);

// Fetch total users for pagination
$total_results = $conn->query("SELECT COUNT(*) as count FROM users $search_sql")->fetch_assoc()['count'];
$total_pages = ceil($total_results / $limit);

// Fetch all groups for the modal
$group_sql = "SELECT id, group_name FROM groups";
$groups = $conn->query($group_sql);

// Handle form submission for adding/updating a user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $role = $_POST['role'];
    $group_id = $_POST['group_id'] ?? NULL;
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password']; // Get the plain text password

    // Hash the password before storing in the database if it's not empty
    $hashed_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

    if (isset($_POST['user_id']) && !empty($_POST['user_id'])) { // Update user
        $user_id = $_POST['user_id'];

        // Update with or without password change
        $sql = "UPDATE users SET username=?, password=?, role=?, group_id=?, first_name=?, last_name=?, phone_number=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssi', $username, $hashed_password, $role, $group_id, $first_name, $last_name, $phone_number, $user_id);

        if ($stmt->execute()) {
            header("Location: manage_users.php?success=User  updated successfully");
            exit;
        } else {
            echo "Error: " . $conn->error;
        }
    } else { // Add new user
        $sql = "INSERT INTO users (username, password, role, group_id, first_name, last_name, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssss', $username, $hashed_password, $role, $group_id, $first_name, $last_name, $phone_number);

        if ($stmt->execute()) {
            header("Location: manage_users.php?success=User  added successfully");
            exit;
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>

<div class="container-fluid p-0">
    <div class="row">
        <div class="col-md-12">
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </ div>
            <?php endif; ?>
            <div class="d-flex justify-content-between align-items-center bg-light p-3 border-bottom">
                <h4 class="m-0">
                    <a href="manage_groups.php" class="text-decoration-none text-dark">Manage Users</a>
                </h4>
                <!-- Navbar links -->
                <nav>
                    <ul class="nav">                      
                        <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="manage_groups.php">Manage Groups</a></li>
                        <li class="nav-item"><a class="nav-link" href="view_requisitions.php">View Requisitions</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="row mt-9">
        <div class="col-md-12">
            <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span>Manage Users</span>
                    <div class="d-flex">
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search Groups" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal"
                        data-bs-target="#addUserModal">
                        Add New User
                    </button>

                    <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>
                        <a href="?sort=id&order=<?php echo $order_by === 'id' ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">
                            # 
                            <?php if ($order_by === 'id') { ?>
                                <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                            <?php } ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort=username&order=<?php echo $order_by === 'username' ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">
                            Username 
                            <?php if ($order_by === 'username') { ?>
                                <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                            <?php } ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort=role&order=<?php echo $order_by === 'role' ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">
                            Role 
                            <?php if ($order_by === 'role') { ?>
                                <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                            <?php } ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort=group_name&order=<?php echo $order_by === 'group_name' ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">
                            Group 
                            <?php if ($order_by === 'group_name') { ?>
                                <i class="fas fa-sort- <?= $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                            <?php } ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort=first_name&order=<?php echo $order_by === 'first_name' ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">
                            First Name 
                            <?php if ($order_by === 'first_name') { ?>
                                <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                            <?php } ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort=last_name&order=<?php echo $order_by === 'last_name' ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">
                            Last Name 
                            <?php if ($order_by === 'last_name') { ?>
                                <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                            <?php } ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort=phone_number&order=<?php echo $order_by === 'phone_number' ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC'; ?>">
                            Phone Number 
                            <?php if ($order_by === 'phone_number') { ?>
                                <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                            <?php } ?>
                        </a>
                    </th>                                
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['username']}</td>
                            <td>{$row['role']}</td>   
                            <td>{$row['group_name']}</td>         
                            <td>{$row['first_name']}</td>
                            <td>{$row['last_name']}</td>
                            <td>{$row['phone_number']}</td>            
                            <td>
                                <a href='#' class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editUserModal' onclick='loadUserData({$row['id']}) '>
                                    Edit
                                </a>
                                <a href='delete_user.php?id={$row['id']}' class='btn btn-danger btn-sm'>Delete</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No users found</td></tr>";
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
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUser ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUser ModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="treasurer">Treasurer</option>
                                <option value="secretary">Secretary</option>
                                <option value="chairperson">Chairperson</option>
                                <option value="patron">Patron</option>
                                <option value="patron">LCC Treasurer</option>
                                <option value="patron">LCC Secretary</option>
                                <option value="patron">LCC Chair</option>
                                <option value="admin">Admin</option>
                            </select>
 </div>
                        <div class="col-md-4 mb-3">
                            <label for="group_id" class="form-label">Group</label>
                            <select class="form-select" id="group_id" name="group_id">
                                <?php while ($group = $groups->fetch_assoc()): ?>
                                    <option value="<?php echo $group['id']; ?>"><?php echo $group['group_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="user_id" id="user_id">
                    <button type="submit" class="btn btn-primary">Add User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUser  ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUser  ModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_password" class="form-label">Password (Leave blank if not changing)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_phone_number" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="edit_phone_number" name="phone_number" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_role" class="form-label">Role</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="treasurer">Treasurer</option>
                                <option value="secretary">Secretary</option>
                                <option value="chairperson">Chairperson</option>
                                <option value="patron">Patron</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_group_id" class="form-label">Group</label>
                            <select class="form-select" id="edit_group_id" name="group_id">
                                <option value="">None</option>
                                <?php
                                // Reset the pointer for fetching groups again
                                $groups->data_seek(0);
                                while ($group = $groups->fetch_assoc()) { ?>
                                    <option value="<?= $group['id'] ?>"><?= $group['group_name'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function loadUserData(userId) {
        // Fetch user data using AJAX
        fetch('get_user.php?id=' + userId)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_user_id').value = data.id;
                document.getElementById('edit_username').value = data.username;
                document.getElementById('edit_first_name').value = data.first_name;
                document.getElementById('edit_last_name').value = data.last_name;
                document.getElementById('edit_phone_number').value = data.phone_number;
                document.getElementById('edit_role').value = data.role ;
                document.getElementById('edit_group_id').value = data.group_id; // Set group
            })
            .catch(error => console.error('Error fetching user data:', error));
    }

    function togglePassword(button) {
        const input = button.previousElementSibling;
        input.type = input.type === 'password' ? 'text' : 'password';
        button.textContent = button.textContent === 'View' ? 'Hide' : 'View';
    }

    // Automatic search functionality
document.getElementById('search').addEventListener('input', function() {
    const searchValue = this.value.trim();
    if (searchValue) {
        window.location.href = `?search=${searchValue}&sort=<?php echo $order_by; ?>&order=<?php echo $order; ?>`;
    } else {
        window.location.href = `?sort=<?php echo $order_by; ?>&order=<?php echo $order; ?>`;
    }
});

document.getElementById('searchBtn').addEventListener('click', function(event) {
    event.preventDefault();
    const searchValue = document.getElementById('search').value.trim();
    if (searchValue) {
        window.location.href = `?search=${searchValue}&sort=<?php echo $order_by; ?>&order=<?php echo $order; ?>`;
    } else {
        window.location.href = `?sort=<?php echo $order_by; ?>&order=<?php echo $order; ?>`;
    }
});
</script>

<?php include('footer.php'); ?>