// defined variable for objects
var data;
var departure;
var departureLongitude;
var departureLatitude;
var destinationLongitude;
var destinationLatitude;
var destination;
var trip;

//function to display trips in dtabase
getTrips();

// create a geocoder object to use geocode to get lng and lat
var geocoder = new google.maps.Geocoder();

$(function() {
  //Fix map
  $('#addtripModal').on('shown.bs.Modal', function() {
    google.maps.event.trigger(map, "resize");
  });
});

//Hide all date and time element
$('.regular').hide();
$('.one-off').hide();
$('.regular2').hide();
$('.one-off2').hide();

var myRadio = $('input[name="regular"]');

myRadio.click(function() {
  if ($(this).is(':checked')) {
    if ($(this).val() == "Y") {
      $('.one-off').hide();
      $('.regular').show();
    } else {
      $('.regular').hide();
      $('.one-off').show();
    }
  }
});

// Edit Trips
var myRadio = $('input[name="regular2"]');

myRadio.click(function() {
  if ($(this).is(':checked')) {
    if ($(this).val() == "Y") {
      $('.one-off2').hide();
      $('.regular2').show();
    } else {
      $('.regular2').hide();
      $('.one-off2').show();
    }
  }
});

//Calender
$('input[name="date"], input[name="date2"]').datepicker({
  numberOfMonths: 1,
  showAnim: "fadeIn",
  dateFormat: "D d M, yy",
  minDate: + 1,
  maxDate: "+12M",
  showWeek: true
});

//Click on create Trip buttons
$('#addtripform').submit(function(event) {
  //show spinner
  $('#spinner').show();
  //hide resulta\s
  $('#addtripmessage').hide();
  event.preventDefault();
  data = $(this).serializeArray();
  //console.log(data);
  getAddTripDepartureCoordinates();
});

//Define functios

// getAddTripDepartureCoordinates function
function getAddTripDepartureCoordinates() {
  geocoder.geocode({
    'address': document.getElementById("departure").value
  }, function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      //console.log(results);
      departureLongitude = results[0].geometry.location.lng();
      departureLatitude = results[0].geometry.location.lat();
      //window.alert(departureLongitude );
      //window.alert(departureLatitude);
      data.push({name: 'departureLongitude', value: departureLongitude});
      data.push({name: 'departureLatitude', value: departureLatitude});
      //console.log(data);
      getAddTripDestinationCoordinates();
    } else {
      getAddTripDestinationCoordinates();

    }
  });
}

// getAddTripDestinationCoordinates function
function getAddTripDestinationCoordinates() {
  geocoder.geocode({
    'address': document.getElementById("destination").value
  }, function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      //console.log(results);
      destinationLongitude = results[0].geometry.location.lng();
      destinationLatitude = results[0].geometry.location.lat();
      //window.alert(departureLongitude );
      //window.alert(departureLatitude);
      data.push({name: 'destinationLongitude', value: destinationLongitude});
      data.push({name: 'destinationLatitude', value: destinationLatitude});
      //console.log(data);
      submitAddTripRequest();
    } else {
      submitAddTripRequest();
    }
  }
 );
}
// Submit trip ajax call
function submitAddTripRequest() {
  //send AJAX call to addtrips.php
  $.ajax({
    url: "addtrips.php",
    data: data,
    type: "POST",
    success: function(returnedData) {
        //hide spinner
      $('#spinner').hide();
      if (returnedData) {
        $("#addtripmessage").html(returnedData);
        $("#addtripmessage").slideDown();
        //console.log(data);
        //console.log(returnedData);
        //hide modal
        // $('#addtripModal').modal('hide');
        // //reset form
        // $('#addtripform')[0].reset();
        //
        // getTrips();
      }else{
        $('#addtripModal').modal('hide');
        //reset form
        $('#addtripform')[0].reset();
        $(".regular").hide();
        $(".one-off").hide();

        getTrips();
    }

  },
    error: function() {
      //hide spinner
      $('#spinner').hide();
      $("#addtripmessage").html("<div class='alert alert-danger'>There was an error with the Ajax Call. Please try again later.</div>");
      $("#addtripmessage").slideDown();
    }
    });
   }

