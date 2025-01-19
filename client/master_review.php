<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: login.php");
    return;
}

// Fetch all masters with their average ratings
$stmt = $pdo->prepare("
    SELECT 
        m.*,
        AVG(CAST(r.rate AS DECIMAL(10,1))) as average_rating,
        COUNT(r.review_id) as review_count
    FROM master m
    LEFT JOIN review r ON m.master_id = r.master_id
    GROUP BY m.master_id
");
$stmt->execute();
$masters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's existing reviews
$stmt = $pdo->prepare("SELECT master_id, rate FROM review WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userReviews = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Reviews</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?>
    <!-- Add Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            gap: 0.3rem;
            --star-color: #ddd;
            --star-active-color: #ffb700;
        }

        .star-rating input {
            appearance: none;
            -webkit-appearance: none;
            margin: 0;
            width: 0;
            height: 0;
            position: absolute;
        }

        .star-rating label {
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--star-color);
            transition: color 0.2s ease-in-out;
        }

        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: var(--star-active-color);
        }

        .master-card {
            transition: transform 0.2s;
            border-radius: 15px;
            overflow: hidden;
        }

        .master-card:hover {
            transform: translateY(-5px);
        }

        .profile-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            margin: 1rem auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .average-rating {
            font-size: 2rem;
            font-weight: bold;
            color: var(--star-active-color);
        }

        .review-count {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<?php include '../files/nav.php'; ?>
<br><br><br><br><br>
<main class="container mt-5">
    <h2 class="text-center mb-4">Rate Our Masters</h2>
    
    <div class="row g-4">
        <?php foreach ($masters as $master): ?>
        <div class="col-md-4">
            <div class="card master-card shadow">
                <img 
                    src="../client/uploads/<?= htmlentities($master['image'] ?? 'placeholder.jpg') ?>" 
                    alt="<?= htmlentities($master['first_name']) ?>'s profile" 
                    class="profile-image"
                >
                
                <div class="card-body text-center">
                    <h5 class="card-title">
                        <?= htmlentities($master['first_name'] . ' ' . $master['last_name']) ?>
                    </h5>
                    
                    <div class="mb-3">
                        <span class="average-rating">
                            <?= number_format($master['average_rating'] ?? 0, 1) ?>
                        </span>
                        <div class="review-count">
                            <?= $master['review_count'] ?> review<?= $master['review_count'] != 1 ? 's' : '' ?>
                        </div>
                    </div>

                    <form action="submit_review.php" method="post" class="rating-form">
                        <input type="hidden" name="master_id" value="<?= $master['master_id'] ?>">
                        <div class="star-rating mb-3">
                            <?php for($i = 5; $i >= 1; $i--): ?>
                            <input 
                                type="radio" 
                                name="rating" 
                                value="<?= $i ?>" 
                                id="star<?= $master['master_id'] ?>-<?= $i ?>"
                                <?= isset($userReviews[$master['master_id']]) && $userReviews[$master['master_id']] == $i ? 'checked' : '' ?>
                            >
                            <label for="star<?= $master['master_id'] ?>-<?= $i ?>">
                                <i class="fas fa-star"></i>
                            </label>
                            <?php endfor; ?>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <?= isset($userReviews[$master['master_id']]) ? 'Update Rating' : 'Submit Rating' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<?php include '../files/footer.php'; ?>

<script>
document.querySelectorAll('.rating-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        
        try {
            const response = await fetch('submit_review.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update the average rating and review count display
                const card = form.closest('.card');
                card.querySelector('.average-rating').textContent = result.newAverage.toFixed(1);
                card.querySelector('.review-count').textContent = 
                    `${result.reviewCount} review${result.reviewCount !== 1 ? 's' : ''}`;
                
                // Show success message
                alert('Rating submitted successfully!');
            } else {
                alert(result.message || 'Error submitting rating');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error submitting rating');
        }
    });
});
</script>

</body>
</html>