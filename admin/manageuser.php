<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

// Redirect to login if the admin is not logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: adminlogin.php");
    return;
}

// Handle blocking and unblocking actions
if (isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];

    if ($action === 'block') {
        $stmt = $pdo->prepare("UPDATE user SET status = -1 WHERE user_id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "User has been blocked.";
    } elseif ($action === 'unblock') {
        $stmt = $pdo->prepare("UPDATE user SET status = 1 WHERE user_id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "User has been unblocked.";
    }
    header("Location: manageuser.php");
    return;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
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
            <th>User Name</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Fetch all users from the database
        $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, status FROM user");
        $stmt->execute();
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlentities($rows['first_name']) . " " . htmlentities($rows['last_name']) . "</td>";

            // Display current status
            $statusText = $rows['status'] == 1 ? "Active" : ($rows['status'] == -1 ? "Blocked" : "Pending");
            echo "<td>$statusText</td>";

            // Display action buttons based on current status
            echo "<td>";
            if ($rows['status'] == 1) {
                // User is active; show "Block" button
                echo "<a href='?action=block&id=" . $rows['user_id'] . "' class='btn btn-warning btn-sm'>Block</a>";
            } elseif ($rows['status'] == -1) {
                // User is blocked; show "Unblock" button
                echo "<a href='?action=unblock&id=" . $rows['user_id'] . "' class='btn btn-success btn-sm'>Unblock</a>";
            } else {
                // User is pending; show both "Approve" and "Reject" buttons
                echo "<a href='?action=unblock&id=" . $rows['user_id'] . "' class='btn btn-success btn-sm'>Approve</a> ";
                echo "<a href='?action=block&id=" . $rows['user_id'] . "' class='btn btn-danger btn-sm'>Reject</a>";
            }
            echo "</td>";

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
