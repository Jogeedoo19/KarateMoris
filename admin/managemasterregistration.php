<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: adminlogin.php");
    return;
}

$adminId = $_SESSION['admin_id'];

// Handle approval or rejection actions
if (isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];

    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE master SET status = 1 WHERE master_id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Master registration approved.";
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE master SET status = -1 WHERE master_id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Master registration rejected.";
    }
    header("Location: managemasterregistration.php");
    return;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Master Registration</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/admincsslib.php'; ?>
</head>
<body>
<?php include '../files/adminsidebar.php'; ?>
<main>
<div class="row mt-3">
<center>
<table class="table table-dark table-hover table-striped w-75">
    <thead>
        <tr>
            <th>Master Name</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $stmt = $pdo->prepare("SELECT master_id, first_name, last_name, status FROM master");
        $stmt->execute();
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlentities($rows['first_name']) . ' ' . htmlentities($rows['last_name']) . "</td>";

            // Display current status
            $statusText = $rows['status'] == 0 ? "Pending" : ($rows['status'] == 1 ? "Approved" : "Rejected");
            echo "<td>$statusText</td>";

            // Display action buttons for pending registrations
            if ($rows['status'] == 0) {
                echo "<td>
                    <a href='?action=approve&id=" . $rows['master_id'] . "' class='btn btn-success btn-sm'>Approve</a>
                    <a href='?action=reject&id=" . $rows['master_id'] . "' class='btn btn-danger btn-sm'>Reject</a>
                </td>";
            } else {
                echo "<td>No actions available</td>";
            }

            echo "</tr>";
        }
        ?>
    </tbody>
</table>
</center>
</div>
</main>
<?php include '../files/adminfooter.php'; ?>
</body>
</html>
