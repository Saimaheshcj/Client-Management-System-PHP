<?php
$page_title = 'Invoice Details';
require_once '../includes/header.php';

// Validate invoice ID
if (!isset($_GET['id'])) {
    header('Location: invoices.php');
    exit;
}
$invoice_id = intval($_GET['id']);

// Fetch invoice details for this client
$stmt = $pdo->prepare(
    "
    SELECT i.*, p.title AS project_title
    FROM invoices i
    LEFT JOIN projects p ON i.project_id = p.project_id
    WHERE i.invoice_id = ? AND i.client_id = ?
    "
);
$stmt->execute([$invoice_id, $_SESSION['user_id']]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
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
        <div class="detail-item"><label>Project:</label><span><?php echo htmlspecialchars($invoice['project_title'] ?? 'General Invoice'); ?></span></div>
        <div class="detail-item"><label>Amount:</label><span>$<?php echo number_format($invoice['amount'], 2); ?></span></div>
        <div class="detail-item"><label>Status:</label><span><?php echo ucfirst($invoice['status']); ?></span></div>
        <div class="detail-item"><label>Due Date:</label><span><?php echo date('M d, Y', strtotime($invoice['due_date'])); ?></span></div>
        <div class="detail-item"><label>Created At:</label><span><?php echo date('M d, Y', strtotime($invoice['created_at'])); ?></span></div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 