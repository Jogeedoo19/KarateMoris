<?php
session_start();
require_once "../db/pdo.php";
require_once "../db/util.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set default timezone
$d = new DateTime('now');
$d->setTimezone(new DateTimeZone('GMT+4'));
$date = $d->format('Y-m-d');

if (isset($_POST['btncancel'])) {
    header('Location: Register.php');
    return;
}

if (isset($_POST['btnsignup'])) {
    // Initialize error messages
    $msg = validateFirstName(); 
    $msg2 = validateFileProfilePic();
    $msg3 = validateEmail();
    $msg4 = validateUname();
    $msg5 = validateAddress();
    $msg6 = validatePass();
    $msg7 = validateConfirmPass();

    if (
        is_string($msg) || is_string($msg2) || is_string($msg3) ||
        is_string($msg4) || is_string($msg5) || is_string($msg6) || is_string($msg7)
    ) {
        $_SESSION['errormsg'] = $msg . "<br>" . $msg2 . "<br>" . $msg3 . "<br>" . $msg4 . "<br>" . $msg5 . "<br>" . $msg6 . "<br>" . $msg7;
        header("Location: signup.php");
        return;
    }

    // Check if the email already exists in the database
    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = :em");
    $stmt->execute([":em" => $_POST['txtemail']]);
    $srow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($srow === false) {
        // Hash the password before saving to the database
        $hashedPassword = password_hash($_POST['txtpsw'], PASSWORD_DEFAULT);

        // Handle file upload
        $filename = $_FILES['profilepic']['name'];
        $uploadPath = "../upload/" . $filename;

        if (!move_uploaded_file($_FILES['profilepic']['tmp_name'], $uploadPath)) {
            $_SESSION['errormsg'] = "File upload failed!";
            header("Location: signup.php");
            return;
        }

        // Insert the new user into the database
        $sql = "INSERT INTO user (first_name, last_name, username, email, password, address, status, image) 
                VALUES (:fn, :ln, :uname, :email, :pass, :address, :sts, :filen)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':fn' => $_POST['txtfn'],
            ':ln' => $_POST['txtln'],
            ':uname' => $_POST['txtuname'],
            ':email' => $_POST['txtemail'],
            ':pass' => $hashedPassword,
            ':address' => $_POST['txtaddress'],
            ':sts' => 'active',
            ':filen' => $filename,
        ]);

        $_SESSION['successmsg'] = "User registered successfully! Redirecting to login page...";
        header("refresh:3; url=login.php");
        return;
    } else {
        $_SESSION['errormsg'] = "Email already exists! Use another email.";
        header("Location: signup.php");
        return;
    }
}


?>
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
<form id="frmsignup" method="post" enctype="multipart/form-data" style="border:1px solid #ccc">
<div class="container">
<h1>Sign Up</h1>
<h2><?php flashMessages(); ?></h2>
<p>Please fill in this form to create an account.</p>
 <hr>
 <label for="txtfn"><b>Firstname</b></label>
 <input type="text" placeholder="Enter Firstname" id="txtfn" name="txtfn" required>

 <label for="txtln"><b>Lastname</b></label>
 <input type="text" placeholder="Enter Lastname" id="txtln" name="txtln" required>

 <label for="txtuname"><b>Username</b></label>
 <input type="text" placeholder="Enter Username"  id="txtuname" name="txtuname" required>

 <label for="txtaddress"><b>Address</b></label>
 <input type="text" placeholder="Enter Address" id="txtaddress" name="txtaddress" required>

 <label for="txtemail"><b>Email</b></label>
 <input type="text" placeholder="Enter Email" id="txtemail" name="txtemail" required>

 <label for="txtpsw"><b>Password</b></label>
 <input type="password" placeholder="Enter Password" id="txtpsw" name="txtpsw" required>

 <label for="txtpsw-repeat"><b>Repeat Password</b></label>
 <input type="password" placeholder="Repeat Password" id="txtpsw-repeat" name="txtpsw-repeat" required>

<label for="profilepic" class="form-label">Upload an Image</label>
<input class="form-control" name="profilepic" type="file" id="profilepic" required>

 

 <p>By creating an account you agree to our <a href="#" style="color:dodgerblue">Terms & Privacy</a>.</p>

 <div class="clearfix">
 <button type="button" name="btncancel" id="btncancel" class="cancelbtn">Cancel</button>
 <button type="submit" name="btnsignup" id="btnsignup" class="signupbtn">Sign Up</button>
 </div>
 </div>
</form>
</div>
</main>


<!-- INCLUDING Footer -->
<?php include '../files/footer.php' ?>


    
</body>
</html>