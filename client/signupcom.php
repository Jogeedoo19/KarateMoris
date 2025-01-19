<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: login.php");
    return;
}

// Check if competition_id was provided
if (!isset($_POST['competition_id'])) {
    $_SESSION['error'] = "Missing competition ID";
    header("Location: viewcompetition.php");
    return;
}

$userId = $_SESSION['user_id'];
$competitionId = $_POST['competition_id'];

try {
    // Check if user has already signed up
    $checkStmt = $pdo->prepare("SELECT * FROM signup WHERE user_id = ? AND com_id = ?");
    $checkStmt->execute([$userId, $competitionId]);
    
    if ($checkStmt->rowCount() > 0) {
        $_SESSION['error'] = "You have already signed up for this competition";
        header("Location: viewcompetition.php");
        return;
    }
    
    // Create new signup with 'pending' status
    $stmt = $pdo->prepare("INSERT INTO signup (user_id, com_id, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$userId, $competitionId]);
    
    $_SESSION['success'] = "Successfully signed up for the competition. Awaiting approval.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

header("Location: viewcompetition.php");
return;