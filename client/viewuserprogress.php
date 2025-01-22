<?php
session_start();
require_once "../db/pdo.php";

// Check if master is logged in
if (!isset($_SESSION['master_id'])) {
    header("Location: masterlogin.php");
    exit();
}

$master_id = $_SESSION['master_id'];

// Get challenges created by this master
$query = "SELECT c.chall_id, c.title, c.description, u.user_id, u.first_name, u.last_name, t.status 
          FROM challenges c 
          LEFT JOIN tracker t ON c.chall_id = t.chall_id 
          LEFT JOIN user u ON t.user_id = u.user_id
          WHERE c.master_id = :master_id 
          ORDER BY c.chall_id, u.last_name, u.first_name";

$stmt = $pdo->prepare($query);
$stmt->execute(['master_id' => $master_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize data by challenge
$challenges = [];
foreach ($results as $row) {
    if (!isset($challenges[$row['chall_id']])) {
        $challenges[$row['chall_id']] = [
            'title' => $row['title'],
            'description' => $row['description'],
            'students' => []
        ];
    }
    if ($row['user_id']) {  // Only add if there's a student
        $challenges[$row['chall_id']]['students'][] = [
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'status' => $row['status'] ?? 'not-started'
        ];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Progress</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?>
    <style>
        .progress-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .progress-header {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .progress-body {
            padding: 0;
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-table th,
        .student-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .student-table tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: 500;
        }

        .not-started {
            background-color: #dc3545;
            color: white;
        }

        .in-progress {
            background-color: #ffc107;
            color: black;
        }

        .completed {
            background-color: #28a745;
            color: white;
        }

        .progress-stats {
            display: flex;
            gap: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        .stat-item {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            background: white;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<?php include_once '../files/nav.php'; ?>
<br><br> <br><br>
<div class="container mt-5">
    <h2>Student Progress Tracking</h2>
    
    <div class="search-box">
        <input type="text" class="search-input" id="searchInput" 
               placeholder="Search by student name..." onkeyup="searchStudents()">
    </div>

    <?php foreach ($challenges as $chall_id => $challenge): ?>
        <div class="progress-card">
            <div class="progress-header">
                <h3><?php echo htmlspecialchars($challenge['title']); ?></h3>
                <p><?php echo htmlspecialchars($challenge['description']); ?></p>
            </div>

            <div class="progress-body">
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($challenge['students'])): ?>
                            <tr>
                                <td colspan="3" class="text-center">No students enrolled yet</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($challenge['students'] as $student): ?>
                                <tr class="student-row">
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $student['status']; ?>">
                                            <?php echo ucfirst($student['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        // You can add last updated timestamp if you add it to your tracker table
                                        echo "Recent"; 
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="progress-stats">
                <?php 
                $total = count($challenge['students']);
                $completed = count(array_filter($challenge['students'], 
                    function($s) { return $s['status'] === 'completed'; }));
                $inProgress = count(array_filter($challenge['students'], 
                    function($s) { return $s['status'] === 'in-progress'; }));
                $notStarted = $total - $completed - $inProgress;
                ?>
                <div class="stat-item">
                    <h4><?php echo $completed; ?></h4>
                    <p>Completed</p>
                </div>
                <div class="stat-item">
                    <h4><?php echo $inProgress; ?></h4>
                    <p>In Progress</p>
                </div>
                <div class="stat-item">
                    <h4><?php echo $notStarted; ?></h4>
                    <p>Not Started</p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
function searchStudents() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const rows = document.getElementsByClassName('student-row');

    for (let row of rows) {
        const name = row.cells[0].textContent.toLowerCase();
        row.style.display = name.includes(filter) ? '' : 'none';
    }
}
</script>

<?php include '../files/footer.php'; ?>
</body>
</html>