<?php
include('header.php');
include('db.php');
include('edit_group.php');
include('add_group.php');

?>

<div class="container-fluid p-0"> <!-- Full-width container with no padding -->
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center bg-light p-3 border-bottom">
                <!-- Manage Groups title with a link -->
                <h4 class="m-0">
                    <a href="manage_groups.php" class="text-decoration-none text-dark">Manage Groups</a>
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
                <div class="card-header bg-primary text-white">Manage Groups</div>
                <div class="card-body">
                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addGroupModal">
                        Add New Group
                    </button>
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Group Name</th>
                                <th>Account Balance</th>
                                <th>Logo</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT id, group_name, total_account_balance, group_logo FROM groups");
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
                </div>
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
                        <input type="number" class="form-control" id="total_account_balance" name="total_account_balance" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="group_logo" class="form-label">Group Logo</label>
                        <input type="file" class="form-control" id="group_logo" name="group_logo" accept="image/*" required>
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
                <form method="POST" action="edit_group.php" enctype="multipart/form-data" id="editGroupForm">
                    <input type="hidden" name="group_id" id="group_id" value="">
                    <div class="mb-3">
                        <label for="edit_group_name" class="form-label">Group Name</label>
                        <input type="text" class="form-control" id="edit_group_name" name="group_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_total_account_balance" class="form-label">Account Balance</label>
                        <input type="number" class="form-control" id="edit_total_account_balance" name="total_account_balance" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_group_logo" class="form-label">Group Logo</label>
                        <input type="file" class="form-control" id="edit_group_logo" name="group_logo" accept="image/*">
                        <img id="editLogoPreview" src="" alt="Group Logo Preview" width="100" style="display: none; margin-top: 10px;">
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
            // Check if there is an error in the response
            if (data.error) {
                alert(data.error); // Show an error if group is not found or another issue arises
            } else {
                // Populate the form fields with the fetched data
                document.getElementById('group_id').value = data.id;
                document.getElementById('edit_group_name').value = data.group_name;
                document.getElementById('edit_total_account_balance').value = data.total_account_balance;
                
                // Handle logo if it exists
                if (data.group_logo) {
                    const logoPreview = document.getElementById('editLogoPreview');
                    logoPreview.src = 'data:image/jpeg;base64,' + btoa(data.group_logo); // Convert to base64
                    logoPreview.style.display = 'block'; // Show the preview
                } else {
                    document.getElementById('editLogoPreview').style.display = 'none'; // Hide if no logo
                }
            }
        })
        .catch(error => {
            console.error('Error fetching group data:', error);
            alert('Error fetching group data');
        });
}

// Preview uploaded image for logo
document.getElementById('group_logo').addEventListener('change', function (e) {
    const file = e.target.files[0];
    const reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('editLogoPreview').src = e.target.result;
        document.getElementById('editLogoPreview').style.display = 'block';
    }
    reader.readAsDataURL(file);
});
</script>

<?php
include('footer.php');
?>