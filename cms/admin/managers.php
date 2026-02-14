<?php
require_once 'includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO managers (name, username, password, contact) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_POST['name'], $_POST['username'], $_POST['password'], $_POST['contact']]);
                break;
            case 'edit':
                $stmt = $pdo->prepare("UPDATE managers SET name = ?, username = ?, contact = ? WHERE manager_id = ?");
                $stmt->execute([$_POST['name'], $_POST['username'], $_POST['contact'], $_POST['manager_id']]);
                break;
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM managers WHERE manager_id = ?");
                $stmt->execute([$_POST['manager_id']]);
                break;
        }
        header("Location: managers.php");
        exit();
    }
}

// Get all managers with their user information
$stmt = $pdo->prepare("
    SELECT m.*, u.name, u.email, u.user_id
    FROM managers m
    JOIN users u ON m.user_id = u.user_id
    ORDER BY u.name
");
$stmt->execute();
$managers = $stmt->fetchAll();
?>

<div class="page-header">
    <h1>Managers</h1>
    <a href="add_manager.php" class="btn-primary">Add New Manager</a>
</div>

<div class="content-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($managers as $manager): ?>
            <tr>
                <td><?php echo htmlspecialchars($manager['name']); ?></td>
                <td><?php echo htmlspecialchars($manager['email']); ?></td>
                <td><?php echo htmlspecialchars($manager['department']); ?></td>
                <td>
                    <a href="edit_manager.php?id=<?php echo $manager['manager_id']; ?>" class="btn-small">Edit</a>
                    <a href="manager_details.php?id=<?php echo $manager['manager_id']; ?>" class="btn-small">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Manager Modal -->
<div id="addManagerModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Add New Manager</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="contact">Contact:</label>
                <input type="text" id="contact" name="contact" required>
            </div>
            <button type="submit" class="btn-primary">Add Manager</button>
        </form>
    </div>
</div>

<!-- Edit Manager Modal -->
<div id="editManagerModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Manager</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="manager_id" id="edit_manager_id">
            <div class="form-group">
                <label for="edit_name">Name:</label>
                <input type="text" id="edit_name" name="name" required>
            </div>
            <div class="form-group">
                <label for="edit_username">Username:</label>
                <input type="text" id="edit_username" name="username" required>
            </div>
            <div class="form-group">
                <label for="edit_contact">Contact:</label>
                <input type="text" id="edit_contact" name="contact" required>
            </div>
            <button type="submit" class="btn-primary">Update Manager</button>
        </form>
    </div>
</div>

<script>
function showAddManagerModal() {
    document.getElementById('addManagerModal').style.display = 'block';
}

function showEditManagerModal(managerId) {
    // Fetch manager details and populate form
    fetch(`get_manager.php?id=${managerId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_manager_id').value = data.manager_id;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_username').value = data.username;
            document.getElementById('edit_contact').value = data.contact;
            document.getElementById('editManagerModal').style.display = 'block';
        });
}

function deleteManager(managerId) {
    if (confirm('Are you sure you want to delete this manager?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="manager_id" value="${managerId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modals when clicking the close button
document.querySelectorAll('.close').forEach(button => {
    button.onclick = function() {
        this.closest('.modal').style.display = 'none';
    }
});

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?> 