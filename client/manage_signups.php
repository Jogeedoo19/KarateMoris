<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

// Verify the user is a master
if (!isset($_SESSION['master_id'])) {
    $_SESSION['error'] = "Access denied";
    header("Location: masterlogin.php");
    return;
}

$masterId = $_SESSION['master_id'];

// Fetch all signups for competitions owned by this master
$stmt = $pdo->prepare("
    SELECT 
        signup.*,
        competition.com_name,
        user.first_name as user_first_name,
        user.last_name as user_last_name
    FROM signup
    JOIN competition ON signup.com_id = competition.com_id
    JOIN user ON signup.user_id = user.user_id
    WHERE competition.master_id = ?
");
$stmt->execute([$masterId]);
$signups = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Signups</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?>
</head>
<body>
    <?php include '../files/nav.php'; ?>
    <br><br> <br><br>
    <main class="container mt-5">
        <h2>Manage Competition Signups</h2>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Competition</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($signups as $signup): ?>
                    <tr>
                        <td><?= htmlentities($signup['com_name']) ?></td>
                        <td><?= htmlentities($signup['user_first_name'] . ' ' . $signup['user_last_name']) ?></td>
                        <td><?= htmlentities($signup['status']) ?></td>
                        <td>
                            <?php if ($signup['status'] === 'pending'): ?>
                                <form method="post" action="update_signup.php" style="display: inline;">
                                    <input type="hidden" name="signup_id" value="<?= $signup['s_id'] ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    
    <?php include '../files/footer.php'; ?>
</body>
</html>
