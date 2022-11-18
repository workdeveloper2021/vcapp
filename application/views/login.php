<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="A fully featured admin ">
  <meta name="author" content="Ritesh">
  <!-- <link rel="shortcut icon" href="<?php echo base_url(); ?>/uploads/appImg/fav32x32.png"> -->
  <title><?php echo get_options("sitetitle");?><?php echo isset($title) ? '-'. $title : '';?></title>
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
          <span>  <img src="<?php echo base_url().get_options("sitelogo");?>" alt="" /> </span>
          <!-- <span>Posi<span>TooT</span></span> -->
        </a>
        <!-- <h5 class="text-muted m-t-0 font-600">Responsive Admin Dashboard</h5> -->
      </div>
      <div class="m-t-40 card-box">
        <div class="text-center">
          <h4 class="text-uppercase font-bold m-b-0 blue f-32"> Log In </h4>
        </div>
        <div class="p-20" style="padding-top: 10px !important;">
          <?php if ($this->session->flashdata('login') != '') { 
            echo '<h6 class="'.$this->session->flashdata('loginclass').'">'.$this->session->flashdata('login').'</h6>';
          } ?>
          <?php $attributes = array('class' => 'form-horizontal m-t-20', 'id' => '');$hidden = array('is_submit' => 1);echo form_open_multipart('admin/login', $attributes, $hidden); ?>

          <div class="form-group">
            <div class="col-xs-12">
              <input name="useremail" class="form-control " type="email" required="" placeholder="Email" value="<?php if(isset($_COOKIE['email'])){ echo $_COOKIE['email'];} ?>">
            </div>
          </div>

          <div class="form-group">
            <div class="col-xs-12">
              <input name="userpass" class="form-control" type="password" required="" placeholder="Password" value="<?php if(isset($_COOKIE['password'])){ echo $_COOKIE['password'];} ?>">
            </div>
          </div>

          <div class="form-group ">
            <div class="col-xs-12">
              <div class="checkbox checkbox-custom">
                <input id="checkbox-signup" type="checkbox" name="keep_me" value="keep_me"<?php if(isset($_COOKIE['password'])){ ?> checked  <?php } ?>>
                <label for="checkbox-signup"> Remember me  </label>
              </div>
            </div>
          </div>

          <div class="form-group text-center m-t-20">
            <div class="col-xs-12">
              <button class="btn btn-custom btn-bordred btn-block waves-effect waves-light" type="submit">Log In</button>
            </div>
          </div>

          <div class="form-group m-t-20 mb-0">
            <div class="col-sm-12">
              <a href="<?=base_url()."admin/forgotpassword"?>" class="text-muted new-focus"> <i class="fa fa-lock m-r-5"></i> Forgot your password? </a>
            </div>
          </div>
          <?php echo form_close(); ?>
        </div>
      </div><!-- end card-box-->

      <!-- <div class="row">
        <div class="col-sm-12 text-center">
          <p class="text-muted">Don't have an account? 
          <a href="<?=base_url()."restaurant/signup"?>" class="text-primary m-l-5"><b>Sign Up</b></a></p>
        </div>
      </div> -->
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

  </script>


</body>
</html>