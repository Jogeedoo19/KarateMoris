<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

// Handle Cancel button
if (isset($_POST['btncancel'])) {
    // Redirect to dashboard page
    header("Location: category.php");
    return;
}

// Handle Add/Update/Delete actions
if (isset($_POST['txtcat'])) {
    $categoryId = isset($_POST['cat_id']) ? $_POST['cat_id'] : null; // Match hidden input field
    $categoryName = $_POST['txtcat'];

    // Validate category name
    $msg = validateCategory();
    if (is_string($msg)) {
        $_SESSION["error"] = "$msg <br/>";
        header("Location: category.php");
        return;
    }

    // Handle Update action
    if (isset($_POST['btnupdate'])) {
        if ($categoryId) {
            $sql = "UPDATE category SET cat_name = :name WHERE cat_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':name' => $categoryName, ':id' => $categoryId]);
            $_SESSION["successmsg"] = "Category Updated";
        } else {
            $_SESSION["error"] = "Invalid category selected for update.";
        }
        header("Location: category.php");
        return;
    }

    // Handle Delete action
    if (isset($_POST['btndelete'])) {
        if ($categoryId) {
            $sql = "DELETE FROM category WHERE cat_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $categoryId]);
            $_SESSION["successmsg"] = "Category Deleted";
        } else {
            $_SESSION["error"] = "Invalid category selected for deletion.";
        }
        header("Location: category.php");
        return;
    }

    // Handle Add action
    if (isset($_POST['btnpost'])) {
        $sql = "INSERT INTO category (cat_name) VALUES (:name)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':name' => $categoryName]);
        $_SESSION["successmsg"] = "Category Added";
        header("Location: category.php");
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
    <?php include '../files/admincsslib.php' ?> <!-- including libraries -->
</head>
<body>

<!-- including header and banner -->
<?php include_once '../files/adminsidebar.php' ?>

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
    <input type="hidden" name="cat_id" id="cat_id">
    
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
<table class="table table-dark table-hover table-striped w-75" id="ls">
    <thead>
        <th>Category Name</th>
        <th>Action</th>
    </thead>
    <tbody>
        <?php
        // Fetch categories
        $stmt = $pdo->query("SELECT * FROM category");
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlentities($rows["cat_name"]) . "</td>";
            echo "<td>
                <button class='btn btn-info' onclick='selectCategory(" . $rows["cat_id"] . ", `" . addslashes($rows["cat_name"]) . "`)'>Edit</button>
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
function selectCategory(id, name) {
    document.getElementById('cat_id').value = id;
    document.getElementById('txtcat').value = name;
}
</script>

<!-- INCLUDING Footer -->
<?php include '../files/adminfooter.php' ?>

</body>
</html>
