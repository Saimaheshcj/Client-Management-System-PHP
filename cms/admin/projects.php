<?php
require_once 'includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO projects (client_id, manager_id, title, description, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['client_id'],
                    $_POST['manager_id'],
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['start_date'],
                    $_POST['end_date'],
                    $_POST['status']
                ]);
                break;
            case 'edit':
                $stmt = $pdo->prepare("UPDATE projects SET client_id = ?, manager_id = ?, title = ?, description = ?, start_date = ?, end_date = ?, status = ? WHERE project_id = ?");
                $stmt->execute([
                    $_POST['client_id'],
                    $_POST['manager_id'],
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['start_date'],
                    $_POST['end_date'],
                    $_POST['status'],
                    $_POST['project_id']
                ]);
                break;
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM projects WHERE project_id = ?");
                $stmt->execute([$_POST['project_id']]);
                break;
        }
        header("Location: projects.php");
        exit();
    }
}

// Get all projects with client and manager details
$stmt = $pdo->prepare(
    "
    SELECT p.*, cu.name AS client_name, mu.name AS manager_name
    FROM projects p
    JOIN clients c ON p.client_id = c.client_id
    JOIN users cu ON c.user_id = cu.user_id
    JOIN managers m ON p.manager_id = m.manager_id
    JOIN users mu ON m.user_id = mu.user_id
    ORDER BY p.start_date DESC
    "
);
$stmt->execute();
$projects = $stmt->fetchAll();

// Get all clients for dropdown
$stmt = $pdo->prepare(
    "
    SELECT c.client_id, u.name
    FROM clients c
    JOIN users u ON c.user_id = u.user_id
    ORDER BY u.name
    "
);
$stmt->execute();
$clients = $stmt->fetchAll();

// Get all managers for dropdown
$stmt = $pdo->prepare(
    "
    SELECT m.manager_id, u.name
    FROM managers m
    JOIN users u ON m.user_id = u.user_id
    ORDER BY u.name
    "
);
$stmt->execute();
$managers = $stmt->fetchAll();
?>

<div class="page-header">
    <h1>Manage Projects</h1>
    <button class="btn-primary" onclick="showAddProjectModal()">Add New Project</button>
</div>

<div class="content-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Project Name</th>
                <th>Client</th>
                <th>Manager</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): ?>
            <tr>
                <td><?php echo htmlspecialchars($project['title']); ?></td>
                <td><?php echo htmlspecialchars($project['client_name']); ?></td>
                <td><?php echo htmlspecialchars($project['manager_name']); ?></td>
                <td><?php echo date('M d, Y', strtotime($project['start_date'])); ?></td>
                <td><?php echo $project['end_date'] ? date('M d, Y', strtotime($project['end_date'])) : 'N/A'; ?></td>
                <td>
                    <span class="status-badge status-<?php echo $project['status']; ?>">
                        <?php echo ucfirst($project['status']); ?>
                    </span>
                </td>
                <td>
                    <button class="btn-small" onclick="showEditProjectModal(<?php echo $project['project_id']; ?>)">Edit</button>
                    <button class="btn-small btn-danger" onclick="deleteProject(<?php echo $project['project_id']; ?>)">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Project Modal -->
<div id="addProjectModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Add New Project</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="client_id">Client:</label>
                <select id="client_id" name="client_id" required>
                    <option value="">Select Client</option>
                    <?php foreach ($clients as $client): ?>
                    <option value="<?php echo $client['client_id']; ?>">
                        <?php echo htmlspecialchars($client['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="manager_id">Manager:</label>
                <select id="manager_id" name="manager_id" required>
                    <option value="">Select Manager</option>
                    <?php foreach ($managers as $manager): ?>
                    <option value="<?php echo $manager['manager_id']; ?>">
                        <?php echo htmlspecialchars($manager['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date">
            </div>
            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                    <option value="on hold">On Hold</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Add Project</button>
        </form>
    </div>
</div>

<!-- Edit Project Modal -->
<div id="editProjectModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Project</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="project_id" id="edit_project_id">
            <div class="form-group">
                <label for="edit_title">Title:</label>
                <input type="text" id="edit_title" name="title" required>
            </div>
            <div class="form-group">
                <label for="edit_client_id">Client:</label>
                <select id="edit_client_id" name="client_id" required>
                    <option value="">Select Client</option>
                    <?php foreach ($clients as $client): ?>
                    <option value="<?php echo $client['client_id']; ?>">
                        <?php echo htmlspecialchars($client['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_manager_id">Manager:</label>
                <select id="edit_manager_id" name="manager_id" required>
                    <option value="">Select Manager</option>
                    <?php foreach ($managers as $manager): ?>
                    <option value="<?php echo $manager['manager_id']; ?>">
                        <?php echo htmlspecialchars($manager['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_description">Description:</label>
                <textarea id="edit_description" name="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="edit_start_date">Start Date:</label>
                <input type="date" id="edit_start_date" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="edit_end_date">End Date:</label>
                <input type="date" id="edit_end_date" name="end_date">
            </div>
            <div class="form-group">
                <label for="edit_status">Status:</label>
                <select id="edit_status" name="status" required>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                    <option value="on hold">On Hold</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Update Project</button>
        </form>
    </div>
</div>

<script>
function showAddProjectModal() {
    document.getElementById('addProjectModal').style.display = 'block';
}

function showEditProjectModal(projectId) {
    fetch(`get_project.php?id=${projectId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_project_id').value = data.project_id;
            document.getElementById('edit_title').value = data.title;
            document.getElementById('edit_client_id').value = data.client_id;
            document.getElementById('edit_manager_id').value = data.manager_id;
            document.getElementById('edit_description').value = data.description;
            document.getElementById('edit_start_date').value = data.start_date;
            document.getElementById('edit_end_date').value = data.end_date;
            document.getElementById('edit_status').value = data.status;
            document.getElementById('editProjectModal').style.display = 'block';
        });
}

function deleteProject(projectId) {
    if (confirm('Are you sure you want to delete this project?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="project_id" value="${projectId}">
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