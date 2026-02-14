<?php
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE client_id = ?");
    $stmt->execute([$_GET['id']]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($client) {
        header('Content-Type: application/json');
        echo json_encode($client);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Client not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Client ID not provided']);
}
?> 