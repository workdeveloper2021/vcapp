 $(document).ready(function() {
 	$('.dropify').dropify({
	    messages: {
	        'default': 'Drag and drop a file here or click',
	        'replace': 'Drag and drop or click to replace',
	        'remove': 'Remove',
	        'error': 'Ooops, something wrong appended.'
	    },
	    error: {
	        'fileSize': 'The file size is too big (1M max).'
	    }
	});

	$('.subCategoryAddRow').on('click', function(){ 
		$('.addotherrow').append('<div class="form-group row"><div class="col-11"><input type="text" id="" name="subcategory[]" class="form-control" placeholder="Sub Cateogry" required /></div><label class="col-1"><span class="removeAddedRow btn btn-icon waves-effect waves-light btn-success m-b-5"><i class="fa fa-minus"></i></span></label></div>');
    });

    $(document).on('click', '.removeAddedRow', function(){
      $(this).parent().parent().remove();
    });
	
 });



 	// 	function deleteData(postid, actionurl, returnurl){ 
	//   var r = confirm("Do you want to delete this?");
	//   if (r == true){
	//        $.ajax({
	//           type : "POST",
	//           url : actionurl,
	//           processData:false,
	//           contentType: false
	//       }).done(function(response) { 
	//         var result = JSON.parse(response);       
	//         if(result.result == '1'){
	//           window.location.href = returnurl;
	//         } 
	//       });	      
	//   } else {
	//      return false;
	//   } 
	// }

	function deleteData(postid, actionurl, returnurl){
	    swal({
	      title: "Are you sure?",
	      text: "Once deleted, you will not be able to revert this change.",
	      icon: "warning",
	      buttons: true,
	      dangerMode: true,
	    })
	    .then((willDelete) => {
	      if (willDelete) {
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
	      }
	    });
	}


	


	function changestatus(ids, status, urls, table, field,idField=''){

	 	var idField = (idField!='') ? idField : '';
	 	swal({
		    title: "Are you sure you want to change status ?",
		    // text: "Once deleted, you will not be able to recover this imaginary file!",
		    icon: "warning",
		    buttons: true,
		    dangerMode: true,
		})
	  	.then((willDelete) => {
		    if (willDelete) {
		       var formData = {
		            'ids': ids,
		            'status': status,
		            'table': table,
		            'field': field,
		            'idField': idField,
		        };
		        $.ajax({
		            type: 'POST',
		            url: urls,
		            dataType: 'json',
		            async: false,
		            data: formData,
		            success: function(data) {
		            	console.log(data)

		        if (data.isSuccess == true) {
                    refreshPge();   
                } else if(data.isSuccess == false && data.error == 'error' && data.message != ''){
                  swal(data.message);
                }else  {
                  swal("Server error, please try again!");
                }
		            },
		        });
		    } 
	  	});
	}
// update status 
	function check_and_status_change(ids, status, urls, table, field,idField='',verify_status=''){
	 	var idField = (idField!='') ? idField : '';
	 	if(verify_status ==0){
		 	swal({
			    title: "Are you sure you want to change status ?",
			    // text: "Once deleted, you will not be able to recover this imaginary file!",
			    icon: "warning",
			    buttons: true,
			    dangerMode: true,
			})
		  	.then((willDelete) => {
			    if (willDelete) {
			       var formData = {
			            'ids': ids,
			            'status': status,
			            'table': table,
			            'field': field,
			            'idField': idField,
			        };
			        $.ajax({
			            type: 'POST',
			            url: urls,
			            dataType: 'json',
			            async: false,
			            data: formData,
			            success: function(data) {
			            	console.log(data)

			        if (data.isSuccess == true) {
	                    refreshPge();   
	                } else if(data.isSuccess == false && data.error == 'error' && data.message != ''){
	                  swal(data.message);
	                }else  {
	                  swal("Server error, please try again!");
	                }
			            },
			        });
			    } 
		  	});
	  	  }else{
            if(verify_status==1){
            	var title_text="Email not verify for this user";
                var doc_text="Please verify email first"; 
            }else if(verify_status==2){
                var title_text="Mobile no. not verify for this user";
                var doc_text="Please verify mobile no. first ";
            }else{
                var title_text="Email and mobile no. not verify for this user";
                 var doc_text="Please verify email and mobile no. first";
            }
          swal({html:true, title:title_text,text:doc_text,type: "warning"});
     }
	}
