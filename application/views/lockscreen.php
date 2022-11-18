<?php
 defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc.">
        <meta name="author" content="Coderthemes">
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <title><?php echo SITE_NAME; ?> - LockScreen</title>
        <!-- App css -->
        <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/css/icons.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/css/style.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/css/custom.css" rel="stylesheet" type="text/css" />
       <script src="<?php echo base_url(); ?>assets/js/modernizr.min.js"></script>

    </head>

    <body>

        <div class="account-pages"></div>
        <div class="clearfix"></div>
        <div class="wrapper-page">
            <div class="text-center">
                <a href="index.html" class="logo"><span>Posi<span>TooT</span></span></a>
                <!-- <h5 class="text-muted m-t-0 font-600">Responsive Admin Dashboard</h5> -->
            </div>
          <div class="m-t-40 card-box">
                <div class="text-center">
                    <h4 class="text-uppercase font-bold m-b-0">Welcome Back</h4>
                </div>
                <div class="p-20">

  <?php if ($this->session->flashdata('lockerror') != '') { 
    echo '<h6 class="'.$this->session->flashdata('lockclass').'">'.$this->session->flashdata('lockerror').'</h6>';
  } ?>


        <?php $attributes = array('class' => 'text-center', 'id' => '');
        $hidden = array('is_submit' => 1);
        echo form_open_multipart('admin/lockopen', $attributes, $hidden); ?> 

            <div class="user-thumb">
              <img src="<?php echo base_url(). '/uploads/user/'.$userinfo['user_pic']; ?>" class="img-fluid rounded-circle img-thumbnail" alt="thumbnail">
            </div>
            <div class="form-group">
              <p class="text-muted m-t-10">
                Enter your password to access the admin.
              </p>
              <div class="input-group m-t-30">
                <input type="password" name="userpass" class="form-control" placeholder="Password" required="">
                <span class="input-group-append">
                  <button type="submit" class="btn btn-pink w-sm waves-effect waves-light">
                    Log In
                  </button>
                </span>
              </div>
            </div>

        <?php echo form_close(); ?>


                </div>
            </div>
            <!-- end card-box -->

      <div class="row">
        <div class="col-sm-12 text-center">
          <p class="text-muted"><?php echo $userinfo['firstname'] . ' ' . $userinfo['lastname']; ?> ?<a href="<?php echo site_url('admin/logout'); ?>" class="text-primary m-l-5"><b>Logout</b></a></p>
        </div>
      </div>

        </div>
        <!-- end wrapper -->




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