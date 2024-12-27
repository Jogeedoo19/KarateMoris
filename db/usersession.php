<?php

//Ex 18: Preventing a user from accessing a web page if NOT authenticated
//start
function checkUserAuth()
{
    //check if the session does not exist
if (isset($_SESSION["user_id"]) ) {
        header("Location: index.php");
        }
}
//End

?>