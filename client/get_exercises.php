<?php
require_once "../db/pdo.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

if (!isset($_GET['challenge_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No challenge ID provided']);
    exit();
}

$challenge_id = $_GET['challenge_id'];
$user_id = $_SESSION['user_id'];

try {
    // Get challenge details
    $challenge_stmt = $pdo->prepare("SELECT * FROM challenges WHERE chall_id = :chall_id");
    $challenge_stmt->execute(['chall_id' => $challenge_id]);
    $challenge = $challenge_stmt->fetch(PDO::FETCH_ASSOC);

    // Get exercises
    $exercises_stmt = $pdo->prepare("SELECT * FROM challenge_exercises 
                                   WHERE chall_id = :chall_id 
                                   ORDER BY exercise_number");
    $exercises_stmt->execute(['chall_id' => $challenge_id]);
    $exercises = $exercises_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get tracker status
    $tracker_stmt = $pdo->prepare("SELECT status FROM tracker 
                                 WHERE chall_id = :chall_id AND user_id = :user_id");
    $tracker_stmt->execute([
        'chall_id' => $challenge_id,
        'user_id' => $user_id
    ]);
    $tracker = $tracker_stmt->fetch(PDO::FETCH_ASSOC);

    $response = [
        'challenge' => $challenge,
        'exercises' => $exercises,
        'tracker_status' => $tracker['status'] ?? 'not-started'
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}