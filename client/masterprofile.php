<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

// Redirect if master is not logged in
if (!isset($_SESSION['master_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: masterlogin.php");
    exit();
}

// Fetch master details
$masterId = $_SESSION['master_id'];
$stmt = $pdo->prepare("SELECT first_name, last_name, username, email, address, image FROM master WHERE master_id = ?");
$stmt->execute([$masterId]);
$master = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch rating statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_reviews,
        AVG(CAST(rate AS DECIMAL(10,1))) as average_rating,
        SUM(CASE WHEN rate = '5' THEN 1 ELSE 0 END) as five_stars,
        SUM(CASE WHEN rate = '4' THEN 1 ELSE 0 END) as four_stars,
        SUM(CASE WHEN rate = '3' THEN 1 ELSE 0 END) as three_stars,
        SUM(CASE WHEN rate = '2' THEN 1 ELSE 0 END) as two_stars,
        SUM(CASE WHEN rate = '1' THEN 1 ELSE 0 END) as one_star
    FROM review 
    WHERE master_id = ?
");
$stmt->execute([$masterId]);
$ratings = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate percentages for each star rating
$total = $ratings['total_reviews'] ?: 1; // Avoid division by zero
$star_percentages = [
    5 => ($ratings['five_stars'] / $total) * 100,
    4 => ($ratings['four_stars'] / $total) * 100,
    3 => ($ratings['three_stars'] / $total) * 100,
    2 => ($ratings['two_stars'] / $total) * 100,
    1 => ($ratings['one_star'] / $total) * 100
];

if (!$master) {
    $_SESSION['error'] = "Unable to fetch profile details.";
    header("Location: masterlogin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Profile</title>
    <?php include '../files/csslib.php' ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
      
        .profile-container {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
        }
        .profile-header {
            text-align: center;
        }
        .profile-details {
            margin-top: 20px;
        }
        .detail-item {
            margin-bottom: 15px;
        }
        .detail-item span {
            font-weight: bold;
        }
        .rating-overview {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .average-rating {
            font-size: 48px;
            font-weight: bold;
            color: #ffc107;
        }
        .star-bar {
            display: flex;
            align-items: center;
            margin: 8px 0;
        }
        .star-label {
            min-width: 60px;
        }
        .progress {
            flex-grow: 1;
            margin: 0 10px;
            height: 15px;
        }
        .star-count {
            min-width: 50px;
            text-align: right;
        }
        .rating-stars {
            color: #ffc107;
            font-size: 24px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
<?php include_once '../files/nav.php' ?>
<br><br> <br><br> 
<main>
    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <img src="uploads/<?php echo htmlentities($master['image']); ?>" alt="Profile Image" class="profile-image">
                <h2><?php echo htmlentities($master['first_name'] . ' ' . $master['last_name']); ?></h2>
                <p class="text-muted">@<?php echo htmlentities($master['username']); ?></p>
            </div>

            <div class="profile-details">
                <div class="detail-item">
                    <span>Email:</span> <?php echo htmlentities($master['email']); ?>
                </div>
                <div class="detail-item">
                    <span>Address:</span> <?php echo htmlentities($master['address']); ?>
                </div>
                
                <!-- Rating Overview Section -->
                <div class="rating-overview">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="average-rating">
                                <?php echo number_format($ratings['average_rating'], 1); ?>
                            </div>
                            <div class="rating-stars">
                                <?php
                                $rating = round($ratings['average_rating']);
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                }
                                ?>
                            </div>
                            <div class="total-reviews">
                                <?php echo $ratings['total_reviews']; ?> reviews
                            </div>
                        </div>
                        <div class="col-md-8">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                            <div class="star-bar">
                                <div class="star-label"><?php echo $i; ?> stars</div>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" 
                                         role="progressbar" 
                                         style="width: <?php echo $star_percentages[$i]; ?>%" 
                                         aria-valuenow="<?php echo $star_percentages[$i]; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <div class="star-count">
                                    <?php echo $ratings[$i . '_' . ($i === 1 ? 'star' : 'stars')]; ?>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div><br>
                <a href="changepassmaster.php" class="btn btn-primary">Change Your Password</a>
                <div class="detail-item text-center mt-4">
                    <a href="editmasterprofile.php" class="btn btn-primary">Edit Profile</a>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../files/footer.php' ?>
</body>
</html>