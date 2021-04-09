<?php
session_start();
include('connection.php');
if (!isset($_SESSION['user_id'])) {
    header("location: index.php");
}

$user_id = $_SESSION['user_id'];

//get username and email
$sql = "SELECT * FROM users WHERE user_id='$user_id'";
$result = mysqli_query($link, $sql);

$count = mysqli_num_rows($result);

if ($count == 1) {
    $row = mysqli_fetch_array($result, MYSQL_ASSOC);
    $username = $row['username'];
    $picture = $row['profilepicture'];
} else {
    echo "There was an error retrieving the username and email from the database";
}
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Drivva - My Trips</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="styling.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Arvo' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.2.0/css/font-awesome.min.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBkkioioxpUiyrpyBSyEd66VwTDEl6Ixls&libraries=places">
    </script>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js">
    </script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>




      <style>
        #container{
            margin-top:120px;
        }

        .footer{
          color:aquamarine;
        }

        .buttons{
            margin-bottom: 20px;
        }

        textarea{
            width: 100%;
            max-width: 100%;
            font-size: 16px;
            line-height: 1.5em;
            border-left-width: 20px;
            border-color: #CA3DD9;
            color: #CA3DD9;
            background-color: #FBEFFF;
            padding: 10px;

        }


        .text{
            font-size: 20px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .timetext{
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .btn-group{
            display: inline-block;
        }

        .modal {

          z-index:20;
        }

        .modal-backdrop {

          z-index:10;
        }

        .time{
            margin-top: 10px;

        }

        .trip{
            border: 0.5px solid grey;
            padding:3px;
            border-radius:10px;
            margin:10px;
            background: #FFF;
            opacity: .7;
        }

        #myTrips{
          margin-top: 20px;
          margin-bottom: 100px;
        }

        .departure, .destination{
            font-size: 1.7em;
      }
      .price{
          font-size: 2em;
    }

    .seatsAvailable{
        font-size: .8em;
  }
  .perseat{

    font-size: .7em;
  }




      </style>
  </head>
  <body>
    <!--Navigation Bar-->
      <nav role="navigation" class="navbar navbar-custom navbar-fixed-top">

          <div class="container-fluid">

              <div class="navbar-header">

                  <a class="navbar-brand" href="#"> Drivva</a>
                  <button type="button" class="navbar-toggle" data-target="#navbarCollapse" data-toggle="collapse">
                      <span class="sr-only">Toggle navigation</span>
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>

                  </button>
              </div>
              <div class="navbar-collapse collapse" id="navbarCollapse">
                  <ul class="nav navbar-nav">
                    <li><a href="index.php">Search</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="#">Help</a></li>
                    <li><a href="#">Contact us</a></li>
                      <li class="active"><a href="#">My Trips</a></li>
                  </ul>
                  <ul class="nav navbar-nav navbar-right">
                      <li><a href="#">
                        <div data-toggle="modal" data-target="#updatepicture">
                        <?php
                        if(empty($picture)){
                              echo "<img class='preview' src='profilepicture/image-2.png'/>";
                            }else{
                              echo "<img class='preview'
                              src='$picture'/>";

                            }
                         ?>


                      </div></a></li>
                      <li><a href="#"><?php echo $username; ?></a></li>
                    <li><a href="index.php?logout=1">Log out</a></li>
                  </ul>

              </div>
          </div>
      </nav>

<!--Main Container-->

      <div class="container" id="container">
          <div class="row">
            <div class="col-sm-8 col-sm-offset-2">
              <div>
                <!-- Add trip -->
                <button class="btn btn-lg green" data-toggle="modal" data-target="#addtripModal">
                  Add trips
                </button>

              </div>
              <div id="mytrips" class="trips">
                    <!-- Ajax Call to to PHP file -->

              </div>

            </div>
          </div>
      </div>

      <!--Add trips-->
        <form method="post" id="addtripform">
          <div class="modal fade" id="addtripModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button class="close" data-dismiss="modal">
                      &times;
                    </button>
                    <h4 id="myModalLabel">
                      Create A Trip
                    </h4>
                </div>
                <div class="modal-body">

                    <!--Add Trip essage from PHP file-->
                    <div id="addtripmessage"></div>

                         <!-- Map -->
                         <div id="googleMap">  </div>
                          <!-- Departure -->
                    <div class="form-group">
                        <label for="departure" class="sr-only">Departure</label>
                        <input class="form-control" type="text" name="departure" id="departure" placeholder="Departure">
                    </div>
                              <!-- Destination -->
                    <div class="form-group">
                        <label for="destination" class="sr-only">Destination</label>
                        <input class="form-control" type="text" name="destination" id="destination" placeholder="Destination">
                    </div>
                            <!-- Price-->
                    <div class="form-group">
                        <label for="price" class="sr-only">price</label>
                        <input class="form-control" type="number" name="price" id="price" placeholder="Price">
                    </div>

                                <!-- Seats-->
                    <div class="form-group">
                        <label for="seatsavailable" class="sr-only">Seats Available</label>
                        <input class="form-control" type="number" name="seatsavailable" id="seatsavailable" placeholder="Seats Available">
                    </div>

                              <!--Frequency -->
                     <div class="form-group">
                        <label><input type="radio" name="regular" id="yes"
                        value="Y"> Regular</label>
                        <label><input type="radio" name="regular" id="no"
                        value="N"> One-Off</label>
                    </div>
                                <!-- Check Box -->
                          <div class="regular">
                                <div class="checkbox">
                                <input id="monday" type="checkbox" value="1" name="monday">
                                <label for="monday">
                                    Monday
                                </label>
                            </div>
                            <div class="checkbox checkbox-primary">
                                <input id="tuesday" type="checkbox" value="1" name="tuesday">
                                <label for="tuesday">
                                    Tuesday
                                </label>
                            </div>
                            <div class="checkbox checkbox-success">
                                <input id="wednesday" type="checkbox" value="1" name="wednesday">
                                <label for="wednesday">
                                    Wednesday
                                </label>
                            </div>
                            <div class="checkbox checkbox-info">
                              <input id="thursday" type="checkbox" value="1" name="thursday">
                              <label for="thursday">
                                  Thursday
                              </label>

                            </div>
                            <div class="checkbox checkbox-warning">
                                <input id="friday" type="checkbox" value="1" name="friday">
                                <label for="friday">
                                    Friday
                                </label>
                            </div>
                            <div class="checkbox checkbox-danger">
                                <input id="saturday" type="checkbox" value="1" name="saturday">
                                <label for="saturday">
                                    Saturday
                                </label>
                            </div>

                            <div class="checkbox checkbox-danger">
                                <input id="sunday" type="checkbox" value="1" name="sunday">
                                <label for="sunday">
                                    Sunday
                                </label>
                            </div>
                        </div>

                                      <!-- Date -->
                              <div class="form-group one-off">
                                  <label for="date" class="sr-only">Date:</label>
                                  <input class="form-control" readonly="readonly" name="date" id="date">
                              </div>
                                    <!-- Time -->
                            <div class="form-group time regular one-off">
                                <label for="time" class="sr-only">Time:</label>
                                <input class="form-control" type="time" name="time" id="time" placeholder="Time">
                            </div>



                            <!-- Comments-->
                      <div class="form-group comment">
                          <label for="moreinformation">Comments:</label>
                          <textarea name="moreinformation" class="form-control" id="moreinformation2" rows="5" maxlength="280" style=resize:none></textarea>
                      </div>

                              <!-- Signup Modal -->
                      <div class="modal-footer">
                          <input class="btn btn-primary" name="signup" type="submit" value="Create trip">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                          Cancel
                        </button>
                      </div>


                    </div>






                        </div>


                    </div>
                </div>
            </div>
        </form>

        <!--Edit trips form-->
          <form method="post" id="edittripform">
            <div class="modal fade" id="edittripModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button class="close" data-dismiss="modal">
                        &times;
                      </button>
                      <h4 id="myModalLabel">
                        Edit trip
                      </h4>
                  </div>
                  <div class="modal-body">

                      <!--Edit Trip essage from PHP file-->
                      <div id="edittripmessage"></div>


                            <!-- Departure -->
                      <div class="form-group">
                          <label for="departure2" class="sr-only">Departure</label>
                          <input class="form-control" type="text" name="departure2" id="departure2" placeholder="Departure">
                      </div>
                                <!-- Destination -->
                      <div class="form-group">
                          <label for="destination2" class="sr-only">Destination</label>
                          <input class="form-control" type="text" name="destination2" id="destination2" placeholder="Destination">
                      </div>
                              <!-- Price-->
                      <div class="form-group">
                          <label for="price2" class="sr-only">price</label>
                          <input class="form-control" type="number" name="price2" id="price2" placeholder="Price">
                      </div>

                                  <!-- Seats-->
                      <div class="form-group">
                          <label for="seatsavailable2" class="sr-only">Seats Available</label>
                          <input class="form-control" type="number" name="seatsavailable2" id="seatsavailable2" placeholder="Seats Available">
                      </div>

                                      <!-- Regular /one-off -->

                      <div  class="form-group">
                    <label><input type="radio" name="regular2" id="yes2" value="Y">Regular</label>
                    <label><input type="radio" name="regular2" id="no2" value="N">One-off</label>
                    </div>


                                  <!-- Check Box -->
                            <div class="regular2">
                                  <div class="checkbox">
                                  <input id="monday2" type="checkbox" value="1" name="monday2">
                                  <label for="monday">
                                      Monday
                                  </label>
                              </div>
                              <div class="checkbox checkbox-primary">
                                  <input id="tuesday2" type="checkbox" value="1" name="tuesday2">
                                  <label for="tuesday">
                                      Tuesday
                                  </label>
                              </div>
                              <div class="checkbox checkbox-success">
                                  <input id="wednesday2" type="checkbox" value="1" name="wednesday2">
                                  <label for="wednesday">
                                      Wednesday
                                  </label>
                              </div>
                              <div class="checkbox checkbox-info">
                                <input id="thursday2" type="checkbox" value="1" name="thursday2">
                                <label for="thursday">
                                    Thursday
                                </label>

                              </div>
                              <div class="checkbox checkbox-warning">
                                  <input id="friday2" type="checkbox" value="1" name="friday2">
                                  <label for="friday">
                                      Friday
                                  </label>
                              </div>
                              <div class="checkbox checkbox-danger">
                                  <input id="saturday2" type="checkbox" value="1" name="saturday2">
                                  <label for="saturday">
                                      Saturday
                                  </label>
                              </div>

                              <div class="checkbox checkbox-danger">
                                  <input id="sunday2" type="checkbox" value="1" name="sunday2">
                                  <label for="sunday">
                                      Sunday
                                  </label>
                              </div>
                          </div>
                                        <!-- Date -->
                                <div class="form-group one-off2">
                                    <label for="date2" class="sr-only">Date:</label>
                                    <input class="form-control" readonly="readonly" name="date2" id="date2">
                                </div>
                                      <!-- Time -->
                              <div class="form-group time regular2 one-off2">
                                  <label for="time2" class="sr-only">Time:</label>
                                  <input class="form-control" type="time" name="time2" id="time2">
                              </div>
                              <!-- Comments-->
                        <div class="form-group comment">
                            <label for="moreinformation2">Comments:</label>
                            <textarea name="moreinformation2" id="moreinformation2" class="form-control" rows="5" maxlength="280" style=resize:none></textarea>
                        </div>

                                <!-- Signup Modal -->
                        <div class="modal-footer">
                            <input class="btn btn-primary" name="updateTrip" type="submit" value="Edit trip">
                            <input class="btn btn-danger" name="deleteTrip"  value="Delete" id="deleteTrip" type="button">
                          <button type="button" class="btn btn-default" data-dismiss="modal">
                            Cancel
                          </button>
                        </div>


                  </div>






                  </div>


              </div>
          </div>
          </div>
          </form>
    <!-- Footer-->
      <div class="footer">
          <div class="container">
              <p>CSC400 -Jerome Smith - Copyright &copy; 2017- <?php $today = date("Y"); echo $today ?>.</p>
          </div>
      </div>

      <!-- Spinner -->
      <div id="spinner">
        <img src='88.gif' width='84' height='84' />
      </div>
      <br /> Hold on.........


    <script src="map.js"></script>
    <script src="trips.js"></script>
  </body>
</html>
