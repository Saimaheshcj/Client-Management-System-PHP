<?php
require_once 'includes/header.php';

// Check if client ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No client ID provided.";
    header("Location: clients.php");
    exit();
}

$client_id = $_GET['id'];

// Get client and user information
$stmt = $pdo->prepare("
    SELECT c.*, u.name, u.email, u.user_id, m.manager_id, mu.name as manager_name
    FROM clients c
    JOIN users u ON c.user_id = u.user_id
    JOIN managers m ON c.manager_id = m.manager_id
    JOIN users mu ON m.user_id = mu.user_id
    WHERE c.client_id = ?
");
$stmt->execute([$client_id]);
$client = $stmt->fetch();

if (!$client) {
    $_SESSION['error'] = "Client not found.";
    header("Location: clients.php");
    exit();
}

// Get client's projects
$stmt = $pdo->prepare("
    SELECT p.*, m.manager_id, mu.name as manager_name
    FROM projects p
    JOIN managers m ON p.manager_id = m.manager_id
    JOIN users mu ON m.user_id = mu.user_id
    WHERE p.client_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$client_id]);
$projects = $stmt->fetchAll();

// Get client's documents
$stmt = $pdo->prepare("
    SELECT d.*, u.name as uploaded_by_name
    FROM documents d
    JOIN users u ON d.uploaded_by = u.user_id
    WHERE d.client_id = ?
    ORDER BY d.uploaded_at DESC
");
$stmt->execute([$client_id]);
$documents = $stmt->fetchAll();

// Get client's invoices
$stmt = $pdo->prepare("
    SELECT i.*, p.title as project_title
    FROM invoices i
    LEFT JOIN projects p ON i.project_id = p.project_id
    WHERE i.client_id = ?
    ORDER BY i.created_at DESC
");
$stmt->execute([$client_id]);
$invoices = $stmt->fetchAll();
?>

<div class="page-header">
    <h1>Client Details</h1>
    <div class="header-actions">
        <a href="edit_client.php?id=<?php echo $client_id; ?>" class="btn-primary">Edit Client</a>
        <a href="clients.php" class="btn-secondary">Back to Clients</a>
    </div>
</div>

<div class="content-card">
    <h2>Client Information</h2>
    <div class="details-grid">
        <div class="detail-item">
            <label>Name:</label>
            <span><?php echo htmlspecialchars($client['name']); ?></span>
        </div>
        <div class="detail-item">
            <label>Email:</label>
            <span><?php echo htmlspecialchars($client['email']); ?></span>
        </div>
        <div class="detail-item">
            <label>Company:</label>
            <span><?php echo htmlspecialchars($client['company']); ?></span>
        </div>
        <div class="detail-item">
            <label>Phone:</label>
            <span><?php echo htmlspecialchars($client['phone']); ?></span>
        </div>
        <div class="detail-item">
            <label>Address:</label>
            <span><?php echo nl2br(htmlspecialchars($client['address'])); ?></span>
        </div>
        <div class="detail-item">
            <label>Assigned Manager:</label>
            <span>
                <a href="manager_details.php?id=<?php echo $client['manager_id']; ?>">
                    <?php echo htmlspecialchars($client['manager_name']); ?>
                </a>
            </span>
        </div>
        <div class="detail-item">
            <label>Created At:</label>
            <span><?php echo date('F j, Y', strtotime($client['created_at'])); ?></span>
        </div>
    </div>
</div>

<div class="content-card">
    <h2>Projects</h2>
    <?php if (count($projects) > 0): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Manager</th>
                <th>Status</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): ?>
            <tr>
                <td><?php echo htmlspecialchars($project['title']); ?></td>
                <td>
                    <a href="manager_details.php?id=<?php echo $project['manager_id']; ?>">
                        <?php echo htmlspecialchars($project['manager_name']); ?>
                    </a>
                </td>
                <td><?php echo ucfirst($project['status']); ?></td>
                <td><?php echo date('M j, Y', strtotime($project['start_date'])); ?></td>
                <td><?php echo $project['end_date'] ? date('M j, Y', strtotime($project['end_date'])) : 'Not set'; ?></td>
                <td>
                    <a href="project_details.php?id=<?php echo $project['project_id']; ?>" class="btn-small">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No projects for this client.</p>
    <?php endif; ?>
</div>

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
            <?php foreach ($documents as $document): ?>
            <tr>
                <td><?php echo htmlspecialchars($document['title']); ?></td>
                <td><?php echo ucfirst($document['document_type']); ?></td>
                <td><?php echo htmlspecialchars($document['uploaded_by_name']); ?></td>
                <td><?php echo date('M j, Y', strtotime($document['uploaded_at'])); ?></td>
                <td>
                    <a href="<?php echo htmlspecialchars($document['file_path']); ?>" class="btn-small" target="_blank">View</a>
                    <a href="download_document.php?id=<?php echo $document['document_id']; ?>" class="btn-small">Download</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No documents for this client.</p>
    <?php endif; ?>
</div>

<div class="content-card">
    <h2>Invoices</h2>
    <?php if (count($invoices) > 0): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Invoice Number</th>
                <th>Project</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoices as $invoice): ?>
            <tr>
                <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                <td>
                    <?php if ($invoice['project_title']): ?>
                    <a href="project_details.php?id=<?php echo $invoice['project_id']; ?>">
                        <?php echo htmlspecialchars($invoice['project_title']); ?>
                    </a>
                    <?php else: ?>
                    General Invoice
                    <?php endif; ?>
                </td>
                <td>$<?php echo number_format($invoice['amount'], 2); ?></td>
                <td><?php echo ucfirst($invoice['status']); ?></td>
                <td><?php echo date('M j, Y', strtotime($invoice['due_date'])); ?></td>
                <td>
                    <a href="invoice_details.php?id=<?php echo $invoice['invoice_id']; ?>" class="btn-small">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No invoices for this client.</p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 