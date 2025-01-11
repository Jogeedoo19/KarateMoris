<?php
require_once "../db/pdo.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}

$challenge_id = $_POST['challenge_id'] ?? null;
$status = $_POST['status'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$challenge_id || !$status) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

try {
    // Check if entry exists
    $check_stmt = $pdo->prepare("SELECT track_id FROM tracker 
                                WHERE chall_id = :chall_id AND user_id = :user_id");
    $check_stmt->execute([
        'chall_id' => $challenge_id,
        'user_id' => $user_id
    ]);
    $exists = $check_stmt->fetch();

    if ($exists) {
        // Update existing entry
        $stmt = $pdo->prepare("UPDATE tracker 
                              SET status = :status 
                              WHERE chall_id = :chall_id AND user_id = :user_id");
    } else {
        // Create new entry
        $stmt = $pdo->prepare("INSERT INTO tracker (status, chall_id, user_id) 
                              VALUES (:status, :chall_id, :user_id)");
    }

    $result = $stmt->execute([
        'status' => $status,
        'chall_id' => $challenge_id,
        'user_id' => $user_id
    ]);

    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);

} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}