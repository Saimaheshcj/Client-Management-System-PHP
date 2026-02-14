<?php
// Secure document download for managers
session_start();
require_once dirname(__DIR__) . '/config/database.php';

if (!isset($_GET['id'])) {
    header('Location: documents.php');
    exit;
}

$document_id = intval($_GET['id']);
$stmt = $pdo->prepare(
    "SELECT d.file_path
     FROM documents d
     JOIN clients c ON d.client_id = c.client_id
     WHERE d.document_id = ? AND c.manager_id = ?"
);
$stmt->execute([$document_id, $_SESSION['user_id']]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
    header('HTTP/1.0 404 Not Found');
    exit;
}

$file = dirname(__DIR__) . '/' . $doc['file_path'];

if (!file_exists($file)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}

// Send download headers
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file));
readfile($file);
exit; 