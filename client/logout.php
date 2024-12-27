<?php 
session_start();

unset($_SESSION['user_id']);
unset($_SESSION['master_id']);

session_destroy();

header('Location: index.php');
?>