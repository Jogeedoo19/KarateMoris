<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: login.php");
    return;
}

$userId = $_SESSION['user_id'];


// Fetch all videos with their associated categories and masters
$stmt = $pdo->prepare("
    SELECT 
        video.*, 
        category.cat_name, 
        master.first_name, 
        master.last_name, 
        master.image 
    FROM video 
    JOIN category ON video.cat_id = category.cat_id 
    JOIN master ON video.master_id = master.master_id
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
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">

    <?php include '../files/csslib.php' ?> <!-- Include Bootstrap and other libraries -->
    <style>
.video-card {
    transition: all 0.3s ease;
}
.form-select:focus, .form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}
</style>
</head>
<body>

<!-- Include navigation -->
<?php include '../files/nav.php' ?>
<br><br><br><br>
<main class="main container mt-5">
    <h2 class="text-center mb-4">Available Videos</h2>

    <div class="row mb-4">
    <div class="col-md-6 mx-auto">
        <div class="input-group">
            <input 
                type="text" 
                id="categorySearch" 
                class="form-control" 
                placeholder="Search by category..."
                aria-label="Search by category">
            <select id="categoryFilter" class="form-select" style="max-width: 200px;">
                <option value="">All Categories</option>
                <?php
                $stmt = $pdo->query("SELECT DISTINCT cat_name FROM category ORDER BY cat_name");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='" . htmlentities($row['cat_name']) . "'>" . 
                         htmlentities($row['cat_name']) . "</option>";
                }
                ?>
            </select>
        </div>
    </div>
</div>
    <!-- Display Videos in Cards -->
    <div class="row" id="videoContainer">
    <?php foreach ($videos as $video): 
                $embed_url = getYoutubeEmbedUrl($video['videourl'] ?? '');
                ?>
            <div class="col-md-4 video-card" data-category="<?= htmlentities($video['cat_name']) ?>">
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
    <!-- Pagination Controls -->
<div class="w3-center w3-padding-16">
    <div class="w3-bar" id="pagination"></div>
</div>
</main>



<!-- Include Footer -->
<?php include '../files/footer.php' ?>
<!-- Add any JavaScript needed -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySearch = document.getElementById('categorySearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const videoCards = document.querySelectorAll('.video-card');

    function filterVideos() {
        const searchTerm = categorySearch.value.toLowerCase();
        const selectedCategory = categoryFilter.value.toLowerCase();

        videoCards.forEach(card => {
            const category = card.dataset.category.toLowerCase();
            const matchesSearch = category.includes(searchTerm);
            const matchesFilter = !selectedCategory || category === selectedCategory;
            
            if (matchesSearch && matchesFilter) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });

        // Show "no results" message if all cards are hidden
        const visibleCards = document.querySelectorAll('.video-card[style=""]').length;
        let noResultsMsg = document.getElementById('noResultsMessage');
        
        if (visibleCards === 0) {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.id = 'noResultsMessage';
                noResultsMsg.className = 'col-12 text-center my-5';
                noResultsMsg.innerHTML = '<p class="text-muted">No videos found matching your search.</p>';
                document.getElementById('videoContainer').appendChild(noResultsMsg);
            }
        } else if (noResultsMsg) {
            noResultsMsg.remove();
        }
    }

    // Event listeners for both search input and dropdown
    categorySearch.addEventListener('input', filterVideos);
    categoryFilter.addEventListener('change', filterVideos);
    // Handle iframe loading errors
    document.querySelectorAll('iframe').forEach(iframe => {
        iframe.onerror = function() {
            this.style.display = 'none';
            this.parentElement.innerHTML = '<p class="text-center text-muted py-5">Video cannot be loaded</p>';
        };
    });
});
</script>
<script src="../js/pagination.js"></script>
</body>
</html>
