<?php
$link = mysqli_connect('localhost', 'csctheco_user', 'ctMk_mT+x*IU', 'csctheco_drivvr');
 // check connection
if (!$link) {
    die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
}
?>
