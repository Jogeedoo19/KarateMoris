<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();
checkMasterAuth();

// Redirect to login if user is not logged in
if (!isset($_SESSION['master_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: masterlogin.php");
    return;
}

$masterId = $_SESSION['master_id'];

// Handle Cancel button
if (isset($_POST['btncancel'])) {
    header("Location: postnotes.php");
    return;
}

// Handle Add/Update/Delete actions
if (isset($_POST['txttitle'])) {
    $notesId = $_POST['notes_id'] ?? null;
    $title = $_POST['txttitle'];
    $description = $_POST['txtdesc'];
    $catId = $_POST['category'];
    $fileUploaded = !empty($_FILES['txtnotes']['name']);

    // Validate input
    if (empty($title) || empty($description) || (!$fileUploaded && !$notesId)) {
        $_SESSION["error"] = "All fields are required.";
        header("Location: postnotes.php");
        return;
    }

    // Allowed file types
    $allowedFileTypes = [
        'pdf' => 'application/pdf',
        'txt' => 'text/plain',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    // Handle File Upload
    $filePath = null;
    if ($fileUploaded) {
        $fileName = basename($_FILES['txtnotes']['name']);
        $fileType = mime_content_type($_FILES['txtnotes']['tmp_name']); // Get MIME type
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION); // Get file extension

        // Validate file type
        if (!array_key_exists($fileExtension, $allowedFileTypes) || $fileType !== $allowedFileTypes[$fileExtension]) {
            $_SESSION["error"] = "Unsupported file type. Allowed types are: " . implode(", ", array_keys($allowedFileTypes));
            header("Location: postnotes.php");
            return;
        }

        // Save the file to the server
        $targetDir = "../upload/";
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['txtnotes']['tmp_name'], $targetFile)) {
            $filePath = $fileName; // Save the file name to the database
        } else {
            $_SESSION["error"] = "Error uploading file.";
            header("Location: postnotes.php");
            return;
        }
    }

    // Handle Update action
    if (isset($_POST['btnupdate']) && $notesId) {
        try {
            $sql = "UPDATE notes 
                    SET notes_name = :title, notes_description = :description, cat_id = :cat_id 
                    " . ($filePath ? ", notes = :notes" : "") . "
                    WHERE notes_id = :notes_id AND master_id = :master_id";
            $params = [
                ':title' => $title,
                ':description' => $description,
                ':cat_id' => $catId,
                ':notes_id' => $notesId,
                ':master_id' => $masterId,
            ];
            if ($filePath) {
                $params[':notes'] = $filePath;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $_SESSION["successmsg"] = "Note Updated";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error updating note: " . $e->getMessage();
        }
        header("Location: postnotes.php");
        return;
    }

    // Handle Delete action
    if (isset($_POST['btndelete']) && $notesId) {
        try {
            $sql = "DELETE FROM notes WHERE notes_id = :notes_id AND master_id = :master_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':notes_id' => $notesId, ':master_id' => $masterId]);
            $_SESSION["successmsg"] = "Note Deleted";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error deleting note: " . $e->getMessage();
        }
        header("Location: postnotes.php");
        return;
    }

    // Handle Add action
    if (isset($_POST['btnpost'])) {
        try {
            $sql = "INSERT INTO notes (notes_name, notes_description, notes, cat_id, master_id) 
                    VALUES (:title, :description, :notes, :cat_id, :master_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':notes' => $filePath,
                ':cat_id' => $catId,
                ':master_id' => $masterId
            ]);
            $_SESSION["successmsg"] = "Note Added";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error adding note: " . $e->getMessage();
        }
        header("Location: postnotes.php");
        return;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notes</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php' ?>
</head>
<body>
<?php include_once '../files/nav.php' ?>
<br><br><br><br><br><br>
<main class="main">
<h3><?php flashMessages(); ?></h3>
<div class="container col-xl-4 col-lg-6">
<form id="frmnote" class="row" method="post" enctype="multipart/form-data" style="border: 3px solid #f1f1f1;">
<input type="hidden" name="notes_id">
<div class="container">
    <center><h2>Manage Note</h2></center>
    <label for="txttitle"><b>Title</b></label>
    <input type="text" class="form-control" placeholder="Enter title" name="txttitle" required>

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

    <label for="txtnotes"><b>Note</b></label>
    <input type="file" class="form-control" name="txtnotes">
    <span id="fileLabel" style="display: block; margin-top: 5px; color: #555;">No file selected</span>
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
</form>
<br>
<div class="row mt-3">
<table class="table table-dark table-hover table-striped w-75">
    <thead>
        <th>Title</th>
        <th>Description</th>
        <th>Note</th>
        <th>Category</th>
        <th>Action</th>
    </thead>
    <tbody>
        <?php
        $stmt = $pdo->prepare("SELECT notes.*, category.cat_name FROM notes JOIN category ON notes.cat_id = category.cat_id WHERE notes.master_id = :master_id");
        $stmt->execute([':master_id' => $_SESSION['master_id']]);
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlentities($rows["notes_name"]) . "</td>";
            echo "<td>" . htmlentities($rows["notes_description"]) . "</td>";
            echo "<td>" . htmlentities($rows["notes"]) . "</td>";
            echo "<td>" . htmlentities($rows["cat_name"]) . "</td>";
            echo "<td>
                <button class='btn btn-info' onclick='selectNotes(" . $rows["notes_id"] . ", `" 
                    . addslashes($rows["notes_name"]) . "`, `" 
                    . addslashes($rows["notes_description"]) . "`, `" 
                    . addslashes($rows["notes"]) . "`, `" 
                    . $rows["cat_id"] . "`)'>Edit</button>
            </td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
</div>
</main>
<script>
function selectNotes(notesId, title, description, notes, catId) {
    document.querySelector('input[name="notes_id"]').value = notesId;
    document.querySelector('input[name="txttitle"]').value = title;
    document.querySelector('input[name="txtdesc"]').value = description;

    // Display file name for the file field
    const fileLabel = document.getElementById('fileLabel');
    fileLabel.textContent = `Selected File: ${notes}`;

    // Set category selection
    document.querySelector('select[name="category"]').value = catId;

    document.getElementById('frmnote').scrollIntoView({ behavior: 'smooth' });
}
function confirmDelete() {
    return confirm('Are you sure you want to delete this note?');
}
</script>
<?php include '../files/footer.php' ?>
</body>
</html>
