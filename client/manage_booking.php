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

$userId = $_SESSION['user_id'];

// Fetch all bookings for the logged-in user
$query = "SELECT 
            b.book_id AS id, 
            b.booking_date, 
            d.name AS dojo_name, 
            cm.catmember_name AS membership_name
          FROM booking b
          JOIN dojo d ON b.dojo_id = d.dojo_id
          JOIN membership m ON b.mem_id = m.mem_id
          JOIN categorymember cm ON m.catmember_id = cm.catmember_id
          WHERE b.user_id = :user_id";



$params = [':user_id' => $userId];

// Filter by date or dojo if search parameters are provided
if (!empty($_GET['search_date'])) {
    $query .= " AND b.booking_date = :search_date";
    $params[':search_date'] = $_GET['search_date'];
}

if (!empty($_GET['search_dojo'])) {
    $query .= " AND d.name LIKE :search_dojo";
    $params[':search_dojo'] = '%' . $_GET['search_dojo'] . '%';
}

$query .= " ORDER BY b.booking_date ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .booking-table {
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .booking-table th, .booking-table td {
            padding: 15px;
            text-align: center;
        }
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .no-results {
            text-align: center;
            margin: 20px 0;
            color: #888;
        }
    </style>
</head>
<body>
    <?php include '../files/nav.php'; ?>
    <br><br> <br><br>
    <main class="container mt-5">
        <h2 class="text-center mb-4">Manage Your Bookings</h2>
        
        <!-- Search Bar -->
        <div class="search-bar">
            <form method="GET" class="d-flex align-items-center w-100">
                <div class="form-group me-3">
                    <label for="search_date" class="form-label">Search by Date:</label>
                    <input type="date" class="form-control" id="search_date" name="search_date" 
                           value="<?= htmlspecialchars($_GET['search_date'] ?? '') ?>">
                </div>
                <div class="form-group me-3">
                    <label for="search_dojo" class="form-label">Search by Dojo:</label>
                    <input type="text" class="form-control" id="search_dojo" name="search_dojo" 
                           placeholder="Enter dojo name" 
                           value="<?= htmlspecialchars($_GET['search_dojo'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary mt-4">Search</button>
            </form>
        </div>

        <!-- Booking Table -->
        <?php if (count($bookings) > 0): ?>
        <div class="table-responsive booking-table">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Booking Date</th>
                        <th>Dojo Name</th>
                        <th>Membership Plan</th>
                        <!-- <th>Actions</th>  -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $index => $booking): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($booking['booking_date']) ?></td>
                        <td><?= htmlspecialchars($booking['dojo_name']) ?></td>
                        <td><?= htmlspecialchars($booking['membership_name']) ?></td>
                        <!-- <td>
                            <a href="manage_booking.php?id=#" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                            <a href="manage_booking.php?id=#" class="btn btn-danger btn-sm" 
                               onclick="return confirm('Are you sure you want to delete this booking?');">
                                <i class="bi bi-trash"></i> Delete
                            </a>
                        </td> -->
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="no-results">
            <p>No bookings found. Try refining your search or make a new booking.</p>
        </div>
        <?php endif; ?>
    </main>

    <?php include '../files/footer.php'; ?>
</body>
</html>
