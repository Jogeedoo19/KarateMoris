<?php
require_once "../db/pdo.php";

$userCount = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
$masterCount = $pdo->query("SELECT COUNT(*) FROM master")->fetchColumn();
$advertCount = $pdo->query("SELECT COUNT(*) FROM advertisement")->fetchColumn();

// Get category counts
$stmt = $pdo->query("SELECT cat_name, COUNT(v.vid_id) as video_count 
                     FROM category c
                     LEFT JOIN video v ON c.cat_id = v.cat_id
                     GROUP BY c.cat_id, cat_name");
$categories = array();
$counts = array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $categories[] = $row['cat_name'];
    $counts[] = $row['video_count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <?php include '../files/admincsslib.php' ?> <!-- including libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- including header and banner -->
<?php include_once '../files/adminsidebar.php' ?>


<main class="content px-3 py-4">
<div class="main-content">



                <div class="container-fluid">
                    <div class="mb-3">
                        <h3 class="fw-bold fs-4 mb-3">Admin Dashboard</h3>



                    <!-- offcanvas -->

<div class="container-fluid">
<div class="row">
<div class="col-md-12"><br />

</div>
</div>
<div class="row">



<div class="col-md-3 mb-3">
<div class="card bg-primary text-white h-100">
  <div class="card-body py-5">Number Of User</div>
  <div class="card-footer d-flex">
  <?= number_format($userCount) ?>
    <span class="ms-auto">
      <i class="bi bi-chevron-right"></i> 
    </span>
  </div>
</div>
</div>
<div class="col-md-3 mb-3">
<div class="card bg-warning text-dark h-100">
  <div class="card-body py-5">Number Of Master</div>
  <div class="card-footer d-flex">
  <?= number_format($masterCount) ?>
    <span class="ms-auto">
      <i class="bi bi-chevron-right"></i> 
    </span>
  </div>
</div>
</div>
<div class="col-md-3 mb-3">
<div class="card bg-success text-white h-100">
  <div class="card-body py-5">Number Of Advert</div>
  <div class="card-footer d-flex">
  <?= number_format($advertCount) ?>
    <span class="ms-auto">
      <i class="bi bi-chevron-right"></i> 
    </span>
  </div>
</div>
</div>
      
</div>
</div>
<br>
<h3>Analytic</h3>

<div style="width: 80%; margin: 20px auto;">
        <canvas id="categoryChart"></canvas>
    </div>

    <script>
        new Chart(document.getElementById('categoryChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($categories); ?>,
                datasets: [{
                    label: 'Videos per Category',
                    data: <?php echo json_encode($counts); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>


            </main>


<!-- INCLUDING Footer -->
<?php include '../files/adminfooter.php' ?>


    
</body>
</html>