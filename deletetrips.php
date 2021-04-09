<?php
session_start();
include("connection.php");

$trip_id = $_POST["trip_id"];

$sql = "DELETE FROM carsharetrips WHERE trip_id='$trip_id'";


$result = mysqli_real_query($link, $sql);

if(!$result){
  echo "error";
}

?>
