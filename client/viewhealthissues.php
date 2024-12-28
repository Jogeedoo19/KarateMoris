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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Issues</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?>
</head>
<body>
<?php include '../files/nav.php'; ?>
<br><br><br><br><br><br>
<main>
<div class="row mt-3">
<center><table class="table table-dark table-hover table-striped w-75">
    <thead>
        <tr>
            <th>Description of Health Issues</th>
            <th>Student Name</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $stmt = $pdo->prepare("
            SELECT healthissues.description, 
                   user.first_name, 
                   user.last_name
            FROM healthissues 
            JOIN user ON healthissues.user_id = user.user_id
        ");
        $stmt->execute();
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlentities($rows["description"]) . "</td>";
            echo "<td>" . htmlentities($rows['first_name']) . ' ' . htmlentities($rows['last_name']) . "</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table></center>
</div>
</main>

<?php include '../files/footer.php'; ?>
</body>
</html>