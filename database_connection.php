<?php

$hostname = 'localhost';
$username = 'root';
$password = '';
$db_name = 'ehrdb';

try {
    $dbh = new PDO("mysql:host=$hostname;dbname=mysql", $username, $password);

    echo 'Connected to database';
    }
catch(PDOException $e)
    {
    echo $e->getMessage();
    }
?>