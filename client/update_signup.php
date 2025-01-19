<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

if (!isset($_SESSION['master_id']) || !isset($_POST['signup_id']) || !isset($_POST['action'])) {
    $_SESSION['error'] = "Invalid request";
    header("Location: manage_signups.php");
    return;
}

$signupId = $_POST['signup_id'];
$action = $_POST['action'];
$newStatus = ($action === 'approve') ? 'approved' : 'rejected';

// Verify this signup belongs to a competition owned by this master
$stmt = $pdo->prepare("
    SELECT competition.master_id 
    FROM signup 
    JOIN competition ON signup.com_id = competition.com_id 
    WHERE signup.s_id = ?
");
$stmt->execute([$signupId]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result['master_id'] != $_SESSION['master_id']) {
    $_SESSION['error'] = "Access denied";
    header("Location: manage_signups.php");
    return;
}

// Update signup status
$stmt = $pdo->prepare("UPDATE signup SET status = ? WHERE s_id = ?");
if ($stmt->execute([$newStatus, $signupId])) {
    $_SESSION['success'] = "Signup has been " . $newStatus;
} else {
    $_SESSION['error'] = "Error updating signup";
}

header("Location: manage_signups.php");
return;