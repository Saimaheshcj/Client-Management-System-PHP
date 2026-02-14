<?php
// Manager Upload Document Page
session_start();
require_once dirname(__DIR__) . '/config/database.php';
$page_title = 'Upload Document';
require_once dirname(__DIR__) . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = isset($_POST['client_id']) ? (int)$_POST['client_id'] : 0;
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $document_type = trim($_POST['document_type']);
    
    // Verify client belongs to manager
    $stmt = $pdo->prepare("SELECT client_id FROM clients WHERE client_id = ? AND manager_id = ?");
    $stmt->execute([$client_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        $_SESSION['error'] = "Invalid client selected.";
        header('Location: clients.php');
        exit;
    }

    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['document'];
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
        
        if (!in_array($file['type'], $allowed_types)) {
            $_SESSION['error'] = "Invalid file type. Only PDF, DOC, DOCX, JPG, and PNG are allowed.";
        } else {
            $upload_dir = dirname(__DIR__) . '/uploads/documents/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $stmt = $pdo->prepare(
                    "INSERT INTO documents (client_id, title, description, file_path, document_type, uploaded_by, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, NOW())"
                );
                $stmt->execute([
                    $client_id,
                    $title,
                    $description,
                    'uploads/documents/' . $file_name,
                    $document_type,
                    $_SESSION['user_id']
                ]);
                
                $_SESSION['success'] = "Document uploaded successfully.";
                header('Location: documents.php');
                exit;
            } else {
                $_SESSION['error'] = "Failed to upload file.";
            }
        }
    } else {
        $_SESSION['error'] = "Please select a file to upload.";
    }
}

// Get manager's clients for dropdown
$stmt = $pdo->prepare("SELECT c.client_id, u.name FROM clients c JOIN users u ON c.user_id = u.user_id WHERE c.manager_id = ? ORDER BY u.name");
$stmt->execute([$_SESSION['user_id']]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <h1>Upload Document</h1>
</div>

<div class="content-card">
    <form method="POST" enctype="multipart/form-data" class="form">
        <div class="form-group">
            <label for="client_id">Client</label>
            <select name="client_id" id="client_id" required>
                <option value="">Select a client</option>
                <?php foreach ($clients as $client): ?>
                <option value="<?php echo $client['client_id']; ?>"><?php echo htmlspecialchars($client['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="title">Document Title</label>
            <input type="text" name="title" id="title" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3"></textarea>
        </div>

        <div class="form-group">
            <label for="document_type">Document Type</label>
            <select name="document_type" id="document_type" required>
                <option value="contract">Contract</option>
                <option value="proposal">Proposal</option>
                <option value="report">Report</option>
                <option value="invoice">Invoice</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="document">Document File</label>
            <input type="file" name="document" id="document" required>
            <small>Allowed file types: PDF, DOC, DOCX, JPG, PNG</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Upload Document</button>
            <a href="documents.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?> 