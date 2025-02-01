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

// Fetch all notes with their associated categories and masters
$stmt = $pdo->prepare("
    SELECT 
        notes.*, 
        category.cat_name, 
        master.first_name, 
        master.last_name, 
        master.image 
    FROM notes 
    JOIN category ON notes.cat_id = category.cat_id 
    JOIN master ON notes.master_id = master.master_id
");
$stmt->execute();
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <?php include '../files/csslib.php'; ?> <!-- Include Bootstrap and other libraries -->
    <style>
.note-card {
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
<?php include '../files/nav.php'; ?>
<br><br> <br><br>
<main class="main container mt-5">
    <h2 class="text-center mb-4">Notes</h2>

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

    <!-- Display Notes in Cards -->
    <div class="row" id="notesContainer">
    <?php foreach ($notes as $note): ?>
    <div class="col-md-4 note-card" data-category="<?= htmlentities($note['cat_name']) ?>">
        <div class="card mb-4 shadow-sm">
            <!-- Card Content -->
            <div class="card-body">
                <?php
                $fileExtension = pathinfo($note['notes'], PATHINFO_EXTENSION);

                if (in_array(strtolower($fileExtension), ['pdf', 'txt', 'docx', 'doc'])): ?>
                    <!-- PDF or Text Document -->
                    <embed 
                        src="../upload/<?= htmlentities($note['notes']) ?>" 
                        type="application/pdf" 
                        class="w-100" 
                        height="200">
                <?php elseif (in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                    <!-- Image Document -->
                    <img 
                        src="../upload/<?= htmlentities($note['notes']) ?>" 
                        alt="Document Image" 
                        class="w-100" 
                        style="height: 200px; object-fit: cover;">
                <?php else: ?>
                    <!-- Unsupported Document Format -->
                    <p class="text-muted">Document preview not available. <a href="../upload/<?= htmlentities($note['notes']) ?>" target="_blank">View Document</a></p>
                <?php endif; ?>

                <h5 class="card-title"><?= htmlentities($note['notes_name']) ?></h5>
                <p class="card-text"><?= htmlentities($note['notes_description']) ?></p>
                <p class="text-muted">Category: <?= htmlentities($note['cat_name']) ?></p>
                <!-- Download Button -->
                <a href="../upload/<?= htmlentities($note['notes']) ?>" 
                   class="btn btn-primary btn-sm" 
                   download="<?= htmlentities($note['notes']) ?>">Download</a>
            </div>

            <!-- Footer -->
            <div class="card-footer d-flex align-items-center bg-light">
                <img 
                    src="../client/uploads/<?= htmlentities($note['image'] ?? 'placeholder.jpg') ?>" 
                    alt="Profile Image" 
                    class="rounded-circle me-2" 
                    width="40" 
                    height="40">
                <span class="text-muted">
                    Posted by <?= htmlentities($note['first_name']) . ' ' . htmlentities($note['last_name']) ?>
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
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySearch = document.getElementById('categorySearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const noteCards = document.querySelectorAll('.note-card');

    function filterNotes() {
        const searchTerm = categorySearch.value.toLowerCase();
        const selectedCategory = categoryFilter.value.toLowerCase();

        noteCards.forEach(card => {
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
        const visibleCards = document.querySelectorAll('.note-card[style=""]').length;
        let noResultsMsg = document.getElementById('noResultsMessage');
        
        if (visibleCards === 0) {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.id = 'noResultsMessage';
                noResultsMsg.className = 'col-12 text-center my-5';
                noResultsMsg.innerHTML = '<p class="text-muted">No notes found matching your search.</p>';
                document.getElementById('notesContainer').appendChild(noResultsMsg);
            }
        } else if (noResultsMsg) {
            noResultsMsg.remove();
        }
    }

    // Event listeners for both search input and dropdown
    categorySearch.addEventListener('input', filterNotes);
    categoryFilter.addEventListener('change', filterNotes);
});
</script>
</main>
<script src="../js/paginationnote.js"></script>
<!-- Include Footer -->
<?php include '../files/footer.php'; ?>
</body>
</html>
