<?php
$pdo = new PDO('mysql:host=localhost;port=3306;dbname=karatemoris', 'fred', 'fred55');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>