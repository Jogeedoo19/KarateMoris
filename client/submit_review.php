<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in']);
    exit();
}

if (!isset($_POST['master_id']) || !isset($_POST['rating'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit();
}

$userId = $_SESSION['user_id'];
$masterId = $_POST['master_id'];
$rating = $_POST['rating'];

// Validate rating
if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating']);
    exit();
}

try {
    // Check if user has already reviewed this master
    $stmt = $pdo->prepare("SELECT review_id FROM review WHERE user_id = ? AND master_id = ?");
    $stmt->execute([$userId, $masterId]);
    $existingReview = $stmt->fetch();

    if ($existingReview) {
        // Update existing review
        $stmt = $pdo->prepare("UPDATE review SET rate = ? WHERE user_id = ? AND master_id = ?");
        $stmt->execute([$rating, $userId, $masterId]);
    } else {
        // Create new review
        $stmt = $pdo->prepare("INSERT INTO review (rate, user_id, master_id) VALUES (?, ?, ?)");
        $stmt->execute([$rating, $userId, $masterId]);
    }

    // Get updated average and count
    $stmt = $pdo->prepare("
        SELECT 
            AVG(CAST(rate AS DECIMAL(10,1))) as average_rating,
            COUNT(*) as review_count
        FROM review 
        WHERE master_id = ?
    ");
    $stmt->execute([$masterId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'newAverage' => (float)$result['average_rating'],
        'reviewCount' => (int)$result['review_count']
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}