$(document).ready(function(){
  /*=============smoothscroll==============*/  
  $('.buttons_main').click(function(){  
      $('html, body').animate({      
          scrollTop: $( $.attr(this, 'href') ).offset().top  }, 500);
           return false;
  });
  $('.sign_leftbanner2 li a').click(function(){  
    $($.attr(this, 'href'))
  });
  /*=============smoothscroll==============*/ 
});

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
        //alert(response); 
        console.log(response);
        var result = JSON.parse(response);
        if(result.result == '1'){
          window.location.href = redirection;
        } else {
          $('#'+message).html(result.msg);
        }
      });
}

/*=================form================*/
/*=================number_count===========*/
$(document).ready(function (){
    var countb = 10;
    function myCount() {
    if (countb < 0) {
        countb = 0;
    }
    $('.countb').text(countb);
    countb --;     
    }
    setInterval(myCount,900);
});
/*=================number_count===========*/

/*=================number_count===========*/  
$('.trip_keymessage').delay(10000).fadeOut('slow');
/*=================number_count===========*/



function videoEnd(){
  var player = new Vimeo.Player($('#myvideos'));
  player.play();
  player.on('ended', function(data){
    $('#myvideos').fadeOut(500);
    $('.my_question_answer').fadeIn(2000);
  });
}  
  

/*=================form================*/    
setTimeout(function(){ 
  $('html, body').animate({
    scrollTop: $('#reach_forms').offset().top
  }, 'slow');
  videoEnd();
}, 10000);
/*=================form================*/
  $(function(){
    $('body').on('click', '.nextbtns', function(){
      $('.ndk').html('');
      var formData1 = new FormData($('#testformtrip')[0]);
      $.ajax({
          type : "POST",
          url : ajaxurl+'/users/triptestanswer',
          data : formData1,
          processData:false,
          contentType: false
      }).done(function(response) { 
        var result = JSON.parse(response);
        if(result.result == '1'){  
          $(this).parents('.fixclassin').fadeOut(500);
          $('.my_videose').html(result.data);
          videoEnd();
        } else {
          $('.ndk').html(result.msg);
        }
      });
    });
  });

  $(document).on('click', '#fgpasswords', function(){
    $('#loginfrm').hide();
    $('.forget_pass_cert').show();
    $('.sign_leftbanner2 li').removeClass('active');
  })
  $(document).on('click', '.sign_leftbanner2 li', function(){
    $('.forget_pass_cert').hide();
    $('#loginfrm').show();
  });
