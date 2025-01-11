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
    <?php include '../files/csslib.php'; ?> <!-- Include Bootstrap and other libraries -->
</head>
<body>

<!-- Include navigation -->
<?php include '../files/nav.php'; ?>

<main class="main container mt-5">
    <h2 class="text-center mb-4">Notes</h2>

    <!-- Display Notes in Cards -->
    <div class="row">
    <?php foreach ($notes as $note): ?>
    <div class="col-md-4">
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
</main>

<!-- Include Footer -->
<?php include '../files/footer.php'; ?>
</body>
</html>
