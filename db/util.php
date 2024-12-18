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
    

    

?>