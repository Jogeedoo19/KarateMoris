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

        <div id="calendarSection" style="display: none;">
        <h3>Select Training Dates</h3>
        <div class="membership-info alert alert-info" id="selectedPlanInfo"></div>
        <form id="bookingForm" method="POST" action="process_booking.php">
            <input type="hidden" name="mem_id" id="membershipId">
            <input type="hidden" name="dojo_id" value="<?= htmlspecialchars($_GET['dojo_id'] ?? 1) ?>">
            <input type="hidden" name="selected_dates" id="selectedDates">
            <input type="hidden" name="catmember_id" id="catmemberId"> <!-- Add this line -->
            <div id="calendar"></div>
            <button type="submit" class="btn btn-success mt-3" id="confirmBooking">
                Confirm Booking
            </button>
        </form>
    </div>

    <script>
        let calendar;
        let selectedDates = [];
        let maxSessions = null;
        let currentMembershipName = '';

        document.addEventListener('DOMContentLoaded', function() {
            initializeCalendar();
            setupMembershipButtons();
            setupBookingForm();
        });

        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar');
            const bookedDates = <?= json_encode($bookedDates) ?>;

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                selectable: true,
                selectConstraint: {
                    start: new Date(),
                },
                select: function(info) {
                    handleDateSelection(info);
                },
                events: bookedDates.map(date => ({
                    title: 'Booked',
                    start: date,
                    color: 'red',
                    display: 'background'
                }))
            });

            calendar.render();
        }

        function handleDateSelection(info) {
            const selectedDate = info.startStr;
            const today = new Date();
            const selectedDateTime = new Date(selectedDate);

            // Prevent selecting past dates
            if (selectedDateTime < today) {
                alert('Cannot select past dates');
                return;
            }

            // Check if date is already selected
            if (selectedDates.includes(selectedDate)) {
                selectedDates = selectedDates.filter(date => date !== selectedDate);
                calendar.getEvents().forEach(event => {
                    if (event.start.toISOString().split('T')[0] === selectedDate && event.title === 'Selected') {
                        event.remove();
                    }
                });
            } else {
                // Check maximum sessions limit
                if (maxSessions !== 'unlimited' && selectedDates.length >= maxSessions) {
                    alert(`You can only select up to ${maxSessions} sessions with this plan`);
                    return;
                }

                selectedDates.push(selectedDate);
                calendar.addEvent({
                    title: 'Selected',
                    start: selectedDate,
                    color: 'green',
                    display: 'background'
                });
            }

            updateSelectedDatesInput();
        }

        function setupMembershipButtons() {
            document.querySelectorAll('.membership-btn').forEach(button => {
                button.addEventListener('click', function() {
                    // Reset previously selected dates
                    selectedDates = [];
                    calendar.getEvents().forEach(event => {
                        if (event.title === 'Selected') {
                            event.remove();
                        }
                    });

                    // Update UI
                    document.querySelectorAll('.pricing-item').forEach(item => {
                        item.classList.remove('selected');
                    });
                    this.closest('.pricing-item').classList.add('selected');

                    // Set membership details
                    const membershipId = this.dataset.membershipId;
                    currentMembershipName = this.dataset.membershipName;
                    maxSessions = this.dataset.maxSessions === 'unlimited' ? 'unlimited' : parseInt(this.dataset.maxSessions);

                    // Update form and show calendar
                    document.getElementById('membershipId').value = membershipId;
                    document.getElementById('catmemberId').value = membershipId; // Assigning membership ID dynamically
                    document.getElementById('selectedPlanInfo').textContent = 
                        `Selected Plan: ${currentMembershipName} (${maxSessions === 'unlimited' ? 'Unlimited' : maxSessions} sessions)`;
                    document.getElementById('calendarSection').style.display = 'block';

                    // Refresh calendar
                    calendar.render();
                });
            });
        }

        function setupBookingForm() {
            document.getElementById('bookingForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (selectedDates.length === 0) {
                    alert('Please select at least one date');
                    return;
                }
                // Prevent form submission if required fields are missing
                if (!document.getElementById('membershipId').value ||
                    !document.querySelector('input[name="dojo_id"]').value ||
                    !document.getElementById('selectedDates').value ||
                    !document.getElementById('catmemberId').value) {
                    alert('Missing required fields');
                    return false;
                }

                updateSelectedDatesInput();
                this.submit();
            });
        }

        function updateSelectedDatesInput() {
            document.getElementById('selectedDates').value = JSON.stringify(selectedDates);
            
        }
    </script>
</body>
</html>
