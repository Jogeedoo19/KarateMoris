<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to display messages
function flashMessage() {
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';
        unset($_SESSION['success']);
    }
}

// Process form submission
if (isset($_POST['signup'])) {
    $errors = [];
    
    // Validate and sanitize inputs
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $username = sanitizeInput($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = sanitizeInput($_POST['address']);
    $image_name = null;

    // Validation
    if (strlen($first_name) < 2) $errors[] = "First name must be at least 2 characters.";
    if (strlen($last_name) < 2) $errors[] = "Last name must be at least 2 characters.";
    if (strlen($username) < 4) $errors[] = "Username must be at least 4 characters.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";
    if (strlen($address) < 5) $errors[] = "Please enter a valid address.";

    // Image Upload Validation
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $upload_dir = 'uploads/';
        
        if (!in_array($_FILES['image']['type'], $allowed_types)) $errors[] = "Only JPG, PNG, and GIF images are allowed.";
        if ($_FILES['image']['size'] > $max_size) $errors[] = "Image size should be less than 5MB.";
        if (empty($errors)) {
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $image_name;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) $errors[] = "Failed to upload image.";
        }
    } else {
        $errors[] = "Profile image is required.";
    }

    // Check if email or username already exists
    $stmt = $pdo->prepare("SELECT master_id FROM master WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->rowCount() > 0) $errors[] = "Email or Username already exists.";

    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO master (first_name, last_name, username, email, password, address, status, image) 
                    VALUES (?, ?, ?, ?, ?, ?, 0, ?)";
            $stmt = $pdo->prepare($sql);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            if ($stmt->execute([$first_name, $last_name, $username, $email, $hashed_password, $address, $image_name])) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: masterlogin.php");
                exit();
            } else {
                $_SESSION['error'] = "Registration failed. Please try again.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
    header("Location: masterlogin.php");
    return;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?> <!-- Including libraries -->
    <style>
        .form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
        #image-preview {
            max-width: 200px;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>
       <!-- Include header and navigation -->
       <?php include_once '../files/nav.php'; ?>
    <br><br> <br><br>
    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4">Sign Up</h2>
            <?php flashMessage(); ?>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required value="<?php echo $_POST['first_name'] ?? ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required value="<?php echo $_POST['last_name'] ?? ''; ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required value="<?php echo $_POST['username'] ?? ''; ?>">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required value="<?php echo $_POST['email'] ?? ''; ?>">
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="2" required><?php echo $_POST['address'] ?? ''; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Profile Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required onchange="previewImage(this)">
                    <img id="image-preview" src="#" alt="Preview">
                </div>
                <button type="submit" name="signup" class="btn btn-primary w-100">Sign Up</button>
                <div class="text-center mt-3">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        function previewImage(input) {
            const preview = document.getElementById('image-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    <?php include '../files/footer.php'; ?>
</body>
</html>
