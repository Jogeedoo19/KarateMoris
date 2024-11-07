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
<div class="container">
<h1>Sign Up</h1>
<p>Please fill in this form to create an account.</p>
 <hr>
 <label for="txtfn"><b>Firstname</b></label>
 <input type="text" placeholder="Enter Firstname" name="txtfn" required>

 <label for="txtln"><b>Lastname</b></label>
 <input type="text" placeholder="Enter Lastname" name="txtln" required>

 <label for="txtuname"><b>Username</b></label>
 <input type="text" placeholder="Enter Username" name="txtuname" required>

 <label for="txtdob"><b>Date of Birth</b></label>
 <input type="text" placeholder="Enter Date of Birth" name="txtdob" required>

 <label for="email"><b>Address</b></label>
 <input type="text" placeholder="Enter Address" name="txtaddress" required>

 <label for="email"><b>Email</b></label>
 <input type="text" placeholder="Enter Email" name="txtemail" required>

 <label for="psw"><b>Password</b></label>
 <input type="password" placeholder="Enter Password" name="txtpsw" required>

 <label for="psw-repeat"><b>Repeat Password</b></label>
 <input type="password" placeholder="Repeat Password" name="txtpsw-repeat" required>

 <label for="txtimg"><b>Image</b></label>
<input type="file" name="txtimg"  class="form-control" required>

 <label>
 <input type="checkbox" checked="checked" name="remember" style="margin-bottom:15px"> Remember me
</label>

 <p>By creating an account you agree to our <a href="#" style="color:dodgerblue">Terms & Privacy</a>.</p>

 <div class="clearfix">
 <button type="button" class="cancelbtn">Cancel</button>
 <button type="submit" class="signupbtn">Sign Up</button>
 </div>
 </div>
</form>
</div>
</main>


<!-- INCLUDING Footer -->
<?php include '../files/footer.php' ?>


    
</body>
</html>