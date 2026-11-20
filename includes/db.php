<?php

    $db_server = "localhost";
    $db_user ="root";
    $db_pass="";
    $db_name = "taskflow";
    

    try{
        $pdo = new
         PDO("mysql:host=$db_server;dbname=$db_name;charset=utf8mb4",$db_user,$db_pass );
        $pdo->setAttribute(PDO:: ATTR_ERRMODE, PDO:: ERRMODE_EXCEPTION);
        
    }
     
    catch(PDOException $e ){
        die("Could not connect: " . $e->getMessage());
    }


?>