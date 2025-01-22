<?php
require_once "../db/pdo.php";
require_once "../db/util.php";

$stmt = $pdo->prepare("SELECT * FROM gallery");
$stmt->execute();
$galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?>
</head>
<body>
<?php include '../files/nav.php'; ?>
<br><br> <br><br>

<main class="main container mt-5">
    <div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <p style="color: #666; font-size: 1.1rem;">Explore our collection of beautiful images</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; padding: 20px;">
            <?php foreach ($galleries as $gallery): ?>
                <div style="position: relative; overflow: hidden; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.3s ease, box-shadow 0.3s ease; background: white; aspect-ratio: 1; cursor: pointer;" 
                     onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.2)'" 
                     onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)'">
                    <img src="../upload/<?php echo htmlspecialchars($gallery['image']); ?>" 
                         alt="<?php echo htmlspecialchars($gallery['image']); ?>"
                         style="width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.3s ease;"
                         onmouseover="this.style.transform='scale(1.05)'"
                         onmouseout="this.style.transform='none'">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php include '../files/footer.php'; ?>

</body>
</html>