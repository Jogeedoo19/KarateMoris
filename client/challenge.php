<?php
session_start();
require_once "../db/pdo.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get all challenges
$query = "SELECT c.*, t.status as challenge_status 
          FROM challenges c 
          LEFT JOIN tracker t ON c.chall_id = t.chall_id AND t.user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $user_id]);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Karate Challenges</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?>
    <style>
        .challenge-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        
        .challenge-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .challenge-card:hover {
            transform: translateY(-5px);
        }

        .challenge-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .exercise-list {
            display: none;
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        
        .exercise-item {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            gap: 15px;
        }

        .exercise-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 5px;
        }

        .exercise-content {
            flex: 1;
        }
        
        .status-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn-progress {
            background: #ffc107;
            color: black;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .btn-completed {
            background: #28a745;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .not-started { background: #dc3545; color: white; }
        .in-progress { background: #ffc107; color: black; }
        .completed { background: #28a745; color: white; }
    </style>
</head>
<body>
<?php include_once '../files/nav.php'; ?>

<br><br><br><br><br><br>
<div class="container mt-5">
    <div class="challenge-container">
        <?php while($challenge = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="challenge-card" onclick="toggleExercises('exercises<?php echo $challenge['chall_id']; ?>')">
                <img src="../upload/<?php echo htmlspecialchars($challenge['image']); ?>" 
                     alt="<?php echo htmlspecialchars($challenge['title']); ?>" 
                     class="challenge-image">
                <h3><?php echo htmlspecialchars($challenge['title']); ?>
                    <?php if($challenge['challenge_status']): ?>
                        <span class="status-badge <?php echo $challenge['challenge_status']; ?>">
                            <?php echo ucfirst($challenge['challenge_status']); ?>
                        </span>
                    <?php endif; ?>
                </h3>
                <p><?php echo htmlspecialchars($challenge['description']); ?></p>
                <div id="exercises<?php echo $challenge['chall_id']; ?>" class="exercise-list">
                    <?php 
                    // Get exercises for this challenge
                    $exercise_query = "SELECT * FROM challenge_exercises 
                                     WHERE chall_id = :chall_id 
                                     ORDER BY exercise_number";
                    $exercise_stmt = $pdo->prepare($exercise_query);
                    $exercise_stmt->execute(['chall_id' => $challenge['chall_id']]);
                    
                    while($exercise = $exercise_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="exercise-item">
                            <img src="../upload/<?php echo htmlspecialchars($exercise['exercise_image']); ?>" 
                                 alt="Exercise Image" 
                                 class="exercise-image">
                            <div class="exercise-content">
                                <h5>Exercise <?php echo $exercise['exercise_number']; ?></h5>
                                <h6><?php echo htmlspecialchars($exercise['exercise_title']); ?></h6>
                                <p><?php echo htmlspecialchars($exercise['exercise_description']); ?></p>
                                
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="status-buttons">
                                    <button class="btn-progress" onclick="event.stopPropagation(); updateStatus(<?php echo $challenge['chall_id']; ?>, 'in-progress')">
                                        Mark In Progress
                                    </button>
                                    <button class="btn-completed" onclick="event.stopPropagation(); updateStatus(<?php echo $challenge['chall_id']; ?>, 'completed')">
                                        Mark Complete
                                    </button>
                                </div>
            </div>
            
        <?php endwhile; ?>
    </div>
</div>

<script>
function toggleExercises(elementId) {
    const exerciseList = document.getElementById(elementId);
    if (exerciseList.style.display === 'none' || exerciseList.style.display === '') {
        exerciseList.style.display = 'block';
    } else {
        exerciseList.style.display = 'none';
    }
}

function updateStatus(challengeId, status) {
    fetch('update_progress.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `challenge_id=${challengeId}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload();
        } else {
            alert('Error updating status');
        }
    });
}
</script>

<?php include '../files/footer.php'; ?>
</body>
</html>