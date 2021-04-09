<?php
//<!--Start session-->
session_start();
include('connection.php');

//<!--Check user inputs-->
//    <!--Define error messages-->
$missingUsername = '<p><strong>Please enter a username!</strong></p>';
$missingEmail = '<p><strong>Please enter your email address!</strong></p>';
$invalidEmail = '<p><strong>Please enter a valid email address!</strong></p>';
$missingPassword = '<p><strong>Please enter a Password!</strong></p>';
$invalidPassword = '<p><strong>Your password should be at least 6 characters long and inlcude one capital letter and one number!</strong></p>';
$differentPassword = '<p><strong>Passwords don\'t match!</strong></p>';
$missingPassword2 = '<p><strong>Please confirm your password</strong></p>';
$missingFirstname = '<p><strong>Please answer your firstname</strong></p>';
$missingLastname = '<p><strong>Please answer your lastname</strong></p>';
$missingPhone = '<p><strong>Please enter your phone number</strong></p>';
$invalidPhonenumber = '<p><strong>Please enter a valid phone number(digits only and less than 15 long)!</strong></p>';
$missingworkorschool = '<p><strong>Please answer your workplace</strong></p>';
$missingGender = '<p><strong>Please select gender</strong></p>';
$missingComment = '<p><strong>Share a few words about yourself></p>';

//    <!--Get username, email, password, password2-->
//Get username
if(empty($_POST["username"])){
    $errors .= $missingUsername;
}else{
    $username = filter_var($_POST["username"], FILTER_SANITIZE_STRING);
}
//Get firstname
if(empty($_POST["firstname"])){
    $errors .= $missingfirstname;
}else{
    $firstname = filter_var($_POST["firstname"], FILTER_SANITIZE_STRING);
}
//Get lastname
if(empty($_POST["lastname"])){
    $errors .= $missingLastname;
}else{
    $lastname = filter_var($_POST["lastname"], FILTER_SANITIZE_STRING);
}
//Get email
if(empty($_POST["email"])){
    $errors .= $missingEmail;
}else{
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors .= $invalidEmail;
    }
}
//Get passwords
if(empty($_POST["password"])){
    $errors .= $missingPassword;
}elseif(!(strlen($_POST["password"])>6
         and preg_match('/[A-Z]/',$_POST["password"])
         and preg_match('/[0-9]/',$_POST["password"])
        )
       ){
    $errors .= $invalidPassword;
}else{
    $password = filter_var($_POST["password"], FILTER_SANITIZE_STRING);
    if(empty($_POST["password2"])){
        $errors .= $missingPassword2;
    }else{
        $password2 = filter_var($_POST["password2"], FILTER_SANITIZE_STRING);
        if($password !== $password2){
            $errors .= $differentPassword;
        }
    }
}

//get phone number
if(empty($_POST["phonenumber"])){
  $errors .= $missingPhone;
}elseif(preg_match('/\D/', $_POST["phonenumber"])){
  $errors .= $invalidPhonenumber;

}else{
  $phonenumber = filter_var($_POST["phonenumber"], FILTER_SANITIZE_STRING);
}
//Get workplace
if(empty($_POST["workorschool"])){
    $errors .= $missingworkorschool;
}else{
    $workorschool = filter_var($_POST["workorschool"], FILTER_SANITIZE_STRING);
}
//get gender
if(empty($_POST["gender"])){
  $errors .= $missingGender;
}else{
  $gender = $_POST["gender"];
}

//get moreinformation
if(empty($_POST["moreinformation"])){
  $errors .= $missingComment;
}else{
  $moreinformation = filter_var($_POST["moreinformation"], FILTER_SANITIZE_STRING);
}

//If there are any errors print error
if($errors){
    $resultMessage = '<div class="alert alert-danger">' . $errors .'</div>';
    echo $resultMessage;
    exit;
}

//no errors

//Prepare variables for the queries
$username = mysqli_real_escape_string($link, $username);
$email = mysqli_real_escape_string($link, $email);
$firstname = mysqli_real_escape_string($link, $firstname);
$lastname = mysqli_real_escape_string($link, $lastname);
$phonenumber = mysqli_real_escape_string($link, $phonenumber);
$workorschool = mysqli_real_escape_string($link, $workorschool);
$moreinformation = mysqli_real_escape_string($link, $moreinformation);
$password = mysqli_real_escape_string($link, $password);

//$password = md5($password);
$password = hash('sha256', $password);
//128 bits -> 32 characters
//256 bits -> 64 characters



//If username exists in the users table print error
$sql = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($link, $sql);
if(!$result){
    echo '<div class="alert alert-danger">Error running the query!</div>';
//    echo '<div class="alert alert-danger">' . mysqli_error($link) . '</div>';
    exit;
}
$results = mysqli_num_rows($result);
if($results){
    echo '<div class="alert alert-danger">That username is already registered. Do you want to log in?</div>';  exit;
}
//If email exists in the users table print error
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($link, $sql);
if(!$result){
    echo '<div class="alert alert-danger">Error running the query!</div>'; exit;
}
$results = mysqli_num_rows($result);
if($results){
    echo '<div class="alert alert-danger">That email is already registered. Do you want to log in?</div>';  exit;
}
//Create a unique  activation code
$activationKey = bin2hex(openssl_random_pseudo_bytes(16));
    //byte: unit of data = 8 bits
    //bit: 0 or 1
    //16 bytes = 16*8 = 128 bits
    //(2*2*2*2)*2*2*2*2*...*2
    //16*16*...*16
    //32 characters

//Insert user details and activation code in the users table

$sql = "INSERT INTO users (`username`, `email`, `password`, `activation`, `first_name`,`last_name`, `phonenumber`, `workorschool`, `gender`, `moreinformation`) VALUES ('$username', '$email', '$password', '$activationKey', '$firstname', '$lastname', '$phonenumber', '$workorschool', '$gender', '$moreinformation')";
$result = mysqli_query($link, $sql);
if(!$result){
    echo '<div class="alert alert-danger">There was an error inserting the users details in the database!</div>';
    exit;
}

//Send the user an email with a link to activate.php with their email and activation code
$message = "Please click on this link to activate your account:\n\n";
$message .= "http://csc400.thecompletewebhosting.com/drivvr/activate.php?email=" . urlencode($email) . "&key=$activationKey";
if(mail($email, 'Confirm your Registration', $message, 'From:'.'developmentisland@gmail.com')){
       echo "<div class='alert alert-success'>Thank for your registring! A confirmation email has been sent to $email. Please click on the activation link to activate your account.</div>";
}

        ?>
