<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: login.php");
    return;
}

$userId = $_SESSION['user_id'];

// Fetch Existing Bookings
$bookingStmt = $pdo->prepare("
    SELECT booking_date 
    FROM booking 
    WHERE dojo_id = ?
");
$bookingStmt->execute([$_GET['dojo_id'] ?? 1]);
$bookedDates = $bookingStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Training</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
<!-- Add this in the <head> section -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">


<link href="https://cdn.jsdelivr.net/gh/lashaNoz/Calendar@2.0.0/calendar.min.css" rel="stylesheet">
<!-- Update the head section with these imports in this specific order -->
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.10/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/gh/lashaNoz/Calendar@2.0.0/calendar.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <style>
        .pricing-item {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .pricing-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .pricing-item.selected {
            border-color: #007bff;
            background-color: #f8f9fa;
        }
        #calendarSection {
            margin-top: 30px;
            padding: 20px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .membership-info {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
        #calendar {
    background: #ffffff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-top: 20px;
}
.calendar {
    margin: 20px auto;
    width: 100%;
    max-width: 800px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}

.calendar .selected-date {
    background: #007bff !important;
    color: white !important;
}

.calendar .booked-date {
    background: #dc3545 !important;
    color: white !important;
    pointer-events: none;
}

    </style>
    <script>
    window.bookedDates = <?= json_encode($bookedDates) ?>;
</script>
</head>
<body>
    <?php include '../files/nav.php'; ?>
    
    <main class="container mt-5">
        <h2 class="text-center mb-4">Select Your Membership Plan</h2>
        
        <div class="row gy-4 mb-5">
            <!-- Free Membership -->
            <div class="col-lg-4">
                <div class="pricing-item" id="membership-1">
                    <h3>Free Trial</h3>
                    <h4><sup>Rs.</sup>0<span> / month</span></h4>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-check-circle"></i> 2 Training Sessions</li>
                        <li><i class="bi bi-check-circle"></i> Basic Access</li>
                        <li><i class="bi bi-x-circle"></i> Limited Availability</li>
                    </ul>
                    <button class="btn btn-primary w-100 membership-btn" data-membership-id="1" 
                            data-membership-name="Free Trial" data-max-sessions="2">
                        Select Free Trial
                    </button>
                </div>
            </div>

            <!-- Monthly Membership -->
            <div class="col-lg-4">
                <div class="pricing-item" id="membership-2">
                    <h3>Monthly Plan</h3>
                    <h4><sup>Rs.</sup>2,000<span> / month</span></h4>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-check-circle"></i> Unlimited Sessions</li>
                        <li><i class="bi bi-check-circle"></i> Full Access</li>
                        <li><i class="bi bi-check-circle"></i> Flexible Schedule</li>
                    </ul>
                    <button class="btn btn-primary w-100 membership-btn" data-membership-id="2" 
                            data-membership-name="Monthly Plan" data-max-sessions="unlimited">
                        Select Monthly Plan
                    </button>
                </div>
            </div>

            <!-- Yearly Membership -->
            <div class="col-lg-4">
                <div class="pricing-item" id="membership-3">
                    <h3>Yearly Plan</h3>
                    <h4><sup>Rs.</sup>20,000<span> / year</span></h4>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-check-circle"></i> Unlimited Sessions</li>
                        <li><i class="bi bi-check-circle"></i> Premium Access</li>
                        <li><i class="bi bi-check-circle"></i> Priority Booking</li>
                    </ul>
                    <button class="btn btn-primary w-100 membership-btn" data-membership-id="3" 
                            data-membership-name="Yearly Plan" data-max-sessions="unlimited">
                        Select Yearly Plan
                    </button>
                </div>
            </div>
        </div>

        <!-- Calendar Section (Initially Hidden) -->
        <div id="calendarSection" style="display: none;">
            <div class="membership-info alert alert-info">
                <span id="selectedPlanInfo"></span>
            </div>
            
            <form id="bookingForm" method="POST" action="process_booking.php">
                <input type="hidden" name="membership_id" id="membershipId">
                <input type="hidden" name="dojo_id" value="<?= htmlspecialchars($_GET['dojo_id'] ?? 1) ?>">
                <input type="hidden" id="selectedDates" name="selected_dates">
                
                <div id="calendar"></div>
                
                <div class="mt-3 text-center">
                    <button type="submit" class="btn btn-success btn-lg">Confirm Booking</button>
                </div>
            </form>
        </div>
    </main>

 
    <script src="../js/calendar-init.js" defer></script>
    <?php include '../files/footer.php'; ?>
</body>
</html>