function check_and_status_change12(ids, status, urls, table, field,idField='',$verify_status=''){
    
       if(verify_status ==0){
          swal({
            title: "Do you want to change status",
            type: "warning",
            showCancelButton: true,
            cancelButtonText: "Cancel",
            customClass: 'swal-wide',
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "yes change it",
            closeOnConfirm: false,
        }, function() {
            if (status == 0) {
                var status_text = "Activated";
            } else {
                var status_text = "Inactivated";
            }
            swal(status_text, "Status change success", "success");

            var formData = {
                'ids': ids,
                'status': status,
                'table': table,
                'field': field,
                'del_Id': del_Id,
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
                        $("#ErrorStatus").html('');
                    }
                },
            });
        });

     }else{
            if(verify_status==1){
                var title_text="Mobile No. not verify for this user";
                var doc_text="Please Verify Mobile No. first ";
            }else if(verify_status==2){
                var title_text="Email not verify for this user";
                var doc_text="Please Verify Email first";
            }else{
                var title_text="Email and Mobile No. not verify for this user";
                 var doc_text="Please Verify Email and Mobile No. first";
            }
       swal({html:true, title:title_text,text:doc_text,type: "warning"});
     }
     
}

	// page refresh
	function refreshPge() {
	    window.location.href = window.location.href;
	}

	/*function deleteData(postid, actionurl, returnurl){
	    swal({
	      title: "Are you sure?",
	      text: "Once deleted, you will not be able to recover this imaginary file!",
	      icon: "warning",
	      buttons: true,
	      dangerMode: true,
	    })
	    .then((willDelete) => {
	      if (willDelete) {
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
	      }
	    });
	} */

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

	/* Category Extantion Check */
	function categoryInfoData(cid, url){
		var controller_name = url;
		var cat_id = cid;
		$.ajax({
	          type : "post",
	          url : url,
	          data : {
	          	cat_id: cat_id
	          },
	      }).done(function(response) { 
	      	$('.catmessage').remove();
	        var result = JSON.parse(response);
	        if(result.status == 'ok'){
	        	$('.uploadtype').val(result.ftype);
	        	if(result.type == 2){
	        		$('.desc_section').addClass('d-none');
	        		$('.file_section').removeClass('d-none');
	        	} else {
	        		$('.file_section').addClass('d-none');
	        		$('.desc_section').removeClass('d-none');
	        	}
	          	$( "<div class='alert alert-info catmessage hide_message' role='alert'>"+result.message+"</div>" ).insertAfter( "#sel1");

	          	setTimeout(function() {
				    $('.hide_message').fadeOut('slow');
				}, 7000);
	        } else {
	          //$('#'+message).html(result.msg);
	        }
	      });

	}
	
	$("#sel1").change(function () { 
	    var selectedValue = $(this).val();
	});

/*  Survey module js */
$(document).ready(function () {
      var qval = '';
      qval = $('.cval').attr('qval');
      $(".add_more_value").click(function(){
            if(qval < 4){
              qval++;
              qval = $('.cval').attr('qval',qval);
              qval = $('.cval').attr('qval');
              var lablename = "Answer";
              var placehplder = "Please provide answer";
              var htmlcontent = '<div class="form-group row option_section"><label class="col-2 col-form-label">'+lablename+'</label><div class="col-7"><input class="form-control" type="text" name="option_name[]" required="" placeholder="'+lablename+'" data-parsley-error-message="'+placehplder+'"></div><div class="col-3"><div class="form-group addmore-btn-box"><a href="javascript:void(0)" class="btn border-btn btn-danger removervalue"> Remove </a></div></div></div>';
              $( htmlcontent ).insertBefore( ".add_more_value_section" );
            }else{
               swal("You can not add more than 4 questions value");
            }
            
            //alert(qval)
      });

      $("body").on("click",".removervalue",function(){ 
          $(this).closest('.option_section').remove();
            qval = $('.cval').attr('qval');
            qval--;
            qval = $('.cval').attr('qval',qval);
            qval = $('.cval').attr('qval');
             
      });
       
    });

/* End survey module js */

