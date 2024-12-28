<?php
require_once "../db/pdo.php";
require_once "../db/util.php";


// Fetch all videos with their associated categories and masters
$stmt = $pdo->prepare("
    SELECT 
        video.*, 
        category.cat_name, 
        user.first_name, 
        user.last_name, 
        user.image 
    FROM video 
    JOIN category ON video.cat_id = category.cat_id 
    JOIN user ON video.master_id = user.user_id
");
$stmt->execute();
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php' ?> <!-- Include Bootstrap and other libraries -->
</head>
<body>

<!-- Include navigation -->
<?php include '../files/nav.php' ?>

<main class="main container mt-5">
    <h2 class="text-center mb-4">Available Videos</h2>

    <!-- Display Videos in Cards -->
    <div class="row">
    <?php foreach ($videos as $video): 
                $embed_url = getYoutubeEmbedUrl($video['videourl'] ?? '');
                ?>
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm">
                    <!-- Video -->
                    <div class="card-body p-0">
                    <?php if (!empty($embed_url)): ?>
                        <iframe 
                            class="w-100" 
                            height="200" 
                           src="<?= htmlentities($embed_url) ?>"
                            title="Video Player" 
                            frameborder="0" 
                            allowfullscreen>
                        </iframe>
                        <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center h-100 bg-light" style="min-height: 200px;">
                                        <p class="text-muted">Video not available</p>
                                    </div>
                                <?php endif; ?>
                    </div>

                    <!-- Card Content -->
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlentities($video['vid_name']) ?></h5>
                        <p class="card-text"><?= htmlentities($video['vid_description']) ?></p>
                        <p class="text-muted">Category: <?= htmlentities($video['cat_name']) ?></p>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer d-flex align-items-center bg-light">
                        <img 
                            src="../client/uploads/<?= htmlentities($video['image'] ?? 'placeholder.jpg') ?>" 
                            alt="Profile Image" 
                            class="rounded-circle me-2" 
                            width="40" 
                            height="40">
                        <span class="text-muted">
                            Posted by <?= htmlentities($video['first_name']) . ' ' . htmlentities($video['last_name']) ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>



<!-- Include Footer -->
<?php include '../files/footer.php' ?>
<!-- Add any JavaScript needed -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle iframe loading errors
    document.querySelectorAll('iframe').forEach(iframe => {
        iframe.onerror = function() {
            this.style.display = 'none';
            this.parentElement.innerHTML = '<p class="text-center text-muted py-5">Video cannot be loaded</p>';
        };
    });
});
</script>

</body>
</html>
