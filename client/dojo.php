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
    header("Location: dojo.php");
    return;
}

// Handle Add/Update/Delete actions
if (isset($_POST['txtname'])) {
    $dojoId = $_POST['dojo_id'] ?? null;
    $name = $_POST['txtname'];
    $address = $_POST['txtaddr'];
    $phonenumber = $_POST['txtpnum'];
    $fileUploaded = !empty($_FILES['txtimg']['name']);
    $imagePath = null;

    // Validate input
    if (empty($name) || empty($address) || empty($phonenumber)) {
        $_SESSION["error"] = "All fields are required.";
        header("Location: dojo.php");
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
            header("Location: dojo.php");
            return;
        }

        if ($fileSize > $maxSize) {
            $_SESSION['error'] = "Image size should be less than 5MB.";
            header("Location: dojo.php");
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
            header("Location: dojo.php");
            return;
        }
    }

    // Handle Update action
    if (isset($_POST['btnupdate']) && $dojoId) {
        try {
            $sql = "UPDATE dojo 
                    SET name = :name, address = :address, phonenumber = :phonenumber" .
                    ($fileUploaded ? ", image = :image" : "") . 
                    " WHERE dojo_id = :dojo_id AND master_id = :master_id";
            $params = [
                ':name' => $name,
                ':address' => $address,
                ':phonenumber' => $phonenumber,
                ':dojo_id' => $dojoId,
                ':master_id' => $masterId,
            ];

            if ($fileUploaded) {
                $params[':image'] = $imageName;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $_SESSION["successmsg"] = "Dojo updated successfully.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error updating dojo: " . $e->getMessage();
        }
        header("Location: dojo.php");
        return;
    }

    // Handle Delete action
    if (isset($_POST['btndelete']) && $dojoId) {
        try {
            $sql = "DELETE FROM dojo WHERE dojo_id = :dojo_id AND master_id = :master_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':dojo_id' => $dojoId, ':master_id' => $masterId]);

            $_SESSION["successmsg"] = "Dojo deleted successfully.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error deleting dojo: " . $e->getMessage();
        }
        header("Location: dojo.php");
        return;
    }

    // Handle Add action
    if (isset($_POST['btnpost'])) {
        try {
            $sql = "INSERT INTO dojo (name, address, phonenumber, image, master_id) 
                    VALUES (:name, :address, :phonenumber, :image, :master_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':address' => $address,
                ':phonenumber' => $phonenumber,
                ':image' => $imageName,
                ':master_id' => $masterId,
            ]);

            $_SESSION["successmsg"] = "Dojo added successfully.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error adding dojo: " . $e->getMessage();
        }
        header("Location: dojo.php");
        return;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Dojo</title>
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
        <form id="frmdojo" class="row" method="post" enctype="multipart/form-data" style="border: 3px solid #f1f1f1;">
            <input type="hidden" name="dojo_id" id="dojo_id">

            <div class="container">
                <center><h2>Manage Dojo</h2></center>
                <label for="txtname"><b>Name</b></label>
                <input type="text" id="txtname" placeholder="Enter name" name="txtname" required>

                <!-- <label for="txtaddr"><b>Address</b></label>
                <input type="text" id="txtaddr" placeholder="Enter address" name="txtaddr" required>
                <button type="button" onclick="getLocation()">📍Get Current Location</button> -->
                <label for="locationSearch"><b>Search Location</b></label>
                <input type="text" id="locationSearch" placeholder="Type a location..." onkeyup="searchLocation()">
                <ul id="suggestionsList" style="list-style: none; padding: 0; max-height: 150px; overflow-y: auto; background: white; border: 1px solid #ccc; position: absolute; display: none;"></ul>
                <input type="hidden" id="txtaddr" name="txtaddr"> <!-- Hidden field for selected address -->

                <label for="txtpnum"><b>Phone Number</b></label>
                <input type="text" id="txtpnum" placeholder="Enter phone number" name="txtpnum" required>

                <label for="txtimg"><b>Image</b></label>
                <input type="file" id="txtimg" name="txtimg" class="form-control">
                <br>
        <!-- Image Preview -->
        <img id="imagePreview" src="" alt="Selected Image" style="max-width: 150px; max-height: 150px; display: none;">
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
                        <th>Address</th>
                        <th>Phone Number</th>
                        <th>Image</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM dojo WHERE master_id = :master_id");
                    $stmt->execute([':master_id' => $_SESSION['master_id']]);
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . htmlentities($row["name"]) . "</td>";
                        echo "<td>" . htmlentities($row["address"]) . "</td>";
                        echo "<td>" . htmlentities($row["phonenumber"]) . "</td>";
                        echo "<td><img src='../upload/" . htmlentities($row["image"]) . "' alt='Dojo Image' width='100'></td>";
                        echo "<td>
                            <button class='btn btn-info' onclick='selectDojo(" . $row["dojo_id"] . ", `" 
                            . addslashes($row["name"]) . "`, `"
                            . addslashes($row["address"]) . "`, `"
                            . addslashes($row["phonenumber"]) . "`, `"
                            . addslashes($row["image"]) . "`)'>Edit</button>
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
function selectDojo(dojoId, name, address, phoneNumber, imagePath) {
    document.getElementById('dojo_id').value = dojoId;
    document.getElementById('txtname').value = name;
    document.getElementById('txtaddr').value = address;
    document.getElementById('txtpnum').value = phoneNumber;
// Set image preview
const imagePreview = document.getElementById('imagePreview');
    if (imagePath) {
        imagePreview.src = '../upload/' + imagePath;
        imagePreview.style.display = 'block';
    } else {
        imagePreview.style.display = 'none';
    }

    document.getElementById('frmdojo').scrollIntoView({ behavior: 'smooth' });
}

function confirmDelete() {
    return confirm('Are you sure you want to delete this dojo?');
}
</script>
<script src="../js/api.js"></script>
<!-- Including footer -->
<?php include '../files/footer.php'; ?>

</body>
</html>
