<?php
require_once "../db/pdo.php";
require_once "../db/util.php";
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to edit your profile.";
    header("Location: login.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: login.php");
    exit();
}

// Handle form submission
if (isset($_POST['update_profile'])) {
    $errors = [];

    // Sanitize inputs
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $address = htmlspecialchars(trim($_POST['address']));
    $image_name = $user['image']; // Retain existing image if no new image is uploaded

    // Validation
    if (strlen($first_name) < 2) $errors[] = "First name must be at least 2 characters.";
    if (strlen($last_name) < 2) $errors[] = "Last name must be at least 2 characters.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";
    if (strlen($address) < 5) $errors[] = "Address must be at least 5 characters.";

    // Image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $upload_dir = '../client/uploads/';

        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF images are allowed.";
        }
        if ($_FILES['image']['size'] > $max_size) {
            $errors[] = "Image size should not exceed 5MB.";
        }
        if (empty($errors)) {
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $image_name;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $errors[] = "Failed to upload the image.";
            }
        }
    }

    // Update the profile if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE user 
            SET first_name = ?, last_name = ?, email = ?, address = ?, image = ? 
            WHERE user_id = ?
        ");
        $success = $stmt->execute([$first_name, $last_name, $email, $address, $image_name, $user_id]);

        if ($success) {
            $_SESSION['success'] = "Profile updated successfully!";
            header("Location: userprofile.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update profile. Please try again.";
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?>
</head>
<body>
    <!-- Include navigation -->
    <?php include '../files/nav.php'; ?>
    <br><br> 
    <div class="container mt-5">
        <h2 class="text-center">Edit Profile</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlentities($user['first_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlentities($user['last_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlentities($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlentities($user['address']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Profile Image</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <small class="text-muted">Current image: <img src="../client/uploads/<?= htmlentities($user['image']); ?>" alt="Profile Image" width="50"></small>
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary w-100">Update Profile</button>
        </form>
    </div>

    <!-- Include Footer -->
    <?php include '../files/footer.php'; ?>
</body>
</html>
