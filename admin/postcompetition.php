<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Competition</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/admincsslib.php' ?> <!-- including libraries -->
    
</head>
<body>

<!-- including header and banner -->
<?php include_once '../files/adminsidebar.php' ?>

<br><br><br><br><br><br>

<main class="main">

<div class="container col-xl-4 col-lg-6">
<form action="action_page.php" method="post" style="border: 3px solid #f1f1f1;">


<div class="container">
    <center><h2>Post Competition</h2></center>
<label for="txtname"><b>Name</b></label>
<input type="text" placeholder="Enter name" name="txtname" required>

<label for="textdesc"><b>Description</b></label>
<input type="text" placeholder="Enter description" name="txtdesc" required>

<label for="txtimg"><b>Image</b></label>
<input type="file" name="txtimg"  class="form-control" required><br>




</div>

<div class="container" style="background-color:#f1f1f1">
 
<center><div>
<button type="submit" class="btn btn-primary col-xl-2 pt-2">Post</button>
<button type="button" class="btn btn-success col-xl-2 pt-2">Update</button>
<button type="button" class="btn btn-danger col-xl-2 pt-2">Delete</button>
<button type="button" class="btn btn-warning col-xl-2 pt-2">Cancel</button>
</div></center>
</div>
</form>
</main>


<!-- INCLUDING Footer -->
<?php include '../files/adminfooter.php' ?>


    
</body>
</html>
    
</body>
</html>