//Retrieve data from form to add to edit trip modal
function formatModal() {
  $('#departure2').val(trip['departure']);
  $('#destination2').val(trip['destination']);
  $('#price2').val(trip['price']);
  $('#seatsavailable2').val(trip['seatsavailable']);
  $('textarea[name="moreinformation2"]').val(trip['moreinformation']);
  if (trip['regular'] == "Y") {
    $('#yes2').prop('checked', true);
    $('#monday2').prop('checked', trip['monday'] == "1"? true:false);
    $('#tuesday2').prop('checked', trip['tueday'] == "1"? true:false);
    $('#wednesday2').prop('checked', trip['wednesday'] == "1"? true:false);
    $('#thursday2').prop('checked', trip['thursday'] == "1"? true:false);
    $('#friday2').prop('checked', trip['friday'] == "1"? true:false);
    $('#saturday2').prop('checked', trip['saturday'] == "1"? true:false);
    $('#sunday2').prop('checked', trip['sunday'] == "1"? true:false);
    $('input[name="time2"]').val(trip["time"]);
    $('.one-off2').hide();
    $('.regular2').show();
  } else {
    $('#no2').prop('checked', true);
    $('input[name="date2"]').val(trip["date"]);
    $('input[name="time2"]').val(trip["time"]);
    $('.regular2').hide();
    $('.one-off2').show();

  }

}
//get departure coordinates for edit trip modal
function getEditTripDepartureCoordinates() {
  geocoder.geocode({
    'address': document.getElementById("departure2").value
  }, function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      //console.log(results);
      departureLongitude = results[0].geometry.location.lng();
      departureLatitude = results[0].geometry.location.lat();
      //window.alert(departureLongitude );
      //window.alert(departureLatitude);
      data.push({name: 'departureLongitude', value: departureLongitude});
      data.push({name: 'departureLatitude', value: departureLatitude});
      //console.log(data);
      getEditTripDestinationCoordinates();
    } else {
      getEditTripDestinationCoordinates();

    }
  });
}
//get destination coordinates for edit trip modal
function getEditTripDestinationCoordinates() {
  geocoder.geocode({
    'address': document.getElementById("destination2").value
  }, function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      //console.log(results);
      destinationLongitude = results[0].geometry.location.lng();
      destinationLatitude = results[0].geometry.location.lat();
      //window.alert(departureLongitude );
      //window.alert(departureLatitude);
      data.push({name: 'destinationLongitude', value: destinationLongitude});
      data.push({name: 'destinationLatitude', value: destinationLatitude});
      //console.log(data);
      submitEditTripRequest();
    } else {
      submitEditTripRequest();
    }
  });
}
 //send AJAX call to updatetrips.php
function submitEditTripRequest() {

  $.ajax({
    url: "updatetrips.php",
    data: data,
    type: "POST",
    success: function(returnedData) {
      //hide spinner
      $('#spinner').hide();
      if (returnedData) {

        $("#edittripmessage").html(returnedData);
        $("#edittripmessage").slideDown();
        //console.log(data);
        //console.log(returnedData);
        //hide modal
        // $('#edittripModal').modal('hide');
        //reset form
        //$('#edittripform')[0].reset();
        //load trips
        // getTrips();

      }else{
          // //hide modal
          $('#edittripModal').modal('hide');
           //reset form
          $('#edittripform')[0].reset();
          // $('#edittripform')[0].reset();
          //load trips
          getTrips();
        }


    },
    error: function() {
      //hide Spinner
      $('#spinner').hide();
      $("#edittripmessage").html("<div class='alert alert-danger'>There was an error with the Ajax Call. Please try again later.</div>");
      $("#edittripmessage").slideDown();

    }

  });
}
// get Trips
function getTrips() {
  //show spinner
  $('#spinner').show();
  //send ajax call to getTrips.php
  $.ajax({
    url: "getrips.php",
    success: function(returnedData) {
      //hide spinner
      $('#spinner').hide();
      $("#mytrips").hide();
      $("#mytrips").html(returnedData);
      $("#mytrips").fadeIn();
    },
    error: function() {
      //hide spinner
      $('#spinner').hide();
      $("#mytrips").hide();
      $("#mytrips").html("<div class='alert alert-danger'>There was an error with the Ajax Call. Please try again later.</div>");
      $("#mytrips").fadeIn();

    }

  });

};

//click on Edit Button inside a trip
$("#edittripModal").on('show.bs.modal', function(event) {
  $("#edittripmessage").empty();
  //   //button to open modal
    var invoker = $(event.relatedTarget);
    //console.log(invoker);
    //ajax call to get details of the trip
        //submit edit form
      $.ajax({
        url: "gettripdetails.php",
        method: "POST",
        data: {trip_id: invoker.data('trip_id')},
        success: function(returnedData) {
          if (returnedData) {
                   //Check if anything has returned first
                  if (returnedData == "error"){
                    $("#edittripmessage").html("<div class='alert alert-danger'>There was an error with the Ajax Call. Please try again later.</div>");
                }else{
                  //JSON Method to change object data into array then stores into trip
                    trip = JSON.parse(returnedData);
                    //console.log(trip);
                    //fill form with trip details using parsed data
                    formatModal();
                  }
                }

        },
         error: function(){

         $("#edittripmessage").html("<div class='alert alert-danger'>There was an error with the Ajax Call. Please try again later.</div>");
         }


      });
      //submit edit form
      $('#edittripform').submit(function(event){
        //show spinner
        $('#spinner').show();
        //hide resulta\s
        $("#edittripmessage").hide()
        //$("#edittripmessage").empty();
        event.preventDefault();
        data = $(this).serializeArray();
        data.push({name:'trip_id', value: invoker.data('trip_id')});
        //console.log(data);
        getEditTripDepartureCoordinates();

      });

      //delete a trips

      $("#deleteTrip").click(function(){
        //show spinner
        $("#spinner").show();
        //hide error messages
        $("#edittripmessage").hide();

        $.ajax({
          url: "deletetrips.php",
          method: "POST",
          data: {trip_id: invoker.data('trip_id')},
          success: function(returnedData) {
            //hide Spinner after server respond
            $("#spinner").hide();
            if (returnedData) {
                     $("#edittripmessage").html("<div class='alert alert-danger'>The trip could not be deleted. Please try again.</div>");
                     $("#edittripmessage").slideDown();
                  }else{
                    $("#edittripModal").modal('hide');
                    getTrips();
                  }

          },
           error: function(){
          $("#spinner").hide();
           $("#edittripmessage").html("<div class='alert alert-danger'>There was an error with the Ajax Call. Please try again later.</div>");
           $("#edittripmessage").slideDown();
           }


        });

      })

});
