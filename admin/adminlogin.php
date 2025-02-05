<?php
require_once '../db/util.php';
require_once '../db/pdo.php';
session_start();

if(isset($_SESSION['admin_id'])){
    header('Location: dashboard.php');
    return;
}

if(isset($_POST['btncancel'])){
    header('Location: dashboard.php');
    return;
}

if(isset($_POST['btnlogin'])){
    $uname = $_POST['txtuname'];
    $psw = $_POST['txtpsw'];
    $remember = $_POST['remember'];

    if(strlen($uname) < 1 || strlen($psw) < 1){
        $_SESSION['error'] = 'Username and password are required';
        header('Location: login.php');
        return;
    }

    $stmt = $pdo->prepare('SELECT * FROM admin WHERE username = :uname');
    $stmt->execute(array(':uname' => $uname));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if($row === false){
        $_SESSION['error'] = 'Invalid username';
        header('Location: login.php');
        return;
    }

    $check = hash('md5', $psw);

    $check = password_verify($psw, $row['password']);
    if($check === false){
        $_SESSION['error'] = 'Incorrect password';
        header('Location: login.php');
        return;
    }

    $_SESSION['admin_id'] = $row['admin_id'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['successmsg'] = 'Logged in successfully';

    if($remember){
        setcookie('admin_id', $row['admin_id'], time() + 60*60*24*30);
        setcookie('username', $row['username'], time() + 60*60*24*30);
    }

    header('Location: dashboard.php');
    return;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="../css/login.css" rel="stylesheet">
    <?php include '../files/csslib.php'; ?> <!-- Including libraries -->
</head>
<body>
    <!-- Include header and navigation -->
    <?php include_once '../files/nav.php'; ?>
    <br><br><br><br>

    <main class="main">
        <div class="container col-xl-4 col-lg-6">
            <form method="post" id="frmlogin" method="post" onsubmit="return remem()" enctype="multipart/form-data">
                <div class="imgcontainer">
                    <h1>Login</h1>
                </div>

              

                <div class="container">
                    <label for="txtuname"><b>Username</b></label>
                    <input type="text" placeholder="Enter Username" name="txtuname" id="txtuname" required>

                    <label for="txtpsw"><b>Password</b></label>
                    <input type="password" placeholder="Enter Password" name="txtpsw" id="txtpsw" required>

                    <button type="submit" name="btnlogin">Login</button>
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                </div>

                <div class="container" style="background-color:#f1f1f1">
                    <a href="index.php" class="btncancel">Cancel</a>
                    <span class="psw">Forgot <a href="forgot_password.php">password?</a></span>
                    <div class="mt-3">
                        Don't have an account? <a href="signup.php">Sign up here</a>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- Include footer -->
    <?php include '../files/footer.php'; ?>
    <script type="text/javascript" src="../js/mylib.js"> </script>
</body>
</html>
