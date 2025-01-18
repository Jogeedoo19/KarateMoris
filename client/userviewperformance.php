<?php
session_start();
require_once "../db/pdo.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get performance data
$query = "SELECT p.stats, p.per_id, c.title as challenge_name, t.status,
          m.first_name as master_name, m.last_name as master_lastname
          FROM performance p
          JOIN tracker t ON p.track_id = t.track_id
          JOIN challenges c ON t.chall_id = c.chall_id
          JOIN master m ON p.master_id = m.master_id
          WHERE t.user_id = :user_id
          ORDER BY p.per_id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $user_id]);
$performances = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get challenge completion stats
$stats_query = "SELECT 
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN status = 'in-progress' THEN 1 END) as in_progress,
                COUNT(CASE WHEN status = 'not-started' OR status IS NULL THEN 1 END) as not_started
                FROM tracker
                WHERE user_id = :user_id";
$stats_stmt = $pdo->prepare($stats_query);
$stats_stmt->execute(['user_id' => $user_id]);
$challenge_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Performance Dashboard</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .performance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stats-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            text-align: left;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: 500;
        }

        .completed { background: #28a745; color: white; }
        .in-progress { background: #ffc107; color: black; }
        .not-started { background: #dc3545; color: white; }
    </style>
</head>
<body>
<?php include_once '../files/nav.php'; ?>

<div class="container mt-5">
    <h2>Performance Dashboard</h2>

    <!-- Challenge Status Overview -->
    <div class="performance-grid">
        <div class="stats-card">
            <h3>Challenge Progress</h3>
            <canvas id="challengeProgress"></canvas>
        </div>

        <div class="stats-card">
            <h3>Recent Performance</h3>
            <canvas id="performanceGraph"></canvas>
        </div>
    </div>

    <!-- Detailed Performance Table -->
    <div class="chart-container">
        <h3>Performance History</h3>
        <table class="performance-table">
            <thead>
                <tr>
                    <th>Challenge</th>
                    <th>Performance</th>
                    <th>Status</th>
                    <th>Master</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($performances as $perf): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($perf['challenge_name']); ?></td>
                        <td><?php echo htmlspecialchars($perf['stats']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $perf['status']; ?>">
                                <?php echo ucfirst($perf['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($perf['master_name'] . ' ' . $perf['master_lastname']); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Challenge Progress Pie Chart
const progressCtx = document.getElementById('challengeProgress').getContext('2d');
new Chart(progressCtx, {
    type: 'doughnut',
    data: {
        labels: ['Completed', 'In Progress', 'Not Started'],
        datasets: [{
            data: [
                <?php echo $challenge_stats['completed']; ?>,
                <?php echo $challenge_stats['in_progress']; ?>,
                <?php echo $challenge_stats['not_started']; ?>
            ],
            backgroundColor: ['#28a745', '#ffc107', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Performance Line Graph
const performanceData = <?php 
    $chartData = array_map(function($perf) {
        return ['challenge' => $perf['challenge_name'], 'performance' => intval($perf['stats'])];
    }, array_slice($performances, 0, 5)); // Get last 5 performances
    echo json_encode(array_reverse($chartData));
?>;

const perfCtx = document.getElementById('performanceGraph').getContext('2d');
new Chart(perfCtx, {
    type: 'line',
    data: {
        labels: performanceData.map(item => item.challenge),
        datasets: [{
            label: 'Performance Score',
            data: performanceData.map(item => item.performance),
            borderColor: '#4e73df',
            tension: 0.1,
            fill: false
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                suggestedMax: 100
            }
        },
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include '../files/footer.php'; ?>
</body>
</html>