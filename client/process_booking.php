<?php
require_once "../db/pdo.php";
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to make a booking.";
    header("Location: book_training.php");
    exit();
}

if (!isset($_POST['membership_id'], $_POST['dojo_id'], $_POST['selected_dates'], $_POST['catmember_id'])) {
    $_SESSION['error'] = "Missing required booking information.";
    error_log("POST data missing: " . print_r($_POST, true));
    header("Location: book_training.php");
    exit();
}

$userId = $_SESSION['user_id'];
$membershipId = (int)$_POST['membership_id'];
$dojoId = (int)$_POST['dojo_id'];
$catmemberId = (int)$_POST['catmember_id'];
$selectedDates = json_decode($_POST['selected_dates'], true);

if (empty($selectedDates)) {
    $_SESSION['error'] = "No dates selected.";
    header("Location: book_training.php");
    exit();
}

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Start transaction
    $pdo->beginTransaction();

    // Define membership amounts
    $amounts = [
        1 => 0.00,      // Free trial
        2 => 2000.00,   // Monthly plan
        3 => 20000.00   // Yearly plan
    ];

    if (!isset($amounts[$membershipId])) {
        throw new Exception("Invalid membership ID.");
    }

    // Insert membership record
    $membershipStmt = $pdo->prepare("
        INSERT INTO membership (amount, user_id, catmember_id, dojo_id) 
        VALUES (?, ?, ?, ?)
    ");
    $membershipStmt->execute([
        $amounts[$membershipId],
        $userId,
        $catmemberId,
        $dojoId
    ]);
    $memId = $pdo->lastInsertId();

    if (!$memId) {
        throw new Exception("Failed to insert membership record.");
    }

    // Insert booking records
    $bookingStmt = $pdo->prepare("
        INSERT INTO booking (booking_date, user_id, dojo_id, membership_paid, mem_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    foreach ($selectedDates as $date) {
        $bookingStmt->execute([
            $date,
            $userId,
            $dojoId,
            1, // membership_paid = true
            $memId
        ]);
    }

    // Commit the transaction
    $pdo->commit();
    $_SESSION['success'] = "Booking confirmed successfully!";
    // Redirect back to the booking page
header("Location: booking_success.php");
exit();

} catch (PDOException $e) {
    // Rollback the transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    error_log("PDO Exception: " . $e->getMessage());
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error: " . $e->getMessage();
    error_log("General Exception: " . $e->getMessage());
}

// Redirect back to the booking page
header("Location: book_training.php");
exit();
