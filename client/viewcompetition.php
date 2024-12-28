<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

// Fetch all notes with their associated categories and masters
$stmt = $pdo->prepare("
    SELECT 
        competition.*, 
    
        master.first_name, 
        master.last_name, 
        master.image 
    FROM competition 
   
    JOIN master ON competition.master_id = master.master_id
");
$stmt->execute();
$competitions = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <h2 class="text-center mb-4">competition</h2>

    <!-- Display Notes in Cards -->
    <div class="row">
    <?php foreach ($competitions as $competition): ?>
    <div class="col-md-4">
        <div class="card mb-4 shadow-sm">
            <!-- Card Content -->
            <div class="card-body">
                <?php
                $fileExtension = pathinfo($competition['com_image'], PATHINFO_EXTENSION);

                if (in_array(strtolower($fileExtension), ['pdf', 'txt', 'docx', 'doc'])): ?>
                    <!-- PDF or Text Document -->
                    <embed 
                        src="../upload/<?= htmlentities($competition['com_image']) ?>" 
                        type="application/pdf" 
                        class="w-100" 
                        height="200">
                <?php elseif (in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                    <!-- Image Document -->
                    <img 
                        src="../upload/<?= htmlentities($competition['com_image']) ?>" 
                        alt="Document Image" 
                        class="w-100" 
                        style="height: 200px; object-fit: cover;">
                <?php else: ?>
                    <!-- Unsupported Document Format -->
                    <p class="text-muted">Document preview not available. <a href="../upload/<?= htmlentities($competition['com_image']) ?>" target="_blank">View Document</a></p>
                <?php endif; ?>

                <h5 class="card-title"><?= htmlentities($competition['com_name']) ?></h5>
                <p class="card-text"> <?= htmlentities($competition['com_description']) ?></p>
              
                <button type="button" class="btn btn-sm btn-outline-primary"><a href="">Sign Up</a></button>
            </div>

            <!-- Footer -->
            <div class="card-footer d-flex align-items-center bg-light">
                <img 
                    src="../client/uploads/<?= htmlentities($competition['image'] ?? 'placeholder.jpg') ?>" 
                    alt="Profile Image" 
                    class="rounded-circle me-2" 
                    width="40" 
                    height="40">
                <span class="text-muted">
                    Posted by <?= htmlentities($competition['first_name']) . ' ' . htmlentities($competition['last_name']) ?>
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
