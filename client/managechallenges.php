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

// Handle Add/Update/Delete for Challenge and Exercises
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['btnpost']) || isset($_POST['btnupdate'])) {
        try {
            $challId = $_POST['chall_id'] ?? null;
            $name = $_POST['txtname'] ?? '';
            $description = $_POST['txtdesc'] ?? '';
            $fileUploaded = !empty($_FILES['txtimg']['name']);
            $imageName = null;

            // Validate challenge input
            if (empty($name) || empty($description)) {
                $_SESSION['error'] = "Challenge name and description are required.";
                header("Location: managechallenges.php");
                return;
            }

            // Handle Challenge Image Upload
            if ($fileUploaded) {
                $imageName = handleImageUpload('txtimg', '../upload/');
            }

            // Begin transaction
            $pdo->beginTransaction();

            if (isset($_POST['btnpost'])) {
                // Insert new challenge
                $sql = "INSERT INTO challenges (title, image, description, master_id) 
                        VALUES (:name, :image, :description, :master_id)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name' => $name,
                    ':image' => $imageName,
                    ':description' => $description,
                    ':master_id' => $masterId,
                ]);
                $challId = $pdo->lastInsertId();
            } else {
                // Update existing challenge
                $sql = "UPDATE challenges 
                        SET title = :name" . 
                        ($fileUploaded ? ", image = :image" : "") . 
                        ", description = :description 
                        WHERE chall_id = :chall_id AND master_id = :master_id";
                
                $params = [
                    ':name' => $name,
                    ':description' => $description,
                    ':chall_id' => $challId,
                    ':master_id' => $masterId,
                ];
                if ($fileUploaded) {
                    $params[':image'] = $imageName;
                }
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }

            // Handle Exercises
            for ($i = 1; $i <= 10; $i++) {
                $exerciseTitle = $_POST["exercise_title_$i"] ?? '';
                $exerciseDesc = $_POST["exercise_desc_$i"] ?? '';
                $exerciseId = $_POST["exercise_id_$i"] ?? null;
                $exerciseImgUploaded = !empty($_FILES["exercise_img_$i"]['name']);
                $exerciseImgName = null;

                if ($exerciseImgUploaded) {
                    $exerciseImgName = handleImageUpload("exercise_img_$i", '../upload/');
                }

                if ($exerciseId) {
                    // Update existing exercise
                    $sql = "UPDATE challenge_exercises 
                            SET exercise_title = :title, 
                                exercise_description = :description" . 
                                ($exerciseImgUploaded ? ", exercise_image = :image" : "") . 
                            " WHERE exercise_id = :exercise_id AND chall_id = :chall_id";
                    
                    $params = [
                        ':title' => $exerciseTitle,
                        ':description' => $exerciseDesc,
                        ':exercise_id' => $exerciseId,
                        ':chall_id' => $challId
                    ];
                    if ($exerciseImgUploaded) {
                        $params[':image'] = $exerciseImgName;
                    }
                } else {
                    // Insert new exercise
                    $sql = "INSERT INTO challenge_exercises 
                            (chall_id, exercise_title, exercise_description, exercise_image, exercise_number) 
                            VALUES (:chall_id, :title, :description, :image, :number)";
                    $params = [
                        ':chall_id' => $challId,
                        ':title' => $exerciseTitle,
                        ':description' => $exerciseDesc,
                        ':image' => $exerciseImgName,
                        ':number' => $i
                    ];
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }

            $pdo->commit();
            $_SESSION["successmsg"] = isset($_POST['btnpost']) ? 
                                    "Challenge added successfully." : 
                                    "Challenge updated successfully.";

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
    }
    // Handle Delete action
    elseif (isset($_POST['btndelete']) && !empty($_POST['chall_id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM challenges WHERE chall_id = :chall_id AND master_id = :master_id");
            $stmt->execute([
                ':chall_id' => $_POST['chall_id'],
                ':master_id' => $masterId,
            ]);
            $_SESSION["successmsg"] = "Challenge deleted successfully.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
    }

    header("Location: managechallenges.php");
    return;
}

// Function to handle image upload
function handleImageUpload($fieldName, $uploadDir) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Challenges & Exercises</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?>
</head>
<body>
<?php include_once '../files/nav.php'; ?>
<br><br> <br><br>
<div class="container mt-5">
    <h3><?php flashMessages(); ?></h3>
    
    <!-- Challenge Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h2>Manage Challenge</h2>
        </div>
        <div class="card-body">
            <form id="challengeForm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="chall_id" id="chall_id">
                
                <div class="mb-3">
                    <label for="txtname" class="form-label">Challenge Name</label>
                    <input type="text" class="form-control" id="txtname" name="txtname" required>
                </div>

                <div class="mb-3">
                    <label for="txtimg" class="form-label">Challenge Image</label>
                    <input type="file" class="form-control" id="txtimg" name="txtimg">
                    <img id="imagePreview" class="mt-2" style="max-width: 200px; display: none;">
                </div>

                <div class="mb-3">
                    <label for="txtdesc" class="form-label">Challenge Description</label>
                    <textarea class="form-control" id="txtdesc" name="txtdesc" required></textarea>
                </div>

                <!-- Exercise Fields -->
                <div id="exercisesContainer">
                    <h3 class="mt-4 mb-3">Exercises</h3>
                    <?php for($i = 1; $i <= 10; $i++): ?>
                        <div class="card mb-3">
                            <div class="card-header">
                                Exercise <?php echo $i; ?>
                            </div>
                            <div class="card-body">
                                <input type="hidden" name="exercise_id_<?php echo $i; ?>" 
                                       id="exercise_id_<?php echo $i; ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" 
                                           name="exercise_title_<?php echo $i; ?>" 
                                           id="exercise_title_<?php echo $i; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" 
                                              name="exercise_desc_<?php echo $i; ?>" 
                                              id="exercise_desc_<?php echo $i; ?>" required></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Image</label>
                                    <input type="file" class="form-control" 
                                           name="exercise_img_<?php echo $i; ?>" 
                                           id="exercise_img_<?php echo $i; ?>">
                                    <img id="exercise_preview_<?php echo $i; ?>" 
                                         class="mt-2" style="max-width: 150px; display: none;">
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" name="btnpost" class="btn btn-primary">Add</button>
                    <button type="submit" name="btnupdate" class="btn btn-success">Update</button>
                    <button type="submit" name="btndelete" class="btn btn-danger" 
                            onclick="return confirm('Are you sure you want to delete this challenge?')">Delete</button>
                    <button type="reset" class="btn btn-warning">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Challenges Table -->
    <div class="card">
        <div class="card-header">
            <h3>Challenge List</h3>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Challenge Name</th>
                        <th>Image</th>
                        <th>Description</th>
                        <th>Exercises</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("
                        SELECT c.*, COUNT(e.exercise_id) as exercise_count 
                        FROM challenges c 
                        LEFT JOIN challenge_exercises e ON c.chall_id = e.chall_id 
                        WHERE c.master_id = :master_id 
                        GROUP BY c.chall_id
                    ");
                    $stmt->execute([':master_id' => $masterId]);
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . htmlentities($row["title"]) . "</td>";
                        echo "<td><img src='../upload/" . htmlentities($row["image"]) . 
                             "' alt='Challenge Image' style='width:100px;'></td>";
                        echo "<td>" . htmlentities($row["description"]) . "</td>";
                        echo "<td>" . $row["exercise_count"] . "/10</td>";
                        echo "<td>
                                <button class='btn btn-info btn-sm' 
                                        onclick='editChallenge(" . $row["chall_id"] . ")'>
                                    Edit
                                </button>
                            </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editChallenge(challId) {
    // Fetch challenge and exercise data
    fetch(`get_challenge_data.php?chall_id=${challId}`)
        .then(response => response.json())
        .then(data => {
            // Fill challenge data
            document.getElementById('chall_id').value = data.challenge.chall_id;
            document.getElementById('txtname').value = data.challenge.title;
            document.getElementById('txtdesc').value = data.challenge.description;
            
            // Show challenge image preview
            const imagePreview = document.getElementById('imagePreview');
            if (data.challenge.image) {
                imagePreview.src = '../upload/' + data.challenge.image;
                imagePreview.style.display = 'block';
            }

            // Fill exercise data
            data.exercises.forEach((exercise, index) => {
                const i = index + 1;
                document.getElementById(`exercise_id_${i}`).value = exercise.exercise_id;
                document.getElementById(`exercise_title_${i}`).value = exercise.exercise_title;
                document.getElementById(`exercise_desc_${i}`).value = exercise.exercise_description;
                
                // Show exercise image preview
                const exercisePreview = document.getElementById(`exercise_preview_${i}`);
                if (exercise.exercise_image) {
                    exercisePreview.src = '../upload/' + exercise.exercise_image;
                    exercisePreview.style.display = 'block';
                }
            });

            // Scroll to form
            document.getElementById('challengeForm').scrollIntoView({ behavior: 'smooth' });
        });
}

// Preview images before upload
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function(e) {
        const preview = this.nextElementSibling;
        if (preview && preview.tagName === 'IMG') {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
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