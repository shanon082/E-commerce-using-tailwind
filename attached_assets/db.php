<?php
$dbConn = "mysql:host=localhost;dbname=ecommerce";
$dbusername = "root";
$dbpassword = "";

try{
    $conn = new PDO($dbConn,$dbusername,$dbpassword);
    $conn -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

}catch(PDOException $e){
    echo "connection failed: ".$e->getMessage();
    exit;
}

?>