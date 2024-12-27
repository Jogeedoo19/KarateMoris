<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: login.php");
    return;
}

$userId = $_SESSION['user_id'];

// Debugging: Ensure `user_id` is set
if (!$userId) {
    die("Error: User ID is missing from the session.");
}

// Handle Cancel button
if (isset($_POST['btncancel'])) {
    header("Location: posthealthissues.php");
    return;
}

// Handle Add/Update/Delete actions
if (isset($_POST['txtmsg'])) {
    $healthissuesId = $_POST['hissues_id'] ?? null;
    $message = $_POST['txtmsg'];

    // Validate input
    if (empty($message)) {
        $_SESSION["error"] = "Description cannot be empty.";
        header("Location: posthealthissues.php");
        return;
    }


    // Handle Update action
    if (isset($_POST['btnupdate'])) {
        if ($healthissuesId) {
            try {
                $sql = "UPDATE healthissues SET description = :message WHERE hissues_id = :hissues_id AND user_id = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':message' => $message, ':hissues_id' => $healthissuesId, ':user_id' => $userId]);
                $_SESSION["successmsg"] = "Health issues Updated";
            } catch (Exception $e) {
                $_SESSION['error'] = "Error updating health issues: " . $e->getMessage();
            }
        } else {
            $_SESSION["error"] = "Invalid health issues selected for update.";
        }
        header("Location: posthealthissues.php");
        return;
    }
    // Handle Delete action
    if (isset($_POST['btndelete'])) {
        if ($healthissuesId) {
            try {
                $sql = "DELETE FROM healthissues WHERE hissues_id = :hissues_id AND user_id = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':hissues_id' => $healthissuesId, ':user_id' => $userId]);
                $_SESSION["successmsg"] = "Health issues Deleted";
            } catch (Exception $e) {
                $_SESSION['error'] = "Error deleting health issues: " . $e->getMessage();
            }
        } else {
            $_SESSION["error"] = "Invalid health issues selected for deletion.";
        }
        header("Location: posthealthissues.php");
        return;
    }

    // Handle Add action
    if (isset($_POST['btnpost'])) {
        try {
            $sql = "INSERT INTO healthissues (description, user_id) VALUES (:message, :user_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':message' => $message, ':user_id' => $userId]);
            $_SESSION["successmsg"] = "Health issues Added";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error adding health issues: " . $e->getMessage();
        }
        header("Location: posthealthissues.php");
        return;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Testimonial</title>
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
<form id="frmhissues" class="row" method="post" enctype="multipart/form-data" style="border: 3px solid #f1f1f1;">
<input type="hidden" name="hissues_id"> <!-- Hidden field for testimonial ID -->


<div class="container">
    <center><h2>Post Health Issues</h2></center>
<label for="txtmsg"><b>Description of Health Issues</b></label>
<input type="text" placeholder="Enter name" name="txtmsg" required>



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
<table class="table table-dark table-hover table-striped w-75">
    <thead>
        <th>Description of Health Issues</th>
        <th>Action</th>
    </thead>
    <tbody>
        <?php
        $stmt = $pdo->prepare("SELECT * FROM healthissues WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlentities($rows["description"]) . "</td>";
            echo "<td>
                <button class='btn btn-info' onclick='selectHealthIssues(" . $rows["hissues_id"] . ", `" . addslashes($rows["description"]) . "`)'>Edit</button>
            </td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
</div>


</main>
<script>
function selectHealthIssues($healthissuesId, message) {
    // Populate the hidden test_id field
    document.querySelector('input[name="hissues_id"]').value = $healthissuesId;

    // Populate the txtmsg field
    document.querySelector('input[name="txtmsg"]').value = message;

    // Optional: Scroll to the form or highlight it
    document.getElementById('frmhissues').scrollIntoView({ behavior: 'smooth' });
}
function confirmDelete() {
    return confirm('Are you sure you want to delete this health issues?');
}
</script>


<!-- INCLUDING Footer -->
<?php include '../files/footer.php' ?>


    
</body>
</html>
    
</body>
</html>