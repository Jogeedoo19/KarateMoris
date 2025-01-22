<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

// Check if logged in as master
if (!isset($_SESSION['master_id'])) {
    $_SESSION['error'] = "Access denied. Masters only.";
    header("Location: masterlogin.php");
    exit();
}

$masterId = $_SESSION['master_id'];

// Fetch bookings for dojo(s) managed by this master
$stmt = $pdo->prepare("
    SELECT 
        b.book_id,
        b.booking_date,
        b.membership_paid,
        u.first_name AS user_first_name,
        u.last_name AS user_last_name,
        u.email AS user_email,
        d.name AS dojo_name,
        m.amount AS membership_amount,
        CASE 
            WHEN m.catmember_id = 1 THEN 'Free Trial'
            WHEN m.catmember_id = 2 THEN 'Monthly Plan'
            WHEN m.catmember_id = 3 THEN 'Yearly Plan'
        END AS plan_type
    FROM booking b
    JOIN user u ON b.user_id = u.user_id
    JOIN dojo d ON b.dojo_id = d.dojo_id
    JOIN membership m ON b.mem_id = m.mem_id
    WHERE d.master_id = ?
    ORDER BY b.booking_date DESC
");
$stmt->execute([$masterId]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bookings</title>
    <?php include '../files/csslib.php'; ?>
    <style>
        .booking-card {
            transition: transform 0.2s;
        }
        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .date-header {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include '../files/nav.php'; ?>
<br><br><br><br>
    <main class="container mt-5">
        <h2 class="mb-4">Training Bookings</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlentities($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlentities($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php
        // Group bookings by date
        $groupedBookings = [];
        foreach ($bookings as $booking) {
            $date = $booking['booking_date'];
            if (!isset($groupedBookings[$date])) {
                $groupedBookings[$date] = [];
            }
            $groupedBookings[$date][] = $booking;
        }

        // Display bookings grouped by date
        foreach ($groupedBookings as $date => $dayBookings):
            $dateObj = new DateTime($date);
        ?>
            <div class="date-header">
                <h4><?= $dateObj->format('l, F j, Y') ?></h4>
                <small class="text-muted"><?= count($dayBookings) ?> booking(s)</small>
            </div>

            <div class="row g-4 mb-5">
                <?php foreach ($dayBookings as $booking): ?>
                    <div class="col-md-4">
                        <div class="card booking-card">
                            <?php if ($booking['membership_paid']): ?>
                                <div class="status-badge">
                                    <span class="badge bg-success">Paid</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlentities($booking['user_first_name'] . ' ' . $booking['user_last_name']) ?></h5>
                                <p class="card-text">
                                    <strong>Dojo:</strong> <?= htmlentities($booking['dojo_name']) ?><br>
                                    <strong>Plan:</strong> <?= htmlentities($booking['plan_type']) ?><br>
                                    <strong>Amount:</strong> Rs. <?= number_format($booking['membership_amount'], 2) ?><br>
                                    <strong>Email:</strong> <?= htmlentities($booking['user_email']) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <?php if (empty($bookings)): ?>
            <div class="alert alert-info">
                No bookings found for your dojo(s).
            </div>
        <?php endif; ?>
    </main>

    <?php include '../files/footer.php'; ?>
</body>
</html>