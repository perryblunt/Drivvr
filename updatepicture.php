<?php
session_start();
include("connection.php");

$user_id = $_SESSION["user_id"];

//print_r($_FILES);

//file name
$name = $_FILES["picture"]["name"];

//file extension
$extension = pathinfo($name, PATHINFO_EXTENSION);

//tmp location after submit button
$tmp_name = $_FILES["picture"]["tmp_name"];

//declare a permanent location on server
$permanentDestination = 'profilepicture/' . md5(time()) . ".$extension";

//erorr from Files array if 0 we good else bad
$fileError = $_FILES["picture"]["error"];

//move file to server and check
if(move_uploaded_file($tmp_name, $permanentDestination)){
      $sql = "UPDATE users SET profilepicture='$permanentDestination' WHERE user_id='$user_id'";
      $result = mysqli_query($link, $sql);

      if(!$result){
                echo "<div class='alert alert-danger'>Unable to update profile
                picture. Plese try again later!</div>";
          }
      }else{
        echo "<div class='alert alert-danger'>Unable to update profile
        picture. Plese try again later!</div>";
      }

      if($fileError>0){
        echo "<div class='alert alert-danger'>Unable to update profile
        picture. Plese try again later! Error code: $fileError</div>";

      }

 ?>
