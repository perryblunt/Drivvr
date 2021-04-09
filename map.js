var map;
var directionsDisplay;
//set map options//
var myLatLng = {lat:17.997, lng: -76.793};
var myOptions ={
  center: myLatLng,
  zoom: 15,
  mapTypeId: google.maps.MapTypeId.ROADMAP
}
//create autocomplete objects
var input1 = document.getElementById("departure");
var input2 = document.getElementById("destination");
var input3 = document.getElementById("departure2");
var input4 = document.getElementById("destination2");
// var options = {
//   types: ['(cities)']
// }

var autocomplete1 = new google.maps.places.Autocomplete(input1);
var autocomplete2 = new google.maps.places.Autocomplete(input2);
var autocomplete3 = new google.maps.places.Autocomplete(input3);
var autocomplete4 = new google.maps.places.Autocomplete(input4);

//create diectionService object to use route method
var directionsService = new google.maps.DirectionsService();
//Startof new field
//info windwow
var infowindow = new google.maps.InfoWindow();
var infowindowContent = document.getElementById('infowindow-content');
    infowindow.setContent(infowindowContent);
        var marker = new google.maps.Marker({
          map: map,
          anchorPoint: new google.maps.Point(0, -29)
        });
        //adding marker
function markDaSpot(){
infowindow.close();
marker.setVisible(false);
var place = autocomplete.getPlace();
  if (!place.geometry) {
    // User entered the name of a Place that was not suggested and
    // pressed the Enter key, or the Place Details request failed.
    window.alert("No details available for input: '" + place.name + "'");
    return;
  }

  // If the place has a geometry, then present it on a map.
  if (place.geometry.viewport) {
    map.fitBounds(place.geometry.viewport);
  } else {
    map.setCenter(place.geometry.location);
    map.setZoom(17);  // Why 17? Because it looks good.
  }
  marker.setPosition(place.geometry.location);
  marker.setVisible(true);

  var address = '';
  if (place.address_components) {
    address = [
      (place.address_components[0] && place.address_components[0].short_name || ''),
      (place.address_components[1] && place.address_components[1].short_name || ''),
      (place.address_components[2] && place.address_components[2].short_name || '')
    ].join(' ');
  }

  infowindowContent.children['place-icon'].src = place.icon;
  infowindowContent.children['place-name'].textContent = place.name;
  infowindowContent.children['place-address'].textContent = address;
  infowindow.open(map, marker);
};
//endof new install
//onload:
google.maps.event.addDomListener(window,'load', initialize);

//initialize draw map in the id googleMap div
function initialize(){

 //create a DirectionsRenderer object which will be used to display the route
    directionsDisplay = new google.maps.DirectionsRenderer();
//create map
    map = new google.maps.Map(document.getElementById("googleMap"),myOptions);
    //bind the DirectionsRenderer to the map
    directionsDisplay.setMap(map);
}

//calculate the route when selecting autocomplete
google.maps.event.addListener(autocomplete1, 'place_changed', calcRoute);
google.maps.event.addListener(autocomplete2, 'place_changed', calcRoute);
google.maps.event.addListener(autocomplete3, 'place_changed', calcRoute);
google.maps.event.addListener(autocomplete4, 'place_changed', calcRoute);

//calculate route
function calcRoute(){
   var start = $('#departure').val();
   var end= $('#destination').val();
   var request = {
            origin:start,
            destination: end,
            travelMode: google.maps.DirectionsTravelMode.DRIVING,
  };
  if(start && end){
    directionsService.route(request, function(response, status){
            if(status == google.maps.DirectionsStatus.OK){
              directionsDisplay.setDirections(response);
      }
    });
  }else{
    initialize();
  }

}
