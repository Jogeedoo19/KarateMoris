<?php

function flashMessages(){
    if ( isset($_SESSION["error"]) ) {
    echo('<p style="color:red">'. $_SESSION["error"] . '</p>');
     //delete the session
     unset($_SESSION["error"]);
    
    }
    if ( isset($_SESSION['successmsg']) ) {
    echo '<p style="color:green">'. $_SESSION['successmsg'] . '</p>';
     //delete the session
     unset($_SESSION['successmsg']);
    } 
    }

function validateCategory(){
    if ( strlen($_POST['txtcat']) < 1) {
    return 'Category name is required';
    }
    }
    


//User registration
function validateFirstName() {
    if (strlen($_POST['txtfn']) < 1) {
        return 'First name is required';
    }
    return null;
}

function validateFileProfilePic() {
    if (strlen($_FILES['profilepic']['name']) < 1) {
        return 'Profile picture is required';
    }
    return null;
}

function validateEmail() {
    if (strlen($_POST['txtemail']) < 1) {
        return 'Email is required';
    } elseif (!filter_var($_POST['txtemail'], FILTER_VALIDATE_EMAIL)) {
        return 'Invalid email format';
    }
    return null;
}

function validateUname() {
    if (strlen($_POST['txtuname']) < 1) {
        return 'Username is required';
    }
    return null;
}

function validateAddress() {
    if (strlen($_POST['txtaddress']) < 1) {
        return 'Address is required';
    }
    return null;
}

function validatePass() {
    if (strlen($_POST['txtpsw']) < 1) {
        return 'Password is required';
    }
    if (strlen($_POST['txtpsw']) <= 6) {
        return 'Password should be more than 6 characters';
    }
    if (!preg_match('/[\^*&$#@]/', $_POST['txtpsw'])) {
        return 'Password should contain at least one of ^, *, &, $, #, or @';
    }
    return null;
}

function validateConfirmPass() {
    if ($_POST['txtpsw'] != $_POST['txtpsw-repeat']) {
        return 'Passwords do not match';
    }
    return null;
}

//Authentication

function checkUserAuth(){
    if(isset($_SESSION['user_id'])){
        header('Location: index.php');
        return;
    }

}

function checkMasterAuth(){
    if(isset($_SESSION['master_id'])){
        header('Location: index.php');
        return;
    }

}

function checkAdminAuth(){
    if(isset($_SESSION['admin_id'])){
        header('Location: dashboard.php');
        return;
    }

}
// view video
function getYoutubeEmbedUrl($url) {
    $video_id = '';
    
    // Pattern for various YouTube URL formats
    $patterns = [
        '/youtube\.com\/watch\?v=([^\&\?\/]+)/',
        '/youtube\.com\/embed\/([^\&\?\/]+)/',
        '/youtube\.com\/v\/([^\&\?\/]+)/',
        '/youtu\.be\/([^\&\?\/]+)/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            $video_id = $matches[1];
            break;
        }
    }
    
    if ($video_id) {
        return 'https://www.youtube.com/embed/' . $video_id;
    }
    
    return '';
}

// Then use it like this:
//$final = getYoutubeEmbedUrl($video['videourl']);
    
function validateOldPass()
            {
            if (strlen($_POST['txtoldpass']) < 1) {
            return 'Old Password is required';
            }
            }
            function validateCPass()
            {
            if ($_POST['txtnewpass'] != $_POST['txtcpass']) {
            return 'Password does not match';
            }
            }

?>