<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['master_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: masterlogin.php");
    return;
}

$masterId = $_SESSION['master_id'];

// Handle Cancel button
if (isset($_POST['btncancel'])) {
    header("Location: managechallenges.php");
    return;
}

// Handle Add/Update/Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $challId = $_POST['chall_id'] ?? null;
    $name = $_POST['txtname'] ?? '';
    $description = $_POST['txtdesc'] ?? '';
    $fileUploaded = !empty($_FILES['txtimg']['name']);
    $imageName = null;

    // Validate input
    if (empty($name) || empty($description)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: managechallenges.php");
        return;
    }

    // Handle Image Upload
    if ($fileUploaded) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $uploadDir = '../upload/';

        $fileType = $_FILES['txtimg']['type'];
        $fileSize = $_FILES['txtimg']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['error'] = "Only JPG, PNG, and GIF images are allowed.";
            header("Location: managechallenges.php");
            return;
        }

        if ($fileSize > $maxSize) {
            $_SESSION['error'] = "Image size should be less than 5MB.";
            header("Location: managechallenges.php");
            return;
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExtension = pathinfo($_FILES['txtimg']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid() . '.' . $fileExtension;
        $imagePath = $uploadDir . $imageName;

        if (!move_uploaded_file($_FILES['txtimg']['tmp_name'], $imagePath)) {
            $_SESSION['error'] = "Failed to upload the image.";
            header("Location: managechallenges.php");
            return;
        }
    }

    try {
        // Handle Update action
        if (isset($_POST['btnupdate']) && $challId) {
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
            $_SESSION["successmsg"] = "Challenge updated successfully.";
        }

        // Handle Delete action
        elseif (isset($_POST['btndelete']) && $challId) {
            $sql = "DELETE FROM challenges WHERE chall_id = :chall_id AND master_id = :master_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':chall_id' => $challId,
                ':master_id' => $masterId,
            ]);
            $_SESSION["successmsg"] = "Challenge deleted successfully.";
        }

        // Handle Add action
        elseif (isset($_POST['btnpost'])) {
            $sql = "INSERT INTO challenges (title, image, description, master_id) 
                    VALUES (:name, :image, :description, :master_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':image' => $imageName,
                ':description' => $description,
                ':master_id' => $masterId,
            ]);
            $_SESSION["successmsg"] = "Challenge added successfully.";
        }

    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    header("Location: managechallenges.php");
    return;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Challenge</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?> <!-- Including CSS libraries -->
</head>
<body>

<!-- Including header and navigation -->
<?php include_once '../files/nav.php'; ?>

<br><br><br><br><br><br>

<main class="main">
    <h3><?php flashMessages(); ?></h3>
    <div class="container col-xl-6 col-lg-8">
        <form id="frmcom" class="row" method="post" enctype="multipart/form-data" style="border: 3px solid #f1f1f1;">
            <input type="hidden" name="chall_id" id="chall_id">

            <div class="container">
                <center><h2>Manage Competition</h2></center>
                <label for="txtname"><b>Name</b></label>
                <input type="text" id="txtname" placeholder="Enter name" name="txtname" required>

                <label for="txtimg"><b>Image</b></label>
                <input type="file" id="txtimg" name="txtimg" class="form-control">
                <br>
        <!-- Image Preview -->
        <img id="imagePreview" src="" alt="Selected Image" style="max-width: 150px; max-height: 150px; display: none;">

                <label for="txtdesc"><b>Description</b></label>
                <input type="text" id="txtdesc" placeholder="Enter description" name="txtdesc" required>


                
                
            </div>

            <div class="container" style="background-color:#f1f1f1">
                <center>
                    <div>
                        <button type="submit" name="btnpost" class="btn btn-primary col-xl-2 pt-2">Add</button>
                        <button type="submit" name="btnupdate" class="btn btn-success col-xl-2 pt-2">Update</button>
                        <button type="submit" name="btndelete" class="btn btn-danger col-xl-2 pt-2" onclick="return confirmDelete()">Delete</button>
                        <button type="submit" name="btncancel" class="btn btn-warning col-xl-2 pt-2">Cancel</button>
                    </div>
                </center>
            </div>
        </form>
        <br>
        <div class="row mt-3">
            <table class="table table-dark table-hover table-striped w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Image</th>
                        <th>Description</th>
                       
                        
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM challenges WHERE master_id = :master_id");
                    $stmt->execute([':master_id' => $_SESSION['master_id']]);
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . htmlentities($row["title"]) . "</td>";
                        echo "<td><img src='../upload/" . htmlentities($row["image"]) . "' alt='Challenge Image' width='100'></td>";
                        echo "<td>" . htmlentities($row["description"]) . "</td>";
                      
                        
                        echo "<td>
                            <button class='btn btn-info' onclick='selectChallenge(" . $row["chall_id"] . ", `" 
                            . addslashes($row["title"]) . "`, `"
                            . addslashes($row["image"]) . "`, `"
                            . addslashes($row["description"]) . "`)'>Edit</button>
                        </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
function selectChallenge(challId, name, imagePath, description) {
    document.getElementById('chall_id').value = challId;
    document.getElementById('txtname').value = name;
    
    
  
// Set image preview
const imagePreview = document.getElementById('imagePreview');
    if (imagePath) {
        imagePreview.src = '../upload/' + imagePath;
        imagePreview.style.display = 'block';
    } else {
        imagePreview.style.display = 'none';
    }
    document.getElementById('txtdesc').value = description;
    document.getElementById('frmcom').scrollIntoView({ behavior: 'smooth' });
}


function confirmDelete() {
    return confirm('Are you sure you want to delete this challenge?');
}
</script>

<!-- Including footer -->
<?php include '../files/footer.php'; ?>

</body>
</html>
