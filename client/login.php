<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="../css/login.css" rel="stylesheet">
    <?php include '../files/csslib.php' ?> <!-- including libraries -->
   

</head>
<body>

<!-- including header and banner -->
<?php include_once '../files/nav.php' ?>

<br><br><br><br><br><br>

<main class="main">

<div class="container col-xl-4 col-lg-6">
<form action="action_page.php" method="post">
<div class="imgcontainer">
<h1>Login</h1>
<img src="img_avatar2.png" alt="Avatar" class="avatar">
</div>

<div class="container">
<label for="uname"><b>Username</b></label>
<input type="text" placeholder="Enter Username" name="txtuname" required>

<label for="psw"><b>Password</b></label>
<input type="password" placeholder="Enter Password" name="txtpsw" required>

<button type="submit">Login</button>
<label>
<input type="checkbox" checked="checked" name="remember"> Remember me
</label>
</div>

<div class="container" style="background-color:#f1f1f1">
<button type="button" class="cancelbtn">Cancel</button>
<span class="psw">Forgot <a href="#">password?</a></span>
</div>
</form>
</main>


<!-- INCLUDING Footer -->
<?php include '../files/footer.php' ?>


    
</body>
</html>