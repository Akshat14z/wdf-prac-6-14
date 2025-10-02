<?php
require_once 'config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Update session in database
$stmt = $pdo->prepare("UPDATE user_sessions SET last_activity = CURRENT_TIMESTAMP WHERE session_id = ? AND user_id = ?");
$stmt->execute([session_id(), $_SESSION['user_id']]);

echo json_encode(['success' => true, 'message' => 'Session refreshed']);
?>