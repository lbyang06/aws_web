<?php
$host = "myasmdb.cgqrtafng5p5.us-east-1.rds.amazonaws.com";
$user = "cloudasm_user";
$password = "Admin12345!";
$database = "chaagee";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully";
?>
