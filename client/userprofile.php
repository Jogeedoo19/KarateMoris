<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: login.php");
    exit();
}

// Fetch user details
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT first_name, last_name, username, email, address, image FROM user WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "Unable to fetch profile details.";
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>user Profile</title>
    <?php include '../files/csslib.php' ?> <!-- including libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
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
    </style>
</head>
<body>
<?php include_once '../files/nav.php' ?>
<br><br><br><br>
<main>
    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <img src="uploads/<?php echo htmlentities($user['image']); ?>" alt="Profile Image" class="profile-image">
                <h2><?php echo htmlentities($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                <p class="text-muted">@<?php echo htmlentities($user['username']); ?></p>
            </div>

            <div class="profile-details">
                <div class="detail-item">
                    <span>Email:</span> <?php echo htmlentities($user['email']); ?>
                </div>
                <div class="detail-item">
                    <span>Address:</span> <?php echo htmlentities($user['address']); ?>
                </div>
                <a href="changepass.php" class="btn btn-primary">Change Your Password</a>
                <div class="detail-item text-center mt-4">
                    <a href="edituserprofile.php" class="btn btn-primary">Edit Profile</a>
               
                </div>
            </div>
        </div>
    </div>
  
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- INCLUDING Footer -->
<?php include '../files/footer.php' ?>
</body>
</html>
