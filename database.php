<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "2rRashmi";

$conn = new mysqli($servername, $username, $password, $dbname);

if(!$conn){
    print("Connection failed");
}
    $hello = "Hello World!";
    echo json_decode($hello);
?>