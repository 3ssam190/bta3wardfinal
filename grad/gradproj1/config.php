<?php
    $dsn = 'mysql:host=localhost;dbname=u578375581_proj';
    $user = 'u578375581_essam1';
    $pass = 'ESAM123esam@@';
    $option = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    );
    try{
        $conn = new PDO($dsn, $user, $pass, $option);
        $conn -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e){
        echo 'Failed to connect' . $e->getMessage();
    } ?>