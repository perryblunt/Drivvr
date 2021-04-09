<?php
//start session and connect
session_start();
include("connection.php");



$trip_id = $_POST['trip_id'];

$sql = "SELECT * FROM carsharetrips WHERE trip_id='$trip_id'";
$result = mysqli_query($link, $sql);

if(!$result){
  echo "error";
}else{
  $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
  echo json_encode($row);
}

?>
