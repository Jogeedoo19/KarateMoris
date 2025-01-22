<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

if (!isset($_SESSION['success'])) {
    header("Location: book_training.php");
    exit();
}

$success_message = $_SESSION['success'];
unset($_SESSION['success']); // Clear the success message
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Successful</title>
    <?php include '../files/csslib.php'; ?>
    <style>
        .success-container {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .checkmark {
            color: #28a745;
            font-size: 80px;
            margin-bottom: 20px;
        }
        .action-buttons {
            margin-top: 30px;
        }
        .action-buttons .btn {
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <?php include '../files/nav.php'; ?>
<br><br><br><br>
    <main class="container">
        <div class="success-container">
            <i class="bi bi-check-circle-fill checkmark"></i>
            <h2 class="mb-4">Booking Successful!</h2>
            <p class="lead mb-4"><?= htmlentities($success_message) ?></p>
            
            <div class="action-buttons">
                <a href="book_training.php" class="btn btn-outline-primary">Make Another Booking</a>
                <a href="manage_booking.php" class="btn btn-primary">View My Bookings</a>
            </div>
        </div>
    </main>

    <?php include '../files/footer.php'; ?>
</body>
</html>