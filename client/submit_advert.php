<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: login.php");
    return;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate inputs
        $requiredFields = ['company_name', 'websiteurl', 'alternatetext', 'keyword', 'numberofmonth', 'amount'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("All fields are required");
            }
        }

        // Handle logo upload
        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
            throw new Exception("Logo is required");
        }

        $logoFile = $_FILES['logo'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        // Check file type
        if (!in_array($logoFile['type'], $allowedTypes)) {
            throw new Exception("Invalid file type. Please upload JPG, PNG, or GIF");
        }

        // Create upload directory if it doesn't exist
        $uploadDir = '../upload/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate unique filename
        $extension = pathinfo($logoFile['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid('advert_') . '.' . $extension;
        $logoPath = $uploadDir . $uniqueName;

        // Move uploaded file
        if (!move_uploaded_file($logoFile['tmp_name'], $logoPath)) {
            throw new Exception("Failed to upload logo");
        }

        // Calculate dates
        $datePosted = date('Y-m-d');
        $numberOfMonths = (int)$_POST['numberofmonth'];
        $expireDate = date('Y-m-d', strtotime("+$numberOfMonths months"));

        // Insert into database
        $stmt = $pdo->prepare("
            INSERT INTO advertisement 
            (logopath, websiteurl, alternatetext, keyword, company_name, amount, 
             numberofmonth, date_posted, expire_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
        ");

        $stmt->execute([
            $uniqueName, // Store just the filename
            $_POST['websiteurl'],
            $_POST['alternatetext'],
            $_POST['keyword'],
            $_POST['company_name'],
            $_POST['amount'],
            $numberOfMonths,
            $datePosted,
            $expireDate
        ]);

        $_SESSION['success'] = "Advertisement submitted successfully. Waiting for admin approval.";
        header("Location: submit_advert.php");
        return;

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Advertisement</title>
    <?php include '../files/csslib.php'; ?>
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            display: none;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <?php include '../files/nav.php'; ?>
<br><br><br><br><br><br>
    <main class="container mt-4">
        <div class="form-container">
            <h2 class="text-center mb-4">Submit Advertisement</h2>
            <?php
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . htmlentities($_SESSION['success']) . '</div>';
    unset($_SESSION['success']); // Clear message after displaying
}
?>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . htmlentities($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            ?>

            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="company_name" class="form-label">Company Name</label>
                    <input type="text" class="form-control" id="company_name" name="company_name" required>
                </div>

                <div class="mb-3">
                    <label for="logo" class="form-label">Company Logo</label>
                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*" required>
                    <img id="logoPreview" class="preview-image">
                </div>

                <div class="mb-3">
                    <label for="websiteurl" class="form-label">Website URL</label>
                    <input type="url" class="form-control" id="websiteurl" name="websiteurl" required>
                </div>

                <div class="mb-3">
                    <label for="alternatetext" class="form-label">Alternative Text</label>
                    <input type="text" class="form-control" id="alternatetext" name="alternatetext" required>
                </div>

                <div class="mb-3">
                    <label for="keyword" class="form-label">Keywords</label>
                    <input type="text" class="form-control" id="keyword" name="keyword" required>
                </div>

                <div class="mb-3">
                    <label for="numberofmonth" class="form-label">Duration (Months)</label>
                    <select class="form-control" id="numberofmonth" name="numberofmonth" required>
                        <option value="">Select duration</option>
                        <?php for($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?> month<?= $i > 1 ? 's' : '' ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="amount" class="form-label">Amount ($)</label>
                    <input type="number" class="form-control" id="amount" name="amount" required step="0.01">
                </div>

                <button type="submit" class="btn btn-primary w-100">Submit Advertisement</button>
            </form>
        </div>
    </main>

    <?php include '../files/footer.php'; ?>

    <script>
        // Preview image before upload
        document.getElementById('logo').addEventListener('change', function(e) {
            const preview = document.getElementById('logoPreview');
            const file = e.target.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }

            if (file) {
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>