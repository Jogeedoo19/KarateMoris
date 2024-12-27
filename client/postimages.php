<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['master_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: masterlogin.php");
    return;
}

$masterId = $_SESSION['master_id'];

// Handle Cancel button
if (isset($_POST['btncancel'])) {
    header("Location: postimages.php");
    return;
}

// Handle Add/Update/Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $image_name = null;

    // Image Upload Validation
    if (!empty($_FILES['image']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $upload_dir = '../upload/';

        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF images are allowed.";
        }
        if ($_FILES['image']['size'] > $max_size) {
            $errors[] = "Image size should be less than 5MB.";
        }
        if (empty($errors)) {
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $image_name;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $errors[] = "Failed to upload image.";
            }
        }
    }

    // Perform database actions if there are no errors
    if (empty($errors)) {
        if (isset($_POST['btnpost'])) {
            // Add Image
            $stmt = $pdo->prepare("INSERT INTO gallery (image, postedby, master_id) VALUES (:image, :postedby, :master_id)");
            $stmt->execute([
                ':image' => $image_name,
                ':postedby' => $_SESSION['master_id'],
                ':master_id' => $_SESSION['master_id']
            ]);
            $_SESSION['success'] = "Image uploaded successfully.";
        } elseif (isset($_POST['btnupdate']) && isset($_POST['gal_id'])) {
            // Update Image
            $galId = $_POST['gal_id'];
            $updateSQL = "UPDATE gallery SET image = :image WHERE gal_id = :gal_id AND master_id = :master_id";
            $params = [':gal_id' => $galId, ':master_id' => $masterId];
            if ($image_name) {
                $params[':image'] = $image_name;
            }
            $stmt = $pdo->prepare($updateSQL);
            $stmt->execute($params);
            $_SESSION['success'] = "Image updated successfully.";
        } elseif (isset($_POST['btndelete']) && isset($_POST['gal_id'])) {
            // Delete Image
            $stmt = $pdo->prepare("DELETE FROM gallery WHERE gal_id = :gal_id AND master_id = :master_id");
            $stmt->execute([':gal_id' => $_POST['gal_id'], ':master_id' => $masterId]);
            $_SESSION['success'] = "Image deleted successfully.";
        }
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
    }
    header("Location: postimages.php");
    return;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Images</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?>
</head>
<body>
<?php include_once '../files/nav.php'; ?>

<br><br><br><br><br><br>
<main class="main">
<h3>
<?php
// Call the Flash messaging function

flashMessages();

?>
</h3>
    <div class="container col-xl-4 col-lg-6">
        <form id="frmImage" method="post" enctype="multipart/form-data" style="border: 3px solid #f1f1f1;">
            <input type="hidden" name="gal_id" id="gal_id">
            <div class="container">
                <center><h2>Manage Images</h2></center>
                <label for="image"><b>Image</b></label>
                <input type="file" class="form-control" name="image" id="image">
                <br>
                <div id="preview" style="display: none;">
                    <label for="current_image"><b>Current Image:</b></label>
                    <br>
                    <img id="current_image" src="" alt="Current Image" width="100">
                </div>
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
            <table class="table table-dark table-hover table-striped w-75">
                <thead>
                    <th>Image</th>
                    <th>Action</th>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM gallery WHERE master_id = :master_id");
                    $stmt->execute([':master_id' => $_SESSION['master_id']]);
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td><img src='../upload/" . htmlentities($row['image']) . "' alt='Image' width='100'></td>";
                        echo "<td>
                            <button class='btn btn-info' onclick='selectImage(" . $row['gal_id'] . ", `" . htmlentities($row['image']) . "`)'>Edit</button>
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
function selectImage(galId, imageName) {
    // Populate the form fields with the selected image data
    document.getElementById('gal_id').value = galId;

    // Show current image preview
    const preview = document.getElementById('preview');
    const currentImage = document.getElementById('current_image');
    currentImage.src = '../upload/' + imageName;
    preview.style.display = 'block';

    // Scroll to the form
    document.getElementById('frmImage').scrollIntoView({ behavior: 'smooth' });
}

function confirmDelete() {
    return confirm('Are you sure you want to delete this image?');
}
</script>

<?php include '../files/footer.php'; ?>
</body>
</html>
