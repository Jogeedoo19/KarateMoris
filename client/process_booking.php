<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: login.php");
    return;
}

// Validate form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $membershipId = $_POST['membership_id'] ?? null;
    $dojoId = $_POST['dojo_id'] ?? null;
    $selectedDates = $_POST['selected_dates'] ?? null;

    if (!$membershipId || !$dojoId || !$selectedDates) {
        $_SESSION['error'] = "Invalid booking data.";
        header("Location: booking.php");
        return;
    }

    // Parse selected dates into an array
    $dates = explode(',', $selectedDates);
    $successfulInserts = 0;

    try {
        $pdo->beginTransaction();

        // Insert each selected date into the database
        foreach ($dates as $date) {
            $stmt = $pdo->prepare("
                INSERT INTO booking (user_id, dojo_id, mem_id, booking_date) 
                VALUES (:user_id, :dojo_id, :membership_id, :booking_date)
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':dojo_id' => $dojoId,
                ':membership_id' => $membershipId,
                ':booking_date' => $date,
            ]);
            $successfulInserts++;
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Booking failed. Please try again.";
        header("Location: book_training.php");
        return;
    }

    // Set success message
    $_SESSION['booking_success'] = "Your booking for " . $successfulInserts . " date(s) has been confirmed.";
    header("Location: booking_success.php");
    return;
}

// Redirect to the booking page if accessed without POST data
header("Location: book_training.php");
return;
?>
