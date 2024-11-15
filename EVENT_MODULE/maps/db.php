<?php
$servername = "vvfv20el7sb2enn3.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "ps1u43eztn9ag7ym";
$password = "zxs25finszh1r9uz";
$dbname = "im4lt5tgvscipyvc";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error){
    die("Connection Failed".$conn->connect_error);
} else {
    //echo "Connected";
}