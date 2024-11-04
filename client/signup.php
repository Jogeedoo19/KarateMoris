<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="../css/signup.css" rel="stylesheet">
    <?php include '../files/csslib.php' ?> <!-- including libraries -->

</head>
<body>

<!-- including header and banner -->
<?php include_once '../files/nav.php' ?>

<br><br><br><br><br><br>

<main class="main">

<div class="container col-xl-4 col-lg-6">
<form action="action_page.php" style="border:1px solid #ccc">

<h1>Sign Up</h1>
<p>Please fill in this form to create an account.</p>
 <hr>
 <label for="email"><b>Email</b></label>
 <input type="text" placeholder="Enter Email" name="email" required>

 <label for="psw"><b>Password</b></label>
 <input type="password" placeholder="Enter Password" name="psw" required>

 <label for="psw-repeat"><b>Repeat Password</b></label>
 <input type="password" placeholder="Repeat Password" name="psw-repeat" required>

 <label>
 <input type="checkbox" checked="checked" name="remember" style="margin-bottom:15px"> Remember me
</label>

 <p>By creating an account you agree to our <a href="#" style="color:dodgerblue">Terms & Privacy</a>.</p>

 <div class="clearfix">
 <button type="button" class="cancelbtn">Cancel</button>
 <button type="submit" class="signupbtn">Sign Up</button>
 </div>

</form>
</div>
</main>


<!-- INCLUDING Footer -->
<?php include '../files/footer.php' ?>


    
</body>
</html>