<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="../css/stylecss.css" rel="stylesheet">
    <?php include '../files/csslib.php' ?> <!-- including libraries -->

</head>
<body>

<!-- including header and banner -->
<?php include_once '../files/nav.php' ?>


<br><br><br><br><br><br>

<main class="main">

<div class="container col-xl-4 col-lg-6">
<form action="action_page.php" method="post" style="border: 3px solid #f1f1f1;">


<div class="container">
    <center><h2>Manage Notes</h2></center>
<label for="txttitle"><b>Title</b></label>
<input type="text" class="form-control" placeholder="Enter title" name="txttitle" required>

<label for="txtdesc"><b>Description</b></label>
<input type="text" class="form-control" placeholder="Enter description" name="txtdesc" required>

<label for="txtfile"><b>Notes</b></label>
<input type="file" class="form-control" name="txtfile" required>
<br>


</div>

<div class="container" style="background-color:#f1f1f1">
 
<center><div>
<button type="submit" class="btn btn-primary col-xl-2 pt-2">Post</button>
<button type="button" class="btn btn-success col-xl-2 pt-2">Update</button>
<button type="button" class="btn btn-danger col-xl-2 pt-2">Delete</button>
<button type="button" class="btn btn-warning col-xl-2 pt-2">Cancel</button>
</div></center>
</div>
</form><br>
</main>

<!-- INCLUDING Footer -->
<?php include '../files/footer.php' ?>


    
</body>
</html>