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

// Debugging: Ensure `master_id` is set
if (!$masterId) {
    die("Error: Master ID is missing from the session.");
}

// Handle Cancel button
if (isset($_POST['btncancel'])) {
    header("Location: postvideo.php");
    return;
}

// Handle Add/Update/Delete actions
if (isset($_POST['txtmsg'])) {
    $vidId = $_POST['vid_id'] ?? null;
    $title = $_POST['txtmsg'];
    $description = $_POST['txtdesc'];
    $video = $_POST['txtvid'];
    $catId = $_POST['category'];

    // Validate input
    if (empty($video)) {
        $_SESSION["error"] = "Video cannot be empty.";
        header("Location: postvideo.php");
        return;
    }
    if (empty($title)) {
        $_SESSION["error"] = "Title cannot be empty.";
        header("Location: postvideo.php");
        return;
    }
    if (empty($description)) {
        $_SESSION["error"] = "Description cannot be empty.";
        header("Location: postvideo.php");
        return;
    }

    // Handle Update action
    if (isset($_POST['btnupdate'])) {
        if ($vidId) {
            try {
                $sql = "UPDATE video SET vid_name = :title, vid_description = :description, videourl = :video, cat_id = :cat_id WHERE vid_id = :vid_id AND master_id = :master_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':title' => $title, ':description' => $description, ':video' => $video, ':cat_id' => $catId, ':vid_id' => $vidId, ':master_id' => $masterId]);
                $_SESSION["successmsg"] = "Video Updated";
            } catch (Exception $e) {
                $_SESSION['error'] = "Error updating video: " . $e->getMessage();
            }
        } else {
            $_SESSION["error"] = "Invalid video selected for update.";
        }
        header("Location: postvideo.php");
        return;
    }
    // Handle Delete action
    if (isset($_POST['btndelete'])) {
        if ($vidId) {
            try {
                $sql = "DELETE FROM video WHERE vid_id = :vid_id AND master_id = :master_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':vid_id' => $vidId, ':master_id' => $masterId]);
                $_SESSION["successmsg"] = "Video Deleted";
            } catch (Exception $e) {
                $_SESSION['error'] = "Error deleting video: " . $e->getMessage();
            }
        } else {
            $_SESSION["error"] = "Invalid video selected for deletion.";
        }
        header("Location: postvideo.php");
        return;
    }

    // Handle Add action
    if (isset($_POST['btnpost'])) {
        try {
            $sql = "INSERT INTO video (vid_name, vid_description, videourl, cat_id, master_id) VALUES (:title, :description, :video, :cat_id, :master_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':title' => $title, ':description' => $description, ':video' => $video, ':cat_id' => $catId, ':master_id' => $masterId]);
            $_SESSION["successmsg"] = "Video Added";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error adding video: " . $e->getMessage();
        }
        header("Location: postvideo.php");
        return;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
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
<form id="frmvid" class="row" method="post" enctype="multipart/form-data" style="border: 3px solid #f1f1f1;">
<input type="hidden" name="vid_id">

<div class="container">
    <center><h2>Manage Video</h2></center>
    <label for="txttitle"><b>Title</b></label>
    <input type="text" class="form-control" placeholder="Enter title" name="txtmsg" required>

    <label for="txtdesc"><b>Description</b></label>
    <input type="text" class="form-control" placeholder="Enter description" name="txtdesc" required>

    <label for="category"><b>Category</b></label>
    <select class="form-control" name="category" required>
        <option value="">Select Category</option>
        <?php
        $stmt = $pdo->query("SELECT cat_id, cat_name FROM category");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value='" . htmlentities($row['cat_id']) . "'>" . htmlentities($row['cat_name']) . "</option>";
        }
        ?>
    </select>

    <label for="txtvid"><b>Video</b></label>
    <input type="url" class="form-control" placeholder="Enter Video URL" name="txtvid" required>
    <br>
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
</form><br>

<div class="row mt-3">
    
<table class="table table-dark table-hover table-striped w-75" id="ls">
    <thead>
        <th>Title</th>
        <th>Description</th>
        <th>Video</th>
        <th>Category</th>
        <th>Action</th>
    </thead>
    <tbody>
        <?php
        $stmt = $pdo->prepare("SELECT video.*, category.cat_name FROM video JOIN category ON video.cat_id = category.cat_id WHERE video.master_id = :master_id");
        $stmt->execute([':master_id' => $_SESSION['master_id']]);
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlentities($rows["vid_name"]) . "</td>";
            echo "<td>" . htmlentities($rows["vid_description"]) . "</td>";
            echo "<td>" . htmlentities($rows["videourl"]) . "</td>";
            echo "<td>" . htmlentities($rows["cat_name"]) . "</td>";
            echo "<td>
                <button class='btn btn-info' onclick='selectVideo(" . $rows["vid_id"] . ", `" . addslashes($rows["vid_name"]) . "`, `" . addslashes($rows["vid_description"]) . "`, `" . addslashes($rows["videourl"]) . "`, `" . $rows["cat_id"] . "`)'>Edit</button>
            </td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
</div>

</main>

<script>
function selectVideo(vidId, title, description, video, catId) {
    document.querySelector('input[name="vid_id"]').value = vidId;
    document.querySelector('input[name="txtmsg"]').value = title;
    document.querySelector('input[name="txtdesc"]').value = description;
    document.querySelector('input[name="txtvid"]').value = video;
    document.querySelector('select[name="category"]').value = catId;
    document.getElementById('frmvid').scrollIntoView({ behavior: 'smooth' });
}
function confirmDelete() {
    return confirm('Are you sure you want to delete this video?');
}
</script>

<!-- INCLUDING Footer -->
<?php include '../files/footer.php' ?>
</body>
</html>
