<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['master_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['chall_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing challenge ID']);
    exit();
}

try {
    // Get challenge data
    $stmt = $pdo->prepare("
        SELECT * FROM challenges 
        WHERE chall_id = ? AND master_id = ?
    ");
    $stmt->execute([$_GET['chall_id'], $_SESSION['master_id']]);
    $challenge = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$challenge) {
        http_response_code(404);
        echo json_encode(['error' => 'Challenge not found']);
        exit();
    }

    // Get exercises data
    $stmt = $pdo->prepare("
        SELECT * FROM challenge_exercises 
        WHERE chall_id = ? 
        ORDER BY exercise_number
    ");
    $stmt->execute([$_GET['chall_id']]);
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'challenge' => $challenge,
        'exercises' => $exercises
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}