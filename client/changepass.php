<?php 
session_start();
require_once "../db/pdo.php";
require_once "../db/util.php";
//Deny access if session does not exist

if (!isset($_SESSION["user_id"])) {
header("Location: index.php");
}
if (isset($_POST['btncancel'])) {
header('Location: index.php');
return;
}
if (isset($_POST['btnupdate'])) {
$msg = validateOldPass();
$msg2 = validateCPass();
if (is_string($msg) || is_string($msg2)) {
$_SESSION['error'] = "$msg <br/> $msg2";
header("Location: changepass.php");
return;
}
$stmt = $pdo->prepare("SELECT user_id, password
FROM user where user_id = :user_id");
$stmt->execute(
array(
':user_id' => $_SESSION['user_id']
)
);

$srow = $stmt->fetch(PDO::FETCH_ASSOC);
//encrypt the password
$oldpass = hash('md5',$_POST['txtoldpass']);
if ($srow["password"] == $oldpass) {
//encrypt the new password
$newpass = hash('md5',$_POST['txtnewpass']);
//add sql to update the client password
$sql = "UPDATE user set password = :pass where user_id = :user_id ";
$stmt2 = $pdo->prepare($sql);
$stmt2->execute(
array(
':user_id' => $_SESSION['user_id'],
':pass' => $newpass
)
);
$_SESSION['successmsg'] = "Your password has been changed";
header("Location: changepass.php");
return;
} else {
$_SESSION['error'] = "Old password does not match!";
}
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title></title>
    <?php include '../files/csslib.php' ?>
</head>

<body>
    <?php include_once '../files/nav.php' ?>
    <main>

        <!-- ======= Section ======= -->
        <section id="trainers" class="trainers">
            <div class="container" data-aos="fade-up">
                <div class="row" data-aos="zoom-in" data-aos-delay="100">
                    <div class="col-md-5 pt-5">
                        <img src="../images/setting.jpg" class="img-fluid h-50" />
                    </div>
                    <div class="col-md-6 pt-5 offset-md-1">
                        <h3>
                            <?php
                            flashMessages(); ?> </h3>
                        <form id="frmmodpass" method="post" enctype="multipart/form data">
                            <div class="mb-3">
                                <label for="txtpass" class="form-label">Old Password</label>
                                <input type="password" class="form-control" name="txtoldpass" id="txtoldpass" />
                            </div>
                            <div class="mb-3">
                                <label for="txtnewpass" class="form-label">New Password</label>
                                <input type="password" class="form-control" name="txtnewpass" id="txtnewpass" />
                            </div>
                            <div class="mb-3">
                                <label for="txtcpass" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" name="txtcpass" id="txtcpass" />
                            </div>
                            <button type="submit" name="btnupdate" class="col-12 btn btn-success btn-lg mx-auto">
                                Change Password
                            </button>
                            <p></p>
                            <button type="submit" name="btncancel" class="col-12 btn btn-success btn-lg mx-auto">
                                Cancel
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Section -->
    </main>
    <?php include '../files/footer.php' ?>
</body>

</html>