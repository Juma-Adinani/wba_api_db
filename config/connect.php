<?php
    
    header('Access-Control-Allow-Origin: *');
    $host   = "localhost";
    $user   = "root";
    $db     = "wba_db";
    $pass   = "";

    $con = mysqli_connect($host, $user, $pass, $db);
    if(mysqli_connect_error()){
        die(json_encode(['status'=>'Error', 'message'=>'Sorry!, An error has occured.', 'data'=>'']));
    }

?>
