<?php
// Manager Documents Page
session_start();
require_once dirname(__DIR__) . '/config/database.php';
$page_title = 'My Documents';
require_once dirname(__DIR__) . '/includes/header.php';

// Fetch documents belonging to this manager's clients
$stmt = $pdo->prepare(
    "
    SELECT d.document_id, d.title, d.document_type,
           u.name AS client_name, emp.name AS uploaded_by, d.uploaded_at, d.file_path
    FROM documents d
    JOIN clients c ON d.client_id = c.client_id
    JOIN users u ON c.user_id = u.user_id
    JOIN users emp ON d.uploaded_by = emp.user_id
    WHERE c.manager_id = ?
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
                <th>Client</th>
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
                <td><?php echo htmlspecialchars($doc['client_name']); ?></td>
                <td><?php echo htmlspecialchars($doc['uploaded_by']); ?></td>
                <td><?php echo date('M j, Y H:i', strtotime($doc['uploaded_at'])); ?></td>
                <td>
                    <a href="<?php echo '../uploads/' . htmlspecialchars($doc['file_path']); ?>" class="btn-small" target="_blank">View</a>
                    <a href="download_document.php?id=<?php echo $doc['document_id']; ?>" class="btn-small">Download</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?> 