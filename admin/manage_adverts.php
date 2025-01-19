<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

// Verify admin access
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Admin access required";
    header("Location: adminlogin.php");
    return;
}

// Handle advertisement approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['advert_id'])) {
    $advertId = $_POST['advert_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $stmt = $pdo->prepare("
            UPDATE advertisement 
            SET status = 1, date_confirm = CURRENT_DATE 
            WHERE advert_id = ?
        ");
    } else if ($action === 'reject') {
        $stmt = $pdo->prepare("
            UPDATE advertisement 
            SET status = 2 
            WHERE advert_id = ?
        ");
    }
    
    if ($stmt->execute([$advertId])) {
        $_SESSION['success'] = "Advertisement " . ($action === 'approve' ? 'approved' : 'rejected');
    } else {
        $_SESSION['error'] = "Error updating advertisement";
    }
    
    header("Location: manage_adverts.php");
    return;
}

// Fetch all advertisements
$stmt = $pdo->prepare("
    SELECT * FROM advertisement 
    ORDER BY date_posted DESC
");
$stmt->execute();
$advertisements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Advertisements</title>
    <?php include '../files/admincsslib.php'; ?>
    <style>
        .advert-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: contain;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include '../files/adminsidebar.php'; ?>

    <main class="container mt-4">
        <h2 class="mb-4">Manage Advertisements</h2>

       

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Logo</th>
                        <th>Company</th>
                        <th>Details</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($advertisements as $ad): ?>
                        <tr>
                            <td>
                                <img src="../upload<?= htmlentities($ad['logopath']) ?>" 
                                     alt="<?= htmlentities($ad['alternatetext']) ?>" 
                                     class="advert-image">
                            </td>
                            <td>
                                <strong><?= htmlentities($ad['company_name']) ?></strong><br>
                                <a href="<?= htmlentities($ad['websiteurl']) ?>" target="_blank">Visit Website</a>
                            </td>
                            <td>
                                <strong>Keywords:</strong> <?= htmlentities($ad['keyword']) ?><br>
                                <strong>Amount:</strong> $<?= htmlentities($ad['amount']) ?>
                            </td>
                            <td>
                                <strong>Months:</strong> <?= htmlentities($ad['numberofmonth']) ?><br>
                                <small>Posted: <?= htmlentities($ad['date_posted']) ?></small><br>
                                <small>Expires: <?= htmlentities($ad['expire_date']) ?></small>
                            </td>
                            <td>
                                <?php
                                $statusClass = '';
                                $statusText = '';
                                switch ($ad['status']) {
                                    case 0:
                                        $statusClass = 'status-pending';
                                        $statusText = 'Pending';
                                        break;
                                    case 1:
                                        $statusClass = 'status-approved';
                                        $statusText = 'Approved';
                                        break;
                                    case 2:
                                        $statusClass = 'status-rejected';
                                        $statusText = 'Rejected';
                                        break;
                                }
                                ?>
                                <span class="status-badge <?= $statusClass ?>">
                                    <?= $statusText ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($ad['status'] == 0): ?>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="advert_id" value="<?= $ad['advert_id'] ?>">
                                        <button type="submit" name="action" value="approve" 
                                                class="btn btn-success btn-sm">Approve</button>
                                        <button type="submit" name="action" value="reject" 
                                                class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include '../files/adminfooter.php'; ?>
</body>
</html>