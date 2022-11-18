<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc.">
  <meta name="author" content="Coderthemes">
  <link rel="shortcut icon" href="<?php echo base_url(); ?>/uploads/appImg/fav32x32.png">
  <title><?php echo get_options("sitetitle");?></title>
  <!-- App css -->
  <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo base_url(); ?>assets/css/icons.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo base_url(); ?>assets/css/style.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo base_url(); ?>assets/css/custom.css" rel="stylesheet" type="text/css" />
  <script src="<?php echo base_url(); ?>assets/js/modernizr.min.js"></script>
</head>
<body class="account-pages">
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div>  -->
  <div class="wrapper-page">
    <div class="text-center">
      <a href="<?php echo base_url(); ?>/admin" class="logo">
         <img src="<?php echo base_url().get_options("sitelogo");?>" alt="" /> 
          <!-- <span>Posi<span>TooT</span></span> -->
      </a>
      <!-- <h5 class="text-muted m-t-0 font-600">Responsive Admin Dashboard</h5> -->
    </div>
    <div class="m-t-40 card-box">
      <div class="text-center">
        <h4 class="text-uppercase font-bold m-b-0 f-32 blue" style="line-height: 33px;">FORGOT YOUR PASSWORD</h4>
        <p class="text-muted m-b-0 font-13 m-t-20">Enter your email address and we'll send you an email with new password.</p>
      </div>
      <div class="p-20" style="padding-top: 10px !important; padding-bottom: 10px !important;">
        <?php if ($this->session->flashdata('forgotpassword') != '') { 
          echo '<h6 class="danger">'.$this->session->flashdata('forgotpassword').'</h6>';
        } ?>
        <?php $attributes = array('class' => 'form-horizontal m-t-20', 'id' => 'forgotpass');$hidden = array('is_submit' => 1);echo form_open_multipart('admin/forgotpassword_submit', $attributes, $hidden); ?> 

        <div class="form-group">
          <div class="col-xs-12">
            <input class="form-control" name="forgot_email" type="email" required="" placeholder="Email">
          </div>
        </div>

        <div class="form-group text-center m-t-20">
          <div class="col-xs-12">
            <button class="btn btn-custom btn-bordred btn-block waves-effect waves-light" id="sendemail" type="submit">Send Email</button>
          </div>
        </div>
        <?php echo form_close(); ?>
        <div class="row">
          <div class="col-sm-12 text-left">
            <p class="text-muted mb-0"><a href="<?php echo base_url().'admin'?>" class="text-primary m-l-5 text-muted"><span><i class="fa fa-arrow-left mr-2"></i></span> <b>Back to Log In?</b> </a> </p>
          </div>
        </div>
      </div>
    </div>
    <!-- end card-box -->

    
  </div>
  <!-- end wrapper page -->


        <!-- jQuery  -->
        <script src="<?php echo base_url(); ?>assets/js/jquery.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/popper.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/detect.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/fastclick.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/jquery.blockUI.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/waves.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/jquery.nicescroll.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/jquery.slimscroll.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/jquery.scrollTo.min.js"></script>
        <!-- App js -->
        <script src="<?php echo base_url(); ?>assets/js/jquery.core.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/jquery.app.js"></script>
        <script> 
            setTimeout(function() {
                $('.danger').hide('fast');
            }, 5000);

            setTimeout(function() {
                $('.success').hide('fast');
            }, 5000);

            // $(document).ready(function(){              
            //   $('#sendemail').click(function(){
            //     $(this).attr('disabled', 'true');
            //   });
            // });
            document.getElementById('forgotpass').onsubmit = function() {
              document.getElementById("sendemail").disabled = true;
            }


        </script>
        

    </body>
</html>