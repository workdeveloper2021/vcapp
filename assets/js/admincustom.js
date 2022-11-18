
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


// update ststus
function sweetalert(ids, status, urls, table, field, suc_msg,idField='') {
  var suc_msg = (suc_msg!='') ? suc_msg : '';    
    swal({
        title: "Do you want to change status?",
        type: "warning",
        showCancelButton: true,
        cancelButtonText: "Cancel",
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes change it",
        closeOnConfirm: false,
    }, function() {
        swal("Deleted!", "Your imaginary file has been deleted.", "success");
    // swal(suc_msg, 'Status change success', 'Success');
    var formData = {
            'ids': ids,
            'status': status,
            'idField': idField,
            'table': table,
            'field': field,
        };
        $.ajax({
            type: 'POST',
            url: urls,
            dataType: 'json',
            async: false,
            data: formData,
            success: function(data) {
        if (data.isSuccess == true) {
                    refreshPge();
                } else {
                    // failed condition code here
                }
            },
        });
    });
}
// update ststus
function delete_data(ids, status, urls, table, field, suc_msg,idField='') {
  var suc_msg = (suc_msg!='') ? suc_msg : '';    
    swal({
        title: "Do you want to delete this?",
        type: "warning",
        showCancelButton: true,
        cancelButtonText: "Cancel",
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes,delete it",
        closeOnConfirm: false,
    }, function() {
        swal("Deleted!", "deleted succesfully.", "success");
    // swal(suc_msg, 'Status change success', 'Success');
    var formData = {
            'ids': ids,
            'status': status,
            'idField': idField,
            'table': table,
            'field': field,
        };
        $.ajax({
            type: 'POST',
            url: urls,
            dataType: 'json',
            async: false,
            data: formData,
            success: function(data) {
        if (data.isSuccess == true) {
                  refreshPge();
                } else {
                    // failed condition code here
                }
            },
        });
    });
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



// page refresh
function refreshPge() {
    window.location.href = window.location.href;
}
