<?php

if(!isset($_POST['cardid']) || !isset($_POST['type'])) die("0");

$cardid = $_POST['cardid'];
$type = intval($_POST['type']);

$servername = "localhost";
$username = "db_user_name";
$password = "db_password";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("1");
} 
$sql = sprintf("CALL db_user_name.CheckCard('%s',%u);", $cardid, $type);
$result =  get_value($conn,$sql);

echo $result;


function get_value($mysqli, $sql) {
    $result = $mysqli->query($sql);
    $value = $result->fetch_array(MYSQLI_NUM);
    return is_array($value) ? $value[0] : "";
}
?>