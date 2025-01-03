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

// Check for booking success data
if (!isset($_SESSION['booking_success'])) {
    $_SESSION['error'] = "No booking data found.";
    header("Location: index.php");
    return;
}

// Fetch the success message and clear it from the session
$successMessage = $_SESSION['booking_success'];
unset($_SESSION['booking_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
        }
        .success-container {
            margin-top: 100px;
            text-align: center;
        }
        .success-container h1 {
            font-size: 2.5rem;
            color: #28a745;
        }
        .success-container p {
            font-size: 1.2rem;
            color: #6c757d;
        }
        .success-container a {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container success-container">
        <h1>Booking Successful!</h1>
        <p><?= htmlspecialchars($successMessage) ?></p>
        <a href="index.php" class="btn btn-primary mt-3">Go Back to Home</a>
        <a href="my_bookings.php" class="btn btn-secondary mt-3">View My Bookings</a>
    </div>
</body>
</html>
