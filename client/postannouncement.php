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

// Debugging: Ensure `user_id` is set
if (!$masterId) {
    die("Error: Master ID is missing from the session.");
}

// Handle Cancel button
if (isset($_POST['btncancel'])) {
    header("Location: postannouncement.php");
    return;
}

// Handle Add/Update/Delete actions
if (isset($_POST['txtmsg'])) {
    $announcementId = $_POST['a_id'] ?? null;
    $description = $_POST['txtmsg'];

    // Validate input
    if (empty($description)) {
        $_SESSION["error"] = "Announcement cannot be empty.";
        header("Location: postannouncement.php");
        return;
    }


    // Handle Update action
    if (isset($_POST['btnupdate'])) {
        if ($announcementId) {
            try {
                $sql = "UPDATE announcement SET description = :description WHERE a_id = :a_id AND master_id = :master_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':description' => $description, ':a_id' => $announcementId, ':master_id' => $masterId]);
                $_SESSION["successmsg"] = "Announcement Updated";
            } catch (Exception $e) {
                $_SESSION['error'] = "Error updating announcement: " . $e->getMessage();
            }
        } else {
            $_SESSION["error"] = "Invalid announcement selected for update.";
        }
        header("Location: postannouncement.php");
        return;
    }
    // Handle Delete action
    if (isset($_POST['btndelete'])) {
        if ($announcementId) {
            try {
                $sql = "DELETE FROM announcement WHERE a_id = :a_id AND master_id = :master_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':a_id' => $announcementId, ':master_id' => $masterId]);
                $_SESSION["successmsg"] = "Announcement Deleted";
            } catch (Exception $e) {
                $_SESSION['error'] = "Error deleting announcement: " . $e->getMessage();
            }
        } else {
            $_SESSION["error"] = "Invalid announcement selected for deletion.";
        }
        header("Location: postannouncement.php");
        return;
    }

    // Handle Add action
    if (isset($_POST['btnpost'])) {
        try {
            $sql = "INSERT INTO announcement (description, master_id) VALUES (:description, :master_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':description' => $description, ':master_id' => $masterId]);
            $_SESSION["successmsg"] = "Announcement Added";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error adding announcement: " . $e->getMessage();
        }
        header("Location: postannouncement.php");
        return;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Announcement</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php' ?> <!-- including libraries -->
    
</head>
<body>

<!-- including header and banner -->
<?php include_once '../files/nav.php' ?>

<br><br><br><br><br><br>

<main class="main">
<h3>
<?php
// Call the Flash messaging function

flashMessages();

?>
</h3>

<div class="container col-xl-4 col-lg-6">
<form id="frmann" class="row" method="post" enctype="multipart/form-data" style="border: 3px solid #f1f1f1;">
<input type="hidden" name="a_id"> <!-- Hidden field for testimonial ID -->


<div class="container">
    <center><h2>Post Announcement</h2></center>
<label for="txtmsg"><b>Announcement</b></label>
<input type="text" placeholder="Enter announcement" name="txtmsg" required>



</div>

<div class="container" style="background-color:#f1f1f1">
 
<center><div>
<button type="submit" name="btnpost" class="btn btn-primary col-xl-2 pt-2">Add</button>
            <button type="submit" name="btnupdate" class="btn btn-success col-xl-2 pt-2">Update</button>
            <button type="submit" name="btndelete" class="btn btn-danger col-xl-2 pt-2" onclick="return confirmDelete()">Delete</button>
            <button type="submit" name="btncancel" class="btn btn-warning col-xl-2 pt-2">Cancel</button>
</div></center>
</div>
</form><br>

<div class="row mt-3">
<table class="table table-dark table-hover table-striped w-75" id="ls">
    <thead>
        <th>Announcement</th>
    
        <th>Action</th>
    </thead>
    <tbody>
        <?php
        $stmt = $pdo->prepare("SELECT * FROM announcement WHERE master_id = :master_id");
        $stmt->execute([':master_id' => $_SESSION['master_id']]);
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlentities($rows["description"]) . "</td>";
            
            echo "<td>
               <button class='btn btn-info' onclick='selectAnnouncement(" . $rows["a_id"] . ", `" . addslashes($rows["description"]) . "`)'>Edit</button>

            </td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
</div>


</main>
<script>
function selectAnnouncement(announcementId, description) {
    // Populate the hidden test_id field
    document.querySelector('input[name="a_id"]').value = announcementId;

    // Populate the txtmsg field
    document.querySelector('input[name="txtmsg"]').value = description;

    // Optional: Scroll to the form or highlight it
    document.getElementById('frmann').scrollIntoView({ behavior: 'smooth' });
}
function confirmDelete() {
    return confirm('Are you sure you want to delete this announcement?');
}
</script>


<!-- INCLUDING Footer -->
<?php include '../files/footer.php' ?>


    
</body>
</html>
    
</body>
</html>