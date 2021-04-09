//Ajax Call for the sign up form
//Once the form is submitted
$("#signupform").submit(function(event){
  //show spinner
  $('#spinner').show();
  //hide results
  $("#signupmessage").hide();
    //prevent default php processing
    event.preventDefault();
    //collect user inputs
    var datatopost = $(this).serializeArray();
//    console.log(datatopost);
    //send them to signup.php using AJAX
    $.ajax({
        url: "signup.php",
        type: "POST",
        data: datatopost,
        success: function(data){
            if(data){
              //hide spinner
              $('#spinner').hide();
              $("#signupmessage").html(data);
                $("#signupmessage").slideDown();

            }
        },
        error: function(){
          //hide spinner
            $('#spinner').hide();
            $("#signupmessage").html("<div class='alert alert-danger'>There was an error with the Ajax Call. Please try again later.</div>");
            $("#signupmessage").slideDown();

        }

    });

});

//Ajax Call for the login form
//Once the form is submitted
$("#loginform").submit(function(event){
  //show spinner
  $('#spinner').show();
  //hide results
    $("#loginmessage").hide();
    //prevent default php processing
    event.preventDefault();
    //collect user inputs
    var datatopost = $(this).serializeArray();
//    console.log(datatopost);
    //send them to login.php using AJAX
    $.ajax({
        url: "login.php",
        type: "POST",
        data: datatopost,
        success: function(data){
            if(data == "success"){
                window.location = "mainpageloggedin.php";
            }else{
              //hide spinner
                $('#spinner').hide();
                $('#loginmessage').html(data);
                $('#loginmessage').slideDown();
            }
        },
        error: function(){
          //hide spinner
            $('#spinner').hide();
            $("#loginmessage").html("<div class='alert alert-danger'>There was an error with the Ajax Call. Please try again later.</div>");
            $('#loginmessage').slideDown();

        }

    });

});


//Ajax Call for the forgot password form
//Once the form is submitted
$("#forgotpasswordform").submit(function(event){
  //show spinner
  $('#spinner').show();
  //hide results
  $("#forgotpasswordmessage").hide();
    //prevent default php processing
    event.preventDefault();
    //collect user inputs
    var datatopost = $(this).serializeArray();
//    console.log(datatopost);
    //send them to signup.php using AJAX
    $.ajax({
        url: "forgot-password.php",
        type: "POST",
        data: datatopost,
        success: function(data){
          $('#spinner').hide();
          $('#forgotpasswordmessage').html(data);
          $('#forgotpasswordmessage').slideDown();


            $('#forgotpasswordmessage').html(data);
        },
        error: function(){
            $("#forgotpasswordmessage").html("<div class='alert alert-danger'>There was an error with the Ajax Call. Please try again later.</div>");
            $("#forgotpasswordmessage").slideDown();

        }

    });

});
// create a geocoder object to use geocode to get lng and lat
var geocoder = new google.maps.Geocoder();
var data;
//submit the search formatModal
$("#searchForm").submit(function(event){
  //show spinner
  $('#spinner').show();
  //hide results
  $('#searchResults').fadeOut();
  event.preventDefault();
  data = $(this).serializeArray();
  getSearchTripDepartureCoordinates();
});

//define functions
function getSearchTripDepartureCoordinates() {
  geocoder.geocode({
    'address': document.getElementById("departure").value
  },
  function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      //console.log(results);
      departureLongitude = results[0].geometry.location.lng();
      data.push({name: 'departureLongitude', value: departureLongitude});
      departureLatitude = results[0].geometry.location.lat();
      data.push({name: 'departureLatitude', value: departureLatitude});
      //console.log(data);
      getSearchTripDestinationCoordinates();
    } else {
      getSearchTripDestinationCoordinates();

    }
  });
}


// getAddTripDestinationCoordinates function
function getSearchTripDestinationCoordinates() {
  geocoder.geocode({
    'address': document.getElementById("destination").value
  },
  function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      //console.log(results);
      destinationLongitude = results[0].geometry.location.lng();
      data.push({name: 'destinationLongitude', value: destinationLongitude});
      destinationLatitude = results[0].geometry.location.lat();
      data.push({name: 'destinationLatitude', value: destinationLatitude});
      //console.log(data);
      submitSearchTripRequest();
    } else {
      submitSearchTripRequest();
    }
  }
 );
}
// Submit trip ajax call
function submitSearchTripRequest(){
  //send AJAX call to addtrips.php
  $.ajax({
    url: "search.php",
    data: data,
    type: "POST",
    success: function(returnedData) {
            //hide spinner
          $('#spinner').hide();
          $('#searchResults').html(returnedData);
          $('#tripResults').accordion({
            active: false,
            collapsible: true,
            heightStyle: "content",
            icons: false
          });

          //show resulta\s
          $('#searchResults').fadeIn();


  },
    error: function() {
        //hide spinner
        $('#spinner').hide();
      $("#searchResults").html("<div class='alert alert-danger'>There was an error with the Ajax Call. Please try again later.</div>");
      //show resulta\s
      $('#searchResults').fadeIn();
    }
    });
   }
