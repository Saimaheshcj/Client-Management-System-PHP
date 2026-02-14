<?php
// Initialize session and database connection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>CMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="logo">
                <h1>CMS</h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="clients.php">Clients</a></li>
                    <li><a href="projects.php">Projects</a></li>
                    <li><a href="documents.php">Documents</a></li>
                    <li><a href="invoices.php">Invoices</a></li>
                </ul>
            </nav>
            <div class="user-menu">
                <span class="user-name"><?php echo htmlspecialchars(isset($_SESSION['username']) ? $_SESSION['username'] : ''); ?></span>
                <a href="../logout.php" class="logout-btn">Logout</a>
            </div>
        </header>
        <main class="main-content"> 