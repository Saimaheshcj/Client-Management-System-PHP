<?php
require_once 'includes/header.php';

// Handle file uploads
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['document'])) {
    $client_id = $_POST['client_id'];
    $file = $_FILES['document'];
    
    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/documents/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $file_name = uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Store original file name as title
        $stmt = $pdo->prepare("INSERT INTO documents (client_id, title, file_path, uploaded_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$client_id, $file['name'], $file_path, $_SESSION['user_id']]);
        header("Location: documents.php");
        exit();
    }
}

// Handle document deletion
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $stmt = $pdo->prepare("SELECT file_path FROM documents WHERE document_id = ?");
    $stmt->execute([$_POST['document_id']]);
    $document = $stmt->fetch();
    
    if ($document && file_exists($document['file_path'])) {
        unlink($document['file_path']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM documents WHERE document_id = ?");
    $stmt->execute([$_POST['document_id']]);
    header("Location: documents.php");
    exit();
}

// Get all documents with client details
$documents = $pdo->query(
    "
    SELECT d.*, u.name AS client_name
    FROM documents d
    JOIN clients c ON d.client_id = c.client_id
    JOIN users u ON c.user_id = u.user_id
    ORDER BY d.uploaded_at DESC
    "
)->fetchAll(PDO::FETCH_ASSOC);

// Get all clients for the dropdown
$clients = $pdo->query(
    "
    SELECT c.client_id, u.name
    FROM clients c
    JOIN users u ON c.user_id = u.user_id
    ORDER BY u.name
    "
)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <h1>Manage Documents</h1>
    <button class="btn-primary" onclick="showUploadModal()">Upload Document</button>
</div>

<div class="content-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Document Name</th>
                <th>Client</th>
                <th>Upload Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($documents as $document): ?>
            <tr>
                <td><?php echo htmlspecialchars($document['title']); ?></td>
                <td><?php echo htmlspecialchars($document['client_name']); ?></td>
                <td><?php echo date('M d, Y H:i', strtotime($document['uploaded_at'])); ?></td>
                <td>
                    <a href="<?php echo str_replace('../../', '', $document['file_path']); ?>" class="btn-small" target="_blank">View</a>
                    <button class="btn-small btn-danger" onclick="deleteDocument(<?php echo $document['document_id']; ?>)">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Upload Document Modal -->
<div id="uploadModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Upload Document</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="client_id">Client:</label>
                <select id="client_id" name="client_id" required>
                    <option value="">Select Client</option>
                    <?php foreach ($clients as $client): ?>
                    <option value="<?php echo $client['client_id']; ?>"><?php echo htmlspecialchars($client['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="document">Document:</label>
                <input type="file" id="document" name="document" required>
            </div>
            <button type="submit" class="btn-primary">Upload Document</button>
        </form>
    </div>
</div>

<script>
function showUploadModal() {
    document.getElementById('uploadModal').style.display = 'block';
}

function deleteDocument(documentId) {
    if (confirm('Are you sure you want to delete this document?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="document_id" value="${documentId}">
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