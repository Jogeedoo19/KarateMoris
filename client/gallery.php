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
    <?php include '../files/csslib.php'; ?> <!-- Include Bootstrap and other libraries -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 2rem;
        }

        .gallery-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: white;
            aspect-ratio: 1;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .gallery-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .gallery-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .gallery-header p {
            color: #666;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
                padding: 15px;
            }

            .gallery-header h1 {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 1rem;
            }

            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 10px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Include navigation -->
<?php include '../files/nav.php'; ?>
<main class="main container mt-5">
    <div class="gallery-container">
        <div class="gallery-header">
            
            <p>Explore our collection of beautiful images</p>
        </div>
        
        <div class="gallery-grid">
            <?php foreach ($galleries as $gallery): ?>
                <div class="gallery-item">
                    <img src="../upload/<?php echo htmlspecialchars($gallery['image']); ?>" 
                         alt="<?php echo htmlspecialchars($gallery['image']); ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Include Footer -->
<?php include '../files/footer.php'; ?>
</body>
</html>