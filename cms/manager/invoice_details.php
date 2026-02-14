<?php
// Invoice Details for Manager
session_start();
require_once dirname(__DIR__) . '/config/database.php';
$page_title = 'Invoice Details';
require_once dirname(__DIR__) . '/includes/header.php';

// Validate invoice ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'No invoice ID provided.';
    header('Location: invoices.php');
    exit;
}
$invoice_id = intval($_GET['id']);

// Fetch invoice details
$stmt = $pdo->prepare(
    "
    SELECT i.*, u.name AS client_name, p.title AS project_title
    FROM invoices i
    JOIN clients c ON i.client_id = c.client_id
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN projects p ON i.project_id = p.project_id
    WHERE i.invoice_id = ? AND c.manager_id = ?
    "
);
$stmt->execute([$invoice_id, $_SESSION['user_id']]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    $_SESSION['error'] = 'Invoice not found.';
    header('Location: invoices.php');
    exit;
}
?>

<div class="page-header">
    <h1>Invoice Details</h1>
    <div class="header-actions">
        <a href="invoices.php" class="btn-secondary">Back to Invoices</a>
    </div>
</div>

<div class="content-card">
    <h2>Invoice Information</h2>
    <div class="details-grid">
        <div class="detail-item"><label>Invoice Number:</label><span><?php echo htmlspecialchars($invoice['invoice_number']); ?></span></div>
        <div class="detail-item"><label>Client:</label><span><?php echo htmlspecialchars($invoice['client_name']); ?></span></div>
        <div class="detail-item"><label>Project:</label><span><?php echo htmlspecialchars($invoice['project_title'] ?? 'General Invoice'); ?></span></div>
        <div class="detail-item"><label>Amount:</label><span>$<?php echo number_format($invoice['amount'], 2); ?></span></div>
        <div class="detail-item"><label>Status:</label><span><?php echo ucfirst($invoice['status']); ?></span></div>
        <div class="detail-item"><label>Due Date:</label><span><?php echo date('M j, Y', strtotime($invoice['due_date'])); ?></span></div>
        <div class="detail-item"><label>Created At:</label><span><?php echo date('M j, Y', strtotime($invoice['created_at'])); ?></span></div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?> 