<?php
require_once '../includes/header.php';

// Get project ID from URL
$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify project belongs to manager
$stmt = $pdo->prepare("
    SELECT p.*, c.name as client_name
    FROM projects p
    JOIN clients c ON p.client_id = c.client_id
    WHERE p.project_id = ? AND p.manager_id = ?
");
$stmt->execute([$project_id, $_SESSION['user_id']]);
$project = $stmt->fetch();

if (!$project) {
    header('Location: projects.php');
    exit;
}

// Get project tasks
$stmt = $pdo->prepare("
    SELECT t.*, u.name as assigned_to_name
    FROM tasks t
    LEFT JOIN users u ON t.assigned_to = u.user_id
    WHERE t.project_id = ?
    ORDER BY t.priority DESC, t.due_date ASC
");
$stmt->execute([$project_id]);
$tasks = $stmt->fetchAll();

// Get project team members
$stmt = $pdo->prepare("
    SELECT u.*, pt.role
    FROM project_team pt
    JOIN users u ON pt.user_id = u.user_id
    WHERE pt.project_id = ?
    ORDER BY pt.role
");
$stmt->execute([$project_id]);
$team_members = $stmt->fetchAll();

// Get project documents
$stmt = $pdo->prepare("
    SELECT d.*, u.name as uploaded_by
    FROM documents d
    JOIN users u ON d.uploaded_by = u.user_id
    WHERE d.project_id = ?
    ORDER BY d.uploaded_at DESC
");
$stmt->execute([$project_id]);
$documents = $stmt->fetchAll();
?>

<div class="page-header">
    <h1>Project: <?php echo htmlspecialchars($project['title']); ?></h1>
    <div class="header-actions">
        <a href="edit_project.php?id=<?php echo $project_id; ?>" class="btn-primary">Edit Project</a>
        <a href="add_task.php?project_id=<?php echo $project_id; ?>" class="btn-secondary">Add Task</a>
    </div>
</div>

<div class="content-card">
    <h2>Project Information</h2>
    <div class="info-grid">
        <div class="info-item">
            <label>Client:</label>
            <span><?php echo htmlspecialchars($project['client_name']); ?></span>
        </div>
        <div class="info-item">
            <label>Status:</label>
            <span class="status-badge status-<?php echo $project['status']; ?>">
                <?php echo ucfirst($project['status']); ?>
            </span>
        </div>
        <div class="info-item">
            <label>Start Date:</label>
            <span><?php echo date('M d, Y', strtotime($project['start_date'])); ?></span>
        </div>
        <div class="info-item">
            <label>End Date:</label>
            <span><?php echo date('M d, Y', strtotime($project['end_date'])); ?></span>
        </div>
    </div>
    <div class="info-item full-width">
        <label>Description:</label>
        <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
    </div>
</div>

<div class="content-card">
    <h2>Tasks</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Assigned To</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task): ?>
            <tr>
                <td><?php echo htmlspecialchars($task['title']); ?></td>
                <td><?php echo htmlspecialchars($task['assigned_to_name'] ?? 'Unassigned'); ?></td>
                <td>
                    <span class="priority-badge priority-<?php echo $task['priority']; ?>">
                        <?php echo ucfirst($task['priority']); ?>
                    </span>
                </td>
                <td>
                    <span class="status-badge status-<?php echo $task['status']; ?>">
                        <?php echo ucfirst($task['status']); ?>
                    </span>
                </td>
                <td><?php echo date('M d, Y', strtotime($task['due_date'])); ?></td>
                <td>
                    <a href="edit_task.php?id=<?php echo $task['task_id']; ?>" class="btn-small">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="content-card">
    <h2>Team Members</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($team_members as $member): ?>
            <tr>
                <td><?php echo htmlspecialchars($member['name']); ?></td>
                <td><?php echo htmlspecialchars($member['email']); ?></td>
                <td><?php echo htmlspecialchars($member['role']); ?></td>
                <td>
                    <a href="edit_team_member.php?project_id=<?php echo $project_id; ?>&user_id=<?php echo $member['user_id']; ?>" class="btn-small">Edit Role</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="table-actions">
        <a href="add_team_member.php?project_id=<?php echo $project_id; ?>" class="btn-secondary">Add Team Member</a>
    </div>
</div>

<div class="content-card">
    <h2>Documents</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Uploaded By</th>
                <th>Upload Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($documents as $document): ?>
            <tr>
                <td><?php echo htmlspecialchars($document['title']); ?></td>
                <td><?php echo htmlspecialchars($document['description']); ?></td>
                <td><?php echo htmlspecialchars($document['uploaded_by']); ?></td>
                <td><?php echo date('M d, Y', strtotime($document['uploaded_at'])); ?></td>
                <td>
                    <a href="../uploads/<?php echo $document['file_path']; ?>" class="btn-small" target="_blank">Download</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="table-actions">
        <a href="upload_document.php?project_id=<?php echo $project_id; ?>" class="btn-secondary">Upload Document</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 