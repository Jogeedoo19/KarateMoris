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
    // Redirect to dashboard page
    header("Location: postmembershipcat.php");
    return;
}

// Handle Add/Update/Delete actions
if (isset($_POST['txtcat'])) {
    $categoryId = isset($_POST['catmember_id']) ? $_POST['catmember_id'] : null; // Match hidden input field
    $categoryName = $_POST['txtcat'];

    // Validate category name
    $msg = validateCategory();
    if (is_string($msg)) {
        $_SESSION["error"] = "$msg <br/>";
        header("Location: postmembershipcat.php");
        return;
    }

    // Handle Update action
    if (isset($_POST['btnupdate'])) {
        if ($categoryId) {
            $sql = "UPDATE categorymember SET catmember_name = :name WHERE catmember_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':name' => $categoryName, ':id' => $categoryId]);
            $_SESSION["successmsg"] = "Category membership Updated";
        } else {
            $_SESSION["error"] = "Invalid category membership selected for update.";
        }
        header("Location: postmembershipcat.php");
        return;
    }

    // Handle Delete action
    if (isset($_POST['btndelete'])) {
        if ($categoryId) {
            $sql = "DELETE FROM categorymember WHERE catmember_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $categoryId]);
            $_SESSION["successmsg"] = "Category membership Deleted";
        } else {
            $_SESSION["error"] = "Invalid category membership selected for deletion.";
        }
        header("Location: postmembershipcat.php");
        return;
    }

    // Handle Add action
    if (isset($_POST['btnpost'])) {
        $sql = "INSERT INTO categorymember (catmember_name) VALUES (:name)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':name' => $categoryName]);
        $_SESSION["successmsg"] = "Category membership Added";
        header("Location: postmembershipcat.php");
        return;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Category</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?> <!-- Including CSS libraries -->
</head>
<body>

<!-- Including header and navigation -->
<?php include_once '../files/nav.php'; ?>

<br><br><br><br><br><br>

<main class="main"><h3> 
<?php
// Call the Flash messaging function
flashMessages();
?>
</h3>

<div class="container col-xl-4 col-lg-6">
<form id="frmadd" class="row" method="post" enctype="multipart/form-data" style="border: 3px solid #f1f1f1;">

<div class="container">
    <center><h2>Manage Category</h2></center>
    
    <!-- Hidden input to store category ID -->
    <input type="hidden" name="catmember_id" id="catmember_id">
    
    <label for="txtcat"><b>Category</b></label>
    <input type="text" placeholder="Enter name" name="txtcat" id="txtcat" required>
</div>

<div class="container" style="background-color:#f1f1f1">
    <center>
        <div>
            <button type="submit" name="btnpost" class="btn btn-primary col-xl-2 pt-2">Add</button>
            <button type="submit" name="btnupdate" class="btn btn-success col-xl-2 pt-2">Update</button>
            <button type="submit" name="btndelete" class="btn btn-danger col-xl-2 pt-2">Delete</button>
            <button type="submit" name="btncancel" class="btn btn-warning col-xl-2 pt-2">Cancel</button>
        </div>
    </center>
</div>
</form>

<div class="row mt-3">
<table class="table table-dark table-hover table-striped w-75">
    <thead>
        <th>Category membership Name</th>
        <th>Action</th>
    </thead>
    <tbody>
        <?php
        // Fetch categories
        $stmt = $pdo->query("SELECT * FROM categorymember");
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlentities($rows["catmember_name"]) . "</td>";
            echo "<td>
                <button class='btn btn-info' onclick='selectCategoryMembership(" . $rows["catmember_id"] . ", `" . addslashes($rows["catmember_name"]) . "`)'>Edit</button>
            </td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
</div>

</main>

<script>
// Set the form fields for update/delete actions
function selectCategoryMembership(id, name) {
    document.getElementById('catmember_id').value = id;
    document.getElementById('txtcat').value = name;
}
</script>


<!-- Including footer -->
<?php include '../files/footer.php'; ?>

</body>
</html>
