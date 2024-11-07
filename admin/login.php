<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
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
    <center><h2>Login</h2></center>
<label for="uname"><b>Username</b></label>
<input type="text" placeholder="Enter Username" name="txtuname" required>

<label for="psw"><b>Password</b></label>
<input type="password" placeholder="Enter Password" name="txtpsw" required>


<label>
<input type="checkbox" checked="checked" name="remember"> Remember me
</label>
</div>

<center><div class="container mt-2" style="background-color:#f1f1f1">
<button type="submit" class="btn btn-success">Login</button>
<button type="button" class="btn btn-warning">Cancel</button>

</div></center>
</form>
</main>


<!-- INCLUDING Footer -->
<?php include '../files/adminfooter.php' ?>


    
</body>
</html>