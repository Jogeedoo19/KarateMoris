<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

if (!isset($_SESSION['master_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: masterlogin.php");
    return;
}

$masterId = $_SESSION['master_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['btnpost'])) {
        try {
            // First insert the main challenge
            $challengeStmt = $pdo->prepare("INSERT INTO challenges (title, image, description, master_id) 
                                          VALUES (:title, :image, :description, :master_id)");
            
            // Handle main challenge image upload
            $challengeImage = null;
            if (!empty($_FILES['challenge_image']['name'])) {
                $challengeImage = handleImageUpload('challenge_image');
            }
            
            $challengeStmt->execute([
                ':title' => $_POST['challenge_title'],
                ':image' => $challengeImage,
                ':description' => $_POST['challenge_description'],
                ':master_id' => $masterId
            ]);
            
            $challId = $pdo->lastInsertId();
            
            // Then insert 10 exercises
            $exerciseStmt = $pdo->prepare("INSERT INTO challenge_exercises 
                (chall_id, exercise_title, exercise_description, exercise_image, exercise_number) 
                VALUES (:chall_id, :title, :description, :image, :number)");
                
            for ($i = 1; $i <= 10; $i++) {
                $imageFieldName = "exercise_image_" . $i;
                $exerciseImage = null;
                
                if (!empty($_FILES[$imageFieldName]['name'])) {
                    $exerciseImage = handleImageUpload($imageFieldName);
                }
                
                $exerciseStmt->execute([
                    ':chall_id' => $challId,
                    ':title' => $_POST['exercise_title_' . $i],
                    ':description' => $_POST['exercise_description_' . $i],
                    ':image' => $exerciseImage,
                    ':number' => $i
                ]);
            }
            
            $_SESSION["successmsg"] = "Challenge and exercises created successfully.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
        
        header("Location: managechallenges.php");
        return;
    }
}

// Function to handle image upload
function handleImageUpload($fieldName) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    $uploadDir = '../upload/';
    
    if (!empty($_FILES[$fieldName]['name'])) {
        $fileType = $_FILES[$fieldName]['type'];
        $fileSize = $_FILES[$fieldName]['size'];
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Only JPG, PNG, and GIF images are allowed.");
        }
        
        if ($fileSize > $maxSize) {
            throw new Exception("Image size should be less than 5MB.");
        }
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
        $imageName = uniqid() . '.' . $fileExtension;
        $imagePath = $uploadDir . $imageName;
        
        if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $imagePath)) {
            throw new Exception("Failed to upload the image.");
        }
        
        return $imageName;
    }
    return null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Challenge with Exercises</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?>
</head>
<body>
    <?php include_once '../files/nav.php'; ?>
    <br><br><br><br><br><br>

    <main class="main">
        <h3><?php flashMessages(); ?></h3>
        <div class="container">
            <form method="post" enctype="multipart/form-data" class="mb-4">
                <!-- Main Challenge Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Challenge Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="challenge_title" class="form-label">Challenge Title</label>
                            <input type="text" class="form-control" id="challenge_title" name="challenge_title" required>
                        </div>
                        <div class="mb-3">
                            <label for="challenge_image" class="form-label">Challenge Image</label>
                            <input type="file" class="form-control" id="challenge_image" name="challenge_image">
                        </div>
                        <div class="mb-3">
                            <label for="challenge_description" class="form-label">Challenge Description</label>
                            <textarea class="form-control" id="challenge_description" name="challenge_description" required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Exercise Information -->
                <div id="exercises">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4>Exercise <?php echo $i; ?></h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="exercise_title_<?php echo $i; ?>" class="form-label">Exercise Title</label>
                                    <input type="text" class="form-control" id="exercise_title_<?php echo $i; ?>" 
                                           name="exercise_title_<?php echo $i; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="exercise_description_<?php echo $i; ?>" class="form-label">Exercise Description</label>
                                    <textarea class="form-control" id="exercise_description_<?php echo $i; ?>" 
                                              name="exercise_description_<?php echo $i; ?>" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="exercise_image_<?php echo $i; ?>" class="form-label">Exercise Image</label>
                                    <input type="file" class="form-control" id="exercise_image_<?php echo $i; ?>" 
                                           name="exercise_image_<?php echo $i; ?>">
                                    <div class="image-preview mt-2"></div>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="text-center">
                    <button type="submit" name="btnpost" class="btn btn-primary">Create Challenge with Exercises</button>
                    <a href="managechallenges.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Image preview functionality
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const preview = this.parentElement.querySelector('.image-preview');
                if (preview) {
                    preview.innerHTML = '';
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.maxWidth = '200px';
                            img.style.marginTop = '10px';
                            preview.appendChild(img);
                        }
                        reader.readAsDataURL(file);
                    }
                }
            });
        });
    </script>

    <?php include '../files/footer.php'; ?>
</body>
</html>