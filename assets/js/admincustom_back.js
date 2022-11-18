
 jQuery(document).ready(function($) {
   $('.replaycomment').hide();
   $('.replaybtnn').on('click', function(){
      $(this).parent().parent().find('.replaycomment').toggle();
   });
 });


function deleteData(postid, actionurl, returnurl){
  var r = confirm("Do you want to delete this?");
  if (r == true){
       $.ajax({
          type : "POST",
          url : actionurl,
          processData:false,
          contentType: false
      }).done(function(response) { 
        var result = JSON.parse(response);       
        if(result.result == '1'){
          window.location.href = returnurl;
        } 
      });
  } else {
     return false;
  }
}

function saveData(frmid, frmaction, redirection, message = ''){
     $('#'+message).html('');
      var formData1 = new FormData($('#'+ frmid)[0]);
      $.ajax({
          type : "POST",
          url : frmaction,
          data : formData1,
          processData:false,
          contentType: false
      }).done(function(response) { 
        var result = JSON.parse(response);
        if(result.result == '1'){
          window.location.href = redirection;
        } else {
          $('#'+message).html(result.msg);
        }
      });
}

function srchfunction(frmid, frmaction, responce){
      $('#'+responce).html('');
      var formData1 = new FormData($('#'+ frmid)[0]);
      $.ajax({
          type : "POST",
          url : frmaction,
          data : formData1,
          processData:false,
          contentType: false
      }).done(function(response) {
        var result = JSON.parse(response);
        $('#'+responce).html(result.data);
      });
}

function getemployeedtl(compid, frmaction, responce, obj){
  _this = $(obj);
  $('#srchcompany').find('tr').removeClass('active');
  _this.parents('tr').addClass('active');
    $('#'+responce).html('');
    $.ajax({
        type : "POST",
        url : frmaction,
        data : { compid:compid },
    }).done(function(resp) {
      console.log(resp);
      var result = JSON.parse(resp);
      $('#'+responce).html(result.data);
    });
}

$('body').on('focus',".form_date", function(){
    $('.form_date').datetimepicker({
      language:  'en',
      weekStart: 1,
      todayBtn:  1,
      autoclose: 1,
      todayHighlight: 1,
      startView: 2,
      minView: 2,
      forceParse: 0
    });
  });

  $('body').on('focus',".form_time", function(){
    $('.form_time').datetimepicker({
      language:  'en',
      weekStart: 1,
      todayBtn:  1,
      autoclose: 1,
      todayHighlight: 1,
      startView: 1,
      minView: 0,
      maxView: 1,
      forceParse: 0
    });
  });


/* Google Auto Complete Location  */


// This example displays an address form, using the autocomplete feature
// of the Google Places API to help users fill in the information.

var placeSearch, autocomplete1;
var componentForm = {
    street_number: 'short_name',
    route: 'long_name',
    locality: 'long_name',
    administrative_area_level_1: 'short_name',
    country: 'long_name',
    postal_code: 'short_name'
};

function initialize() {
    // Create the autocomplete object, restricting the search
    // to geographical location types.
    autocomplete1 = new google.maps.places.Autocomplete(
    /** @type {HTMLInputElement} */ (document.getElementById('autocomplete1')), {
        //types: ['geocode']
        types: ['geocode', 'establishment']
    });
    // When the user selects an address from the dropdown,
    // populate the address fields in the form.
    google.maps.event.addListener(autocomplete1, 'place_changed', function () {
        fillInAddress();

    });

}

function fillInAddress() {
    // Get the place details from the autocomplete object.
    var place = autocomplete1.getPlace();
    var formatted_address = place.formatted_address;
    document.getElementById("latitude").value = place.geometry.location.lat();
    document.getElementById("longitude").value = place.geometry.location.lng();
    /*document.getElementById("formatted_address").value = formatted_address;

    for (var component in componentForm) {
        document.getElementById(component).value = '';
        document.getElementById(component).disabled = false;
    }*/

    // Get each component of the address from the place details
    // and fill the corresponding field on the form.
    /*for (var i = 0; i < place.address_components.length; i++) {
        var addressType = place.address_components[i].types[0];
        if (componentForm[addressType]) {
            var val = place.address_components[i][componentForm[addressType]];
            document.getElementById(addressType).value = val;
        }
    }*/
}

// Bias the autocomplete object to the user's geographical location,
// as supplied by the browser's 'navigator.geolocation' object.
function geolocate() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            var geolocation = new google.maps.LatLng(
            position.coords.latitude, position.coords.longitude);

            var latitude = position.coords.latitude;
            var longitude = position.coords.longitude;
            document.getElementById("latitude").value = latitude;
            document.getElementById("longitude").value = longitude;

            autocomplete.setBounds(new google.maps.LatLngBounds(geolocation, geolocation));
        });
    }

}
initialize();



