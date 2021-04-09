<?php
$user_id = $_SESSION['user_id'];
include("connection.php");
//get username
$sql = "SELECT * FROM users WHERE user_id='$user_id'";
$result = mysqli_query($link, $sql); //connection & Query
$count = mysqli_num_rows($result); //Number of count return which should be one
//1 count is good else error
if ($count == 1) {
    $row = mysqli_fetch_array($result, MYSQL_ASSOC);//fetch array from database table
  $username = $row["username"];//assign ellement username from table to $username
  $picture = $row["profilepicture"];
} else {
    echo "<div class='alert alert-danger'> There was an error retrieving the username from the database.</div>";
}

 ?>


<nav role="navigation" class="navbar navbar-custom navbar-fixed-top">

    <div class="container-fluid">

        <div class="navbar-header">
            <a class="navbar-brand" href="#">Drivva</a>
            <button type="button" class="navbar-toggle" data-target="#navbarCollapse" data-toggle="collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>

            </button>
        </div>
        <div class="navbar-collapse collapse" id="navbarCollapse">
            <ul class="nav navbar-nav">
              <li class="active"><a href="index.php">Search</a></li>
              <li><a href="profile.php">Profile</a></li>
              <li><a href="#">Help</a></li>
              <li><a href="#">Contact us</a></li>
              <li><a href="mainpageloggedin.php">My Trips</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
              <li><a href="#">
                <?php
                    if(empty($picture)) {
                        echo "<div class='image_preview'><img class='previewing2' src='profilepicture/noimage.jpg' /></div>";
                    } else {
                        echo "<div class='image_preview'><img class='previewing2' src='$picture' /></div>";
                    }
                ?>
                </a>
              </li>
              <li><a href="#"><b><?php echo $username ?></b></a></li>
              <li><a href="index.php?logout=1" data-toggle="modal">Log out</a></li>
            </ul>
        </div>
    </div>
</nav>
