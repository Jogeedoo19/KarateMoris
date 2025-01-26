<?php
require_once "../db/pdo.php";
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['master_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: masterlogin.php");
    return;
}

$masterId = $_SESSION['master_id'];
// Get challenge participation stats
$stmt = $pdo->query("SELECT ch.title, COUNT(t.track_id) as participants 
                     FROM challenges ch
                     LEFT JOIN tracker t ON ch.chall_id = t.chall_id
                     GROUP BY ch.chall_id, ch.title");
$challenges = [];
$participants = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $challenges[] = $row['title'];
    $participants[] = $row['participants'];
}

// Get recent bookings
$stmt = $pdo->query("SELECT DATE(b.booking_date) as date, COUNT(*) as bookings 
                     FROM booking b 
                     GROUP BY DATE(booking_date) 
                     ORDER BY booking_date DESC 
                     LIMIT 7");
$dates = [];
$bookingCounts = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $dates[] = $row['date'];
    $bookingCounts[] = $row['bookings'];
}
?>

<!DOCTYPE html>
<html>
<head>
<?php include '../files/csslib.php' ?> <!-- including libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            width: 45%;
            display: inline-block;
            margin: 20px;
        }
    </style>
</head>
<body>
    
<!-- including header and banner -->
<?php include_once '../files/nav.php' ?>
<br><br><br>
<div class="chart-container">
        <canvas id="challengeChart"></canvas>
    </div>
    <div class="chart-container">
        <canvas id="bookingChart"></canvas>
    </div>

    <script>
        // Radar Chart - Challenge Participation
        new Chart(document.getElementById('challengeChart'), {
            type: 'radar',
            data: {
                labels: <?php echo json_encode($challenges); ?>,
                datasets: [{
                    label: 'Participants',
                    data: <?php echo json_encode($participants); ?>,
                    fill: true,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgb(54, 162, 235)',
                    pointBackgroundColor: 'rgb(54, 162, 235)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(54, 162, 235)'
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Challenge Participation'
                    }
                }
            }
        });

        // Line Chart - Recent Bookings
        new Chart(document.getElementById('bookingChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Daily Bookings',
                    data: <?php echo json_encode($bookingCounts); ?>,
                    fill: false,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Recent Booking Trends'
                    }
                }
            }
        });
    </script>
    <!-- INCLUDING Footer -->
<?php include '../files/footer.php' ?>
</body>
</html>