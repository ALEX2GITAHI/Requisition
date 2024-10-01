<?php
session_start();
include('header.php');
require 'db.php';

// Ensure the user is logged in and has admin privileges
if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch all users for the dashboard
$user_sql = "SELECT * FROM users";
$users = $conn->query($user_sql);

// Fetch all groups for the modal
$group_sql = "SELECT id, group_name FROM groups";
$groups = $conn->query($group_sql);

// Handle form submission for adding a new user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];  // Normal password, no hashing
    $role = $_POST['role'];
    $group_id = $_POST['group_id'] ?? NULL; // Allow null for no group
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone_number = $_POST['phone_number'];

    // Insert new user into the database
    $sql = "INSERT INTO users (username, password, role, group_id, first_name, last_name, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssss', $username, $password, $role, $group_id, $first_name, $last_name, $phone_number);

    if ($stmt->execute()) {
        header("Location: manage_users.php?success=User added successfully");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<div class="container-fluid p-0"> <!-- Full-width container with no padding -->
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center bg-light p-3 border-bottom">
                <!-- Manage Users title with a link -->
                <h4 class="m-0">
                    <a href="manage_users.php" class="text-decoration-none text-dark">Manage Users</a>
                </h4>

                <!-- Navbar links -->
                <nav>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
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
                <div class="card-header bg-primary text-white">Manage Users</div>
                <div class="card-body">
                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        Add New User
                    </button>
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Password</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Phone Number</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch users from the database
                            $result = $conn->query("SELECT id, username, role, password, first_name, last_name, phone_number FROM users");

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['username']}</td>
            <td>{$row['role']}</td>
            <td>
                <div class='input-group'>
                    <input type='password' class='form-control' value='{$row['password']}' readonly>
                    <button class='btn btn-sm btn-secondary' onclick='togglePassword(this)'>View</button>
                </div>
            </td>
            <td>{$row['first_name']}</td>
            <td>{$row['last_name']}</td>
            <td>{$row['phone_number']}</td>
            <td>
                <a href='#' class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editUserModal' onclick='loadUserData({$row['id']})'>
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="treasurer">Treasurer</option>
                            <option value="secretary">Secretary</option>
                            <option value="chairperson">Chairperson</option>
                            <option value="patron">Patron</option>
                            <option value="lcc_treasurer">LCC Treasurer</option>
                            <option value="lcc_secretary">LCC Secretary</option>
                            <option value="lcc_chair">LCC Chair</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="group_id" class="form-label">Group</label>
                        <select class="form-select" id="group_id" name="group_id">
                            <option value="">None</option>
                            <?php while ($group = $groups->fetch_assoc()) { ?>
                                <option value="<?= $group['id'] ?>"><?= $group['group_name'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="editUserForm">
                    <input type="hidden" name="user_id" id="user_id" value="">
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="edit_password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone_number" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="edit_phone_number" name="phone_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="treasurer">Treasurer</option>
                            <option value="secretary">Secretary</option>
                            <option value="chairperson">Chairperson</option>
                            <option value="patron">Patron</option>
                            <option value="lcc_treasurer">LCC Treasurer</option>
                            <option value="lcc_secretary">LCC Secretary</option>
                            <option value="lcc_chair">LCC Chair</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_group_id" class="form-label">Group</label>
                        <select class="form-select" id="edit_group_id" name="group_id">
                            <option value="">None</option>
                            <?php while ($group = $groups->fetch_assoc()) { ?>
                                <option value="<?= $group['id'] ?>"><?= $group['group_name'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Function to load user data into the edit modal
function loadUserData(id) {
    fetch('get_user.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            document.getElementById('user_id').value = data.id;
            document.getElementById('edit_username').value = data.username;
            document.getElementById('edit_password').value = data.password;
            document.getElementById('edit_first_name').value = data.first_name;
            document.getElementById('edit_last_name').value = data.last_name;
            document.getElementById('edit_phone_number').value = data.phone_number;
            document.getElementById('edit_role').value = data.role;
            document.getElementById('edit_group_id').value = data.group_id;
        });
}

// Function to toggle password visibility
function togglePassword(button) {
    const passwordInput = button.previousElementSibling;
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    button.textContent = isPassword ? 'Hide' : 'View';
}
</script>

<?php include('footer.php'); ?>
