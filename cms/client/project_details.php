<?php
$page_title = 'Project Details';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: projects.php');
    exit;
}
$project_id = intval($_GET['id']);

// Fetch project details for this client
$stmt = $pdo->prepare(
    "
    SELECT *
    FROM projects
    WHERE project_id = ? AND client_id = ?
    "
);
$stmt->execute([$project_id, $_SESSION['user_id']]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    header('Location: projects.php');
    exit;
}
?>

<div class="page-header">
    <h1>Project: <?php echo htmlspecialchars($project['title']); ?></h1>
    <div class="header-actions">
        <a href="projects.php" class="btn-secondary">Back to Projects</a>
    </div>
</div>

<div class="content-card">
    <h2>Project Information</h2>
    <div class="details-grid">
        <div class="detail-item"><label>Status:</label><span><?php echo ucfirst($project['status']); ?></span></div>
        <div class="detail-item"><label>Start Date:</label><span><?php echo date('M d, Y', strtotime($project['start_date'])); ?></span></div>
        <div class="detail-item"><label>End Date:</label><span><?php echo date('M d, Y', strtotime($project['end_date'])); ?></span></div>
        <div class="detail-item full-width">
            <label>Description:</label>
            <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
        </div>
    </div>
</div>

<?php
// Fetch project documents
$stmt = $pdo->prepare(
    "
    SELECT d.*, u.name AS uploaded_by
    FROM documents d
    JOIN users u ON d.uploaded_by = u.user_id
    WHERE d.project_id = ?
    ORDER BY d.uploaded_at DESC
    "
);
$stmt->execute([$project_id]);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-card">
    <h2>Documents</h2>
    <?php if (count($documents) > 0): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Type</th>
                <th>Uploaded By</th>
                <th>Upload Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($documents as $doc): ?>
            <tr>
                <td><?php echo htmlspecialchars($doc['title']); ?></td>
                <td><?php echo ucfirst($doc['document_type']); ?></td>
                <td><?php echo htmlspecialchars($doc['uploaded_by']); ?></td>
                <td><?php echo date('M j, Y', strtotime($doc['uploaded_at'])); ?></td>
                <td>
                    <a href="../uploads/<?php echo htmlspecialchars($doc['file_path']); ?>" class="btn-small" download>Download</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No documents for this project.</p>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?> 