/* Reward module js */
$(document).ready(function() {
  var countRow = $('.tot_reward').attr('rewar');
  //alert(countRow);
  $(document).on('click','.addreward',function(e){ 
   countRow++;
    var lab_reward_name = "Reward Name";
    var lab_reward_number = "Reward Number";
    var lab_sequence = "Sequence";
      e.preventDefault();
  
      var htmlcontent = '';
     
        htmlcontent  += '<div class="row rowInfodoc_id" id="addDiv2'+countRow+'">';
          htmlcontent  += '<div class="col-sm-4">';
            htmlcontent  += '<div class="form-group">';
              htmlcontent  += '<label class="col-sm-12 control-label">'+lab_reward_name+'</label>';
              htmlcontent  += '<div class="col-sm-12">';
                htmlcontent  += '<input type="text" name="rewardname[]" class="form-control rname" value="" placeholder="Reward Name">';
              htmlcontent  += '</div>';
            htmlcontent  += '</div>';
          htmlcontent  += '</div>';

          htmlcontent  += '<div class="col-sm-4">';
             htmlcontent  += '<div class="form-group">';
              htmlcontent  += '<label  class="col-sm-12 control-label">'+lab_reward_number+'</label>';
              htmlcontent  += '<div class="col-sm-12">';
                htmlcontent  += '<input type="number" name="rewardnumber[]" class="form-control rnumber" placeholder="Sequence">';
              htmlcontent  += '</div>';
            htmlcontent  += '</div>';
          htmlcontent  += '</div>';

          htmlcontent  += '<div class="col-sm-4">';
             htmlcontent  += '<div class="form-group">';
              htmlcontent  += '<label  class="col-sm-12 control-label">'+lab_sequence+'</label>';
                htmlcontent  += '<div class="col-sm-12">';
                      htmlcontent  += '<select name="rewardsequence[]" class="form-control rsequence" id="reward_sequence_'+countRow+'" required="">';
                        htmlcontent  += '<option value="">-- Select Sequence --</option>';
                        htmlcontent  += '<option value="1">1</option>';
                        htmlcontent  += '<option value="2">2</option>';
                        htmlcontent  += '<option value="3">3</option>';
                        htmlcontent  += '<option value="4">4</option>';
                        htmlcontent  += '<option value="5">5</option>';
                        htmlcontent  += '<option value="6">6</option>';
                        htmlcontent  += '<option value="7">7</option>';
                        htmlcontent  += '<option value="8">8</option>';
                        htmlcontent  += '<option value="9">9</option>';
                        htmlcontent  += '<option value="10">10</option>';
                      htmlcontent  += '</select>';
                     
                    htmlcontent  += '</div>';
            htmlcontent  += '</div>';
          htmlcontent  += '</div>';

          
        htmlcontent  += '</div>';
      
      $('.rowAdjust_2').append(htmlcontent);
     });

     $(document).on('click','.removereward',function(e){
        e.preventDefault(); 
         var rowId   = $(this).attr('reward_row');
        $('#addDiv2'+rowId).remove();
        countRow--;
      });


    $('.reward_form .rsequence').on('change', function() {
      var sequence = $(this).val();
      var sid = $(this).attr('id');
      var set = 0;
      var j = 0;
      var fid = 'reward_sequence_'
        $( ".reward_form select" ).each(function( index ) {
           var fid = 'reward_sequence_'+j;
          if(fid != sid){
              if(sequence == $(this).val()){
                set = 1;
                $('#'+sid).children().removeAttr("selected");
                $('#'+sid).val('');
                return false;
            }
          }
        
        j++;
      });
        
        if(set == 1){
          swal('This sequence number is already used by other fields, please try other sequence number');
          $('#'+sid).val('');
        }
    });
});
/* End js*/

/* Add loader on form submit page */
$(document).ready(function () {
  $(".add_loader").submit(function (e) {
      $('#add_loader_form_submit').show();
  });
})
/* End */




/* Add loader on form submit page */
$(document).ready(function () {  
	$(document).on('change', '#checkall', function () {
	    $('.cb-element').prop('checked',this.checked);
	     var val = [];
	        $('.cb-element:checked').each(function(i){
	          val[i] = $(this).val();
	        });
	        $(".getIds").val(val);
	       
	});
	// check single and all
	$(document).on('change', '.cb-element', function() {
	     var val = [];
	  $(':checkbox:checked').each(function(i){
	          val[i] = $(this).val();
	        });
	  
	        $(".getIds").val(val);
	     if ($('.cb-element:checked').length == $('.cb-element').length){
	          $('#checkall').prop('checked',true);
	         }
	         else {
	          $('#checkall').prop('checked',false);
	     }
	});
})
/* End */



// **********************DATATABLE FUNCTION***************
function setTable(table_id,ajax_url,sort_table=''){
    return $(table_id).DataTable({
        "bPaginate": true,
        "bLengthChange": true,
        "bFilter": true,
        "bSort": true,
        "bInfo": true,
        "bAutoWidth": false,
        "processing": true,
        "serverSide": true,
        "stateSave": false,
        "order": [ 0, "desc" ],
        "ajax": ajax_url,
        "columnDefs": [ { "targets": 1, "bSortable": true,"orderable": true, "visible": true } ],
        'aoColumnDefs': [{ 'bSortable': false,'aTargets': sort_table}],
    });
}

  $('.text_name').keyup(function(){
       var $th = $(this);
        $th.val( $th.val().replace(/[^a-zA-Z]/g, function(str) { 
          return '';
           } 
           ) );
    });
  
