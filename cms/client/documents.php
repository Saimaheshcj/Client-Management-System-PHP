<?php
$page_title = 'My Documents';
require_once '../includes/header.php';

// Fetch client's documents
$stmt = $pdo->prepare(
    "
    SELECT d.*, u.name AS uploaded_by
    FROM documents d
    JOIN users u ON d.uploaded_by = u.user_id
    WHERE d.client_id = ?
    ORDER BY d.uploaded_at DESC
    "
);
$stmt->execute([$_SESSION['user_id']]);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <h1>My Documents</h1>
</div>

<div class="content-card">
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
                    <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" class="btn-small" download>Download</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?> 