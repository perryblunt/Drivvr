// Ajax call to updateusername.php
$("#updateusernameform").submit(function(event){
    //prevent default php processing
    event.preventDefault();
    //collect user inputs
    var datatopost = $(this).serializeArray();
//    console.log(datatopost);
    //send them to updateusername.php using AJAX
    $.ajax({
        url: "updateusername.php",
        type: "POST",
        data: datatopost,
        success: function(data){
            if(data){
                $("#updateusernamemessage").html(data);
            }else{
                location.reload();
            }
        },
        error: function(){
            $("#updateusernamemessage").html("<div class='alert alert-danger'>There was an error with the Ajax Call. Please try again later.</div>");

        }

    });

});

// Ajax call to updatepassword.php
$("#updatepasswordform").submit(function(event){
    //prevent default php processing
    event.preventDefault();
    //collect user inputs
    var datatopost = $(this).serializeArray();
//    console.log(datatopost);
    //send them to updateusername.php using AJAX
    $.ajax({
        url: "updatepassword.php",
        type: "POST",
        data: datatopost,
        success: function(data){
            if(data){
                $("#updatepasswordmessage").html(data);
            }
        },
        error: function(){
            $("#updatepasswordmessage").html("<div class='alert alert-danger'>There was an error with the Ajax Call. Please try again later.</div>");

        }

    });

});



// Ajax call to updateemail.php
$("#updateemailform").submit(function(event){
    //prevent default php processing
    event.preventDefault();
    //collect user inputs
    var datatopost = $(this).serializeArray();
//    console.log(datatopost);
    //send them to updateusername.php using AJAX
    $.ajax({
        url: "updateemail.php",
        type: "POST",
        data: datatopost,
        success: function(data){
            if(data){
                $("#updateemailmessage").html(data);
            }
        },
        error: function(){
            $("#updateemailmessage").html("<div class='alert alert-danger'>There was an error with the Ajax Call. Please try again later.</div>");

        }

    });

});

//update picture preview
var file;
var imageType;
var imageSize;
var wrongType;
$("#picture").change(function(){
  file = this.files[0];
  imageType = file.type;
  imageSize = file.size;
  //image type
  var acceptableTypes = ["image/jpeg", "image/png", "image/jpg"];
  wrongType = ($.inArray(imageType, acceptableTypes) == -1);
  if(wrongType){
    $("#updatepicturemessage").html("<div class='alert alert-danger'> I'm sorry! try using only jpeg, png, and jpg.</div>");
    return false;
  }
  //check image size
  if(imageSize>3*1024*1024){
    $("#updatepicturemessage").html("<div class='alert alert-danger'> I'm sorry! try image less than 3mb.</div>");
    return false;
  }
  //the file reader object will be used to convert image to binary string
  var reader = new FileReader();
  //callback
  reader.onload = updatePreview;
  //Start read operation then convert into data url which is passed to the callback
  reader.readAsDataURL(file);
});


function updatePreview(event){
  //console.log(event);
  $("#preview2").attr("src", event.target.result);
}

//update picture
//var file;
$("#updatepictureform").submit(function(){
  event.preventDefault();
  //file missing
  if(!file){
    $("#updatepicturemessage").html("<div class='alert alert-danger'> Please upload a picture.</div>");
    return false;
  }
  //wrong file
  if(wrongType){
    $("#updatepicturemessage").html("<div class='alert alert-danger'> I'm sorry! try using only jpeg, png, and jpg.</div>");
    return false;
  }
  //wrong file too big
  if(imageSize>3*1024*1024){
    $("#updatepicturemessage").html("<div class='alert alert-danger'>I'm sorry! try image less than 3mb.</div>");
    return false;
  }

  // var test = new FormData(this);
  // console.log(test.get("picture"));



  // send them to updatepicture.php using AJAX
  $.ajax({
      url: "updatepicture.php",
      type: "POST",
      data: new FormData(this),
      contentType: false,       // The content type used when sending data to the server.
      cache: false,             // To unable request pages to be cached
      processData:false,        // To send DOMDocument or non processed data file it is set to false
      success: function(data){
          if(data){
              $("#updatepicturemessage").html(data);
          //     //hide spinner
          //     // $("#spinner").css("display", "none");
          //     //show message
          //     $("#updatepicturemessage").slideDown();
              //update picture in the settings
          }else{
              location.reload();

          }
      //
      },
      error: function(){
          $("#updatepicturemessage").html("<div class='alert alert-danger'>There was an error with the Ajax Call. Please try again later.</div>");
          //hide spinner
          // $("#spinner").css("display", "none");
          //show message
          // $("#signupmessage").slideDown();

      }
  });



});
