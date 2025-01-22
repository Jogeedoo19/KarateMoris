<?php
session_start();
require_once "../db/pdo.php";

// Check if master is logged in
if (!isset($_SESSION['master_id'])) {
    header("Location: masterlogin.php");
    exit();
}

$master_id = $_SESSION['master_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_performance'])) {
        try {
            $stats = $_POST['stats'];
            $track_id = $_POST['track_id'];
            
            $stmt = $pdo->prepare("INSERT INTO performance (stats, track_id, master_id) VALUES (:stats, :track_id, :master_id)");
            $stmt->execute([
                ':stats' => $stats,
                ':track_id' => $track_id,
                ':master_id' => $master_id
            ]);
            
            $_SESSION['success'] = "Performance updated successfully";
        } catch(Exception $e) {
            $_SESSION['error'] = "Error updating performance: " . $e->getMessage();
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Get all students and their challenges with tracker info
$query = "SELECT u.user_id, u.first_name, u.last_name, 
                 c.chall_id, c.title as challenge_title,
                 t.track_id, t.status,
                 p.stats, p.per_id
          FROM challenges c
          JOIN tracker t ON c.chall_id = t.chall_id
          JOIN user u ON t.user_id = u.user_id
          LEFT JOIN performance p ON t.track_id = p.track_id AND p.master_id = :master_id
          WHERE c.master_id = :master_id
          ORDER BY u.last_name, u.first_name, c.title";

$stmt = $pdo->prepare($query);
$stmt->execute(['master_id' => $master_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Performance</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?>
    <style>
        .performance-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        
        .performance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .performance-table th,
        .performance-table td {
            padding: 12px;
            border: 1px solid #dee2e6;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
        }
        
        .search-box {
            margin-bottom: 20px;
        }
        
        .stats-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        
        .current-stats {
            font-style: italic;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<?php include_once '../files/nav.php'; ?>
<br><br> <br><br>
<div class="container mt-5">
    <h2>Student Performance Tracking</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success']; 
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error']; 
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="performance-card">
        <div class="search-box">
            <input type="text" class="form-control" id="searchInput" 
                   placeholder="Search by student name or challenge..." onkeyup="searchTable()">
        </div>

        <table class="performance-table" id="ls">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Challenge</th>
                    <th>Status</th>
                    <th>Performance Stats</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                <tr class="performance-row">
                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['challenge_title']); ?></td>
                    <td>
                        <span class="status-badge <?php echo $row['status']; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($row['stats']): ?>
                            <div class="current-stats">
                                Current: <?php echo htmlspecialchars($row['stats']); ?>
                            </div>
                        <?php endif; ?>
                        <form method="post" class="mt-2">
                            <input type="hidden" name="track_id" value="<?php echo $row['track_id']; ?>">
                            <div class="input-group">
                                <input type="text" name="stats" class="form-control stats-input" 
                                       placeholder="Enter performance stats" required>
                                <button type="submit" name="submit_performance" class="btn btn-primary">
                                    Update
                                </button>
                            </div>
                        </form>
                    </td>
                    <td>
                        <?php if ($row['per_id']): ?>
                            <button class="btn btn-secondary" onclick="viewHistory(<?php echo $row['track_id']; ?>)">
                                View History
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const rows = document.getElementsByClassName('performance-row');

    for (let row of rows) {
        const studentName = row.cells[0].textContent.toLowerCase();
        const challengeName = row.cells[1].textContent.toLowerCase();
        const shouldShow = studentName.includes(filter) || challengeName.includes(filter);
        row.style.display = shouldShow ? '' : 'none';
    }
}

function viewHistory(trackId) {
    // You can implement a modal or redirect to show performance history
    alert('Feature coming soon: View performance history');
}
</script>

<?php include '../files/footer.php'; ?>
</body>
</html>