<?php
require_once '../includes/header.php';

// Get client ID from URL
$client_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify client belongs to manager
$stmt = $pdo->prepare("
    SELECT c.* 
    FROM clients c
    WHERE c.client_id = ? AND c.manager_id = ?
");
$stmt->execute([$client_id, $_SESSION['user_id']]);
$client = $stmt->fetch();

if (!$client) {
    header('Location: clients.php');
    exit;
}

// Get client's projects
$stmt = $pdo->prepare("
    SELECT p.*, 
           (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.project_id) as total_tasks,
           (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.project_id AND t.status = 'completed') as completed_tasks
    FROM projects p
    WHERE p.client_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$client_id]);
$projects = $stmt->fetchAll();

// Get client's documents
$stmt = $pdo->prepare("
    SELECT d.*, u.name as uploaded_by
    FROM documents d
    JOIN users u ON d.uploaded_by = u.user_id
    WHERE d.client_id = ?
    ORDER BY d.uploaded_at DESC
");
$stmt->execute([$client_id]);
$documents = $stmt->fetchAll();
?>

<div class="page-header">
    <h1>Client Details: <?php echo htmlspecialchars($client['name']); ?></h1>
</div>

<div class="content-card">
    <h2>Client Information</h2>
    <div class="info-grid">
        <div class="info-item">
            <label>Email:</label>
            <span><?php echo htmlspecialchars($client['email']); ?></span>
        </div>
        <div class="info-item">
            <label>Phone:</label>
            <span><?php echo htmlspecialchars($client['phone']); ?></span>
        </div>
        <div class="info-item">
            <label>Address:</label>
            <span><?php echo htmlspecialchars($client['address']); ?></span>
        </div>
        <div class="info-item">
            <label>Company:</label>
            <span><?php echo htmlspecialchars($client['company']); ?></span>
        </div>
    </div>
</div>

<div class="content-card">
    <h2>Projects</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Progress</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): ?>
            <tr>
                <td><?php echo htmlspecialchars($project['title']); ?></td>
                <td><?php echo htmlspecialchars($project['description']); ?></td>
                <td><?php echo htmlspecialchars($project['status']); ?></td>
                <td>
                    <?php 
                    $progress = $project['total_tasks'] > 0 
                        ? round(($project['completed_tasks'] / $project['total_tasks']) * 100) 
                        : 0;
                    echo $progress . '%';
                    ?>
                </td>
                <td><?php echo date('M d, Y', strtotime($project['start_date'])); ?></td>
                <td><?php echo date('M d, Y', strtotime($project['end_date'])); ?></td>
                <td>
                    <a href="project_details.php?id=<?php echo $project['project_id']; ?>" class="btn-small">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
</div>

<?php require_once 'includes/footer.php'; ?> 