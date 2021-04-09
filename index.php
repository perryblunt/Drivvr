<?php
session_start();
include('connection.php');

//logout
include('logout.php');

//remember me
include('remember.php');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Drivva</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
      <link href="styling.css" rel="stylesheet">
      <link href='https://fonts.googleapis.com/css?family=Arvo' rel='stylesheet' type='text/css'>

      <!-- //google map api -->
      <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBkkioioxpUiyrpyBSyEd66VwTDEl6Ixls&libraries=places">
      </script>
      <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
      <!-- Include all compiled plugins (below), or include individual files as needed -->
      <script src="js/bootstrap.min.js"></script>
      <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>


      <style>
        .previewing2{
          width:40px;
          height: auto;
          border-radius: 100%;
        }

        .linkforgotme{
            display:inline-block;

        }
        .rememberme{

          display:inline-block;
        }

      </style>
  </head>
  <body>
    <!--Navigation Bar-->
      <?php
        if(isset($_SESSION["user_id"])){
          include("navbarconnected.php");
        }else{
          include("navbarnotconnected.php");
        }

      ?>


              <div class="container-fluid" id="myContainer">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-3">
                            <h1>WEH YOU AGO!</h1>
                            <p class="lead bold">  $ave Money.</p>
                            <p class="bold">  Save JAMAICA</p>
                            <!-- Search form -->
                            <form action="" class="form-inline" method="get" id="searchForm">
                              <div class="form-group">
                                <label class="sr-only" for="departure">Departure</label>
                                    <input type="text" name="departure" id="departure" placeholder="Departure">
                              </div>

                              <div class="form-group">
                                <label class="sr-only" for="departure">Destination</label>
                                    <input type="text" name="destination" id="destination" placeholder="Destination">
                              </div>
                                <input type="submit" value="Search" class="btn btn-lg green" name="search">

                            </form>

                            <!---googleapis-->
                            <div id="googleMap"></div>

                            <!-- infowindow -->
                              <div id="infowindow-content">
                                <img src="" width="16" height="16" id="place-icon">
                                <span id="place-name"  class="title"></span><br>
                                <span id="place-address"></span>
                              </div>


                          <!-- Signup button with hide button if not signed in -->
                      <?php
                        if(!isset($_SESSION["user_id"])){
                          echo "<button class='btn btn-lg green signup' data-toggle='modal' data-target='#signupModal'>Signup Now</button>";
                        }
                      ?>
                      <!-- trips -->
                              <div id="searchResults"></div>
                          </div>
                      </div>
                </div>




    <!--Login form-->
      <form method="post" id="loginform">
        <div class="modal fade" id="loginModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <button class="close" data-dismiss="modal">
                    &times;
                  </button>
                  <h4 id="myModalLabel">
                    Login:
                  </h4>
              </div>
              <div class="modal-body">

                  <!--Login message from PHP file-->
                  <div id="loginmessage"></div>
                  <div class="form-group">
                      <label for="loginemail" class="sr-only">Email:</label>
                      <input class="form-control" type="email" name="loginemail" id="loginemail" placeholder="Email" maxlength="50">
                  </div>
                  <div class="form-group">
                      <label for="loginpassword" class="sr-only">Password</label>
                      <input class="form-control" type="password" name="loginpassword" id="loginpassword" placeholder="Password" maxlength="30">
                  </div>
                  <div class="checkbox">

                      <label>
                          <input type="checkbox" name="rememberme" id="rememberme">
                        Remember me
                      </label>

                        <a class="pull-right" style="cursor: pointer" data-dismiss="modal" data-target="#forgotpasswordModal" data-toggle="modal">
                       Forgot Password?
                    </a>
                  </div>

              </div>
              <div class="modal-footer">
                  <input class="btn green" name="login" type="submit" value="Login">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                  Cancel
                </button>
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal" data-target="#signupModal" data-toggle="modal">
                  Register
                </button>
              </div>
          </div>
      </div>
      </div>
      </form>

    <!--Sign up form-->
      <form method="post" id="signupform">
        <div class="modal fade" id="signupModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <button class="close" data-dismiss="modal">
                    &times;
                  </button>
                  <h4 id="myModalLabel">
                    Sign up to use you Drivva!
                  </h4>
              </div>
              <div class="modal-body">

                  <!--Sign up message from PHP file-->
                  <div id="signupmessage"></div>
                            <!-- Enter Username -->
                  <div class="form-group">
                      <label for="username" class="sr-only">Username:</label>
                      <input class="form-control" type="text" name="username" id="username" placeholder="Username" maxlength="30">
                  </div>
                          <!-- Firstname -->
                  <div class="form-group">
                      <label for="firstname" class="sr-only">Firstname:</label>
                      <input class="form-control" type="text" name="firstname" id="firstname" placeholder="Firstname" maxlength="30">
                  </div>
                          <!-- Lastname-->
                  <div class="form-group">
                      <label for="lastname" class="sr-only">Lastname:</label>
                      <input class="form-control" type="text" name="lastname" id="lastname" placeholder="Lastname" maxlength="30">
                  </div>

                          <!-- Email-->
                  <div class="form-group">
                      <label for="email" class="sr-only">Email:</label>
                      <input class="form-control" type="email" name="email" id="email" placeholder="Email Address" maxlength="50">
                  </div>
                            <!-- Password1-->
                  <div class="form-group">
                      <label for="password" class="sr-only">Choose a password:</label>
                      <input class="form-control" type="password" name="password" id="password" placeholder="Choose a password" maxlength="30">
                  </div>
                          <!-- Confirm password-->
                  <div class="form-group">
                      <label for="password2" class="sr-only">Confirm password</label>
                      <input class="form-control" type="password" name="password2" id="password2" placeholder="Confirm password" maxlength="30">
                  </div>
                      <!-- Phone number -->
                  <div class="form-group">
                      <label for="phonenumber" class="sr-only">Telephone:</label>
                      <input class="form-control" type="text" name="phonenumber" id="phonenumber" placeholder="Telephone" maxlength="30">
                  </div>
                        <!-- Workplace -->
                  <div class="form-group">
                      <label for="workorschool" class="sr-only">Workplace or School Name :</label>
                      <input class="form-control" type="text" name="workorschool" id="workorschool" placeholder="work or school name" maxlength="30">
                  </div>
                        <!-- Gender male -->
                  <div class="form-group gender">
                      <label><input type="radio" name="gender" id="male"
                      value="male">Male</label>
                  </div>
                        <!-- Gender female-->
                  <div class="form-group gender">
                      <label><input type="radio" name="gender" id="female"
                      value="female">FeMale</label>
                  </div>
                        <!-- Comments-->
                  <div class="form-group">
                      <label for="moreinformation">Comments:</label>
                      <textarea name="moreinformation" class="form-control" rows="5" maxlength="280" style=resize:none></textarea>
                  </div>

              </div>

                      <!-- Signup Modal -->
              <div class="modal-footer">
                  <input class="btn green" name="signup" type="submit" value="Sign up">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                  Cancel
                </button>
              </div>
          </div>
      </div>
      </div>
      </form>

    <!--Forgot password form-->
      <form method="post" id="forgotpasswordform">
        <div class="modal" id="forgotpasswordModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <button class="close" data-dismiss="modal">
                    &times;
                  </button>
                  <h4 id="myModalLabel">
                    Forgot Password? Enter your email address:
                  </h4>
              </div>
              <div class="modal-body">

                  <!--forgot password message from PHP file-->
                  <div id="forgotpasswordmessage"></div>


                  <div class="form-group">
                      <label for="forgotemail" class="sr-only">Email:</label>
                      <input class="form-control" type="email" name="forgotemail" id="forgotemail" placeholder="Email" maxlength="50">
                  </div>
              </div>
              <div class="modal-footer">
                  <input class="btn green" name="forgotpassword" type="submit" value="Submit">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                  Cancel
                </button>
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal" data-target="signupModal" data-toggle="modal">
                  Register
                </button>
              </div>
          </div>
      </div>
      </div>
      </form>
    <!-- Footer-->
      <div class="footer">
          <div class="container">
              <p>CSC400 -Jerome Smith - Copyright &copy; 2017- <?php $today = date("Y"); echo $today?>.</p>
          </div>
      </div>

                <!-- Spinner -->
                <div id="spinner">
                  <img src='88.gif' width='84' height='84' />
                </div>
                <br /> Hold on.........
    <script src="javascript.js"></script>
    <script src="map.js"></script>
  </body>
</html>
