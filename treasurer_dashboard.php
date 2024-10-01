<?php
session_start();
include('header.php');
include('navbar.php');
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch group and user info from the database
$user_id = $_SESSION['user_id'];
$query = "SELECT g.group_name, u.role, g.total_account_balance
          FROM users u
          JOIN groups g ON u.group_id = g.id
          WHERE u.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user_info = $stmt->get_result()->fetch_assoc();

if ($user_info) {
    $group_name = $user_info['group_name'];
    $role = $user_info['role'];
    $group_balance = $user_info['total_account_balance'];
} else {
    echo "User or group information not found.";
}

// Handle add, edit, delete logic here...
if (isset($_POST['add_item_submit'])) {
    $item_name = $_POST['item_name'];
    $item_cost = $_POST['item_cost'];
    $item_quantity = $_POST['item_quantity'];
    $total_cost = $item_cost * $item_quantity;

    $_SESSION['requisition_items'][] = [
        'item_name' => $item_name,
        'item_cost' => $item_cost,
        'item_quantity' => $item_quantity,
        'total_cost' => $total_cost,
    ];
}

if (isset($_POST['edit_item_submit'])) {
    $edit_index = $_SESSION['edit_index'];
    if (isset($_SESSION['requisition_items'][$edit_index])) {
        $_SESSION['requisition_items'][$edit_index]['item_name'] = $_POST['edit_item_name'];
        $_SESSION['requisition_items'][$edit_index]['item_cost'] = $_POST['edit_item_cost'];
        $_SESSION['requisition_items'][$edit_index]['item_quantity'] = $_POST['edit_item_quantity'];
        $_SESSION['requisition_items'][$edit_index]['total_cost'] = $_POST['edit_item_cost'] * $_POST['edit_item_quantity'];

        unset($_SESSION['edit_index']); // Clear the edit index after saving
    }
}

if (isset($_POST['delete_item'])) {
    $item_index = $_POST['item_index'];
    unset($_SESSION['requisition_items'][$item_index]);
    $_SESSION['requisition_items'] = array_values($_SESSION['requisition_items']); // Reindex the array
}

// Save requisition, generate PDF, and other logic goes here...
?>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light mt-9">
        <div class="container-fluid">
            <a class="navbar-brand" href="main_dashboard.php">Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="treasurer_dashboard.php">Create Requisition</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Table for displaying requisition items -->
    <div class="card mt-2">
        <div class="card-header">
            <h4><strong>Account Balance: KSh <?= number_format($group_balance, 2) ?></strong>
            <button class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addItemModal">Add Item</button>
        </div>
        <div class="card-body ">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item Name</th>
                        <th>Cost</th>
                        <th>Quantity</th>
                        <th>Total Cost</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($_SESSION['requisition_items'])) {
                        $total_cost = 0;
                        foreach ($_SESSION['requisition_items'] as $index => $item) {
                            $total_cost += $item['total_cost'];
                    ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= $item['item_name'] ?></td>
                        <td><?= number_format($item['item_cost'], 2) ?></td>
                        <td><?= $item['item_quantity'] ?></td>
                        <td><?= number_format($item['total_cost'], 2) ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editItemModal" 
                                    onclick="setEditForm(<?= $index ?>, '<?= $item['item_name'] ?>', <?= $item['item_cost'] ?>, <?= $item['item_quantity'] ?>)">
                                Edit
                            </button>
                            <form action="treasurer_dashboard.php" method="POST" style="display:inline;">
                                <input type="hidden" name="item_index" value="<?= $index ?>">
                                <button type="submit" name="delete_item" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total Requisition Cost:</strong></td>
                        <td><strong><?= number_format($total_cost, 2) ?></strong></td>
                    </tr>
                    <?php } else { ?>
                    <tr>
                        <td colspan="6" class="text-center">No items added</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <form action="save_requisition.php" method="POST">
    <button type="submit" name="submit_requisition" class="btn btn-success float-end">Save Requisition</button>
</form>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">Add New Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="treasurer_dashboard.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="item_name" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="item_name" name="item_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="item_cost" class="form-label">Item Cost</label>
                            <input type="number" class="form-control" id="item_cost" name="item_cost" required>
                        </div>
                        <div class="mb-3">
                            <label for="item_quantity" class="form-label">Item Quantity</label>
                            <input type="number" class="form-control" id="item_quantity" name="item_quantity" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_item_submit" class="btn btn-primary">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editItemModalLabel">Edit Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="treasurer_dashboard.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_item_name" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="edit_item_name" name="edit_item_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_item_cost" class="form-label">Item Cost</label>
                            <input type="number" class="form-control" id="edit_item_cost" name="edit_item_cost" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_item_quantity" class="form-label">Item Quantity</label>
                            <input type="number" class="form-control" id="edit_item_quantity" name="edit_item_quantity" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_item_submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    // Update time for Nairobi timezone
    function updateTime() {
        const now = new Date().toLocaleString('en-US', { timeZone: 'Africa/Nairobi' });
        document.getElementById('datetime').textContent = now;
    }
    setInterval(updateTime, 1000);

    // Set values in the edit modal
    function setEditForm(index, name, cost, quantity) {
        document.getElementById('edit_item_name').value = name;
        document.getElementById('edit_item_cost').value = cost;
        document.getElementById('edit_item_quantity').value = quantity;

        // Set edit index in hidden form field
        const hiddenIndexField = document.createElement('input');
        hiddenIndexField.setAttribute('type', 'hidden');
        hiddenIndexField.setAttribute('name', 'edit_index');
        hiddenIndexField.setAttribute('value', index);
        document.querySelector('#editItemModal form').appendChild(hiddenIndexField);
    }
</script>

<?php include('footer.php'); ?>
