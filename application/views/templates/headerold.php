<?php defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc.">
        <meta name="author" content="Prathak Jain">

        <link rel="shortcut icon" href="<?php echo base_url(); ?>/uploads/appImg/fav32x32.png">
          <title><?php echo get_options("sitetitle");?><?php echo isset($title) ? '-'. $title : '';?></title>
        <!--Morris Chart CSS -->
        <link rel="stylesheet" href="assets/plugins/morris/morris.css">
        <!-- App css -->
        <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/css/icons.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/css/style.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/plugins/fileuploads/css/dropify.min.css" rel="stylesheet" type="text/css" />

        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">  
        <!-- <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"> -->

        <link href="<?php echo base_url(); ?>assets/css/fontawesome5.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/css/custom.css" rel="stylesheet" type="text/css" />

        <script src="<?php echo base_url(); ?>assets/js/modernizr.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/jquery.min.js"></script>


        <script type="text/javascript" src="<?php echo base_url();?>/assets/js/datatables.min.js"></script>  

        <script type="text/javascript">
            var  BASEURL = "<?php echo base_url(); ?>"; 
        </script>
        <script type="text/javascript">
            
            function onReady(callback) {
  var intervalId = window.setInterval(function() {
    if (document.getElementsByTagName('body')[0] !== undefined) {
      window.clearInterval(intervalId);
      callback.call(this);
    }
  }, 200);
}

function setVisible(selector, visible) {
  document.querySelector(selector).style.display = visible ? 'block' : 'none';
}

onReady(function() {
  setVisible('.page', true);
  setVisible('#loading', false);
});

        </script>
    </head>
    <!-- Add css -->
    <style type="text/css">
        #add_loader_form_submit {
          display: none;
          position: fixed;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          width: 100%;
          background: rgba(0,0,0,0.75) url(<?php echo site_url() ?>assets/static_image/giphy.gif) no-repeat center center;
          z-index: 10000;
        }

        #loading {
              display: block;
              position: fixed;
              top: 0;
              left: 0;
              right: 0;
              bottom: 0;  
              z-index: 10000;
              width: 100%;
              background-color: rgba(0,0,0,0.75);
              background-image: url("http://192.168.1.160/positoot/assets/static_image/giphy_old.gif");
              background-repeat: no-repeat;
              background-position: center;
}
    </style>
    <!-- And css -->
    <body class="fixed-left"> 
        <?php $userinfo = getuserdetails(); ?>
        <!-- Begin page -->
        <div id="wrapper">
            <!-- Top Bar Start -->
            <div class="topbar">
                <!-- LOGO -->
                <div class="topbar-left">
                    <a href="<?php echo site_url(). 'admin';?>" class="logo">
                        <span>  <img src="<?php echo base_url().get_options("sitelogo");?>" alt="" width="60%" /> </span>
                        <!-- <span>Posi<span>TooT</span></span> -->
                        <!-- <i class="mdi mdi-layers"></i> -->
                    </a>
                </div>
                <!-- Button mobile view to collapse sidebar menu -->
                <div class="navbar navbar-default" role="navigation">
                    <div class="container-fluid">
                        <!-- Page title -->
    <ul class="nav navbar-nav list-inline navbar-left">
        <li class="list-inline-item">
            <button class="button-menu-mobile open-left">
                <i class="mdi mdi-menu"></i>
            </button>
        </li>
        <li class="list-inline-item">
            <h4 class="page-title"><?php echo isset($title) ? $title : '';?></h4>
        </li>
    </ul>

    <!-- <nav class="navbar-custom">
        <ul class="list-unstyled topbar-right-menu float-right mb-0">
            <li>
            <div class="notification-box">
                <ul class="list-inline mb-0">
                <li>
                    <a href="javascript:void(0);" class="right-bar-toggle">
                        <i class="mdi mdi-bell-outline noti-icon"></i>
                    </a>
                    <div class="noti-dot">
                        <span class="dot"></span>
                        <span class="pulse"></span>
                    </div>
                </li>
                </ul>
            </div>
                End Notification bar
            </li> 
        </ul>
    </nav> -->
                    </div><!-- end container -->
                </div><!-- end navbar -->
            </div>
            <!-- Top Bar End -->


            <!-- ========== Left Sidebar Start ========== -->
            <div class="left side-menu">
                <div class="sidebar-inner slimscrollleft">

                    <!-- User -->
                    <div class="user-box">
                        <div class="user-img">
                            <?php
                            $profile_img = (!empty($userinfo['profile_img']) ? $userinfo['profile_img'] : 'default.png');
                            ?>
                            <img src="<?php echo site_url().'uploads/user/'. $profile_img; ?>" alt="user-img" title="Mat Helme" class="rounded-circle img-thumbnail img-responsive">
                            <!-- <div class="user-status offline"><i class="mdi mdi-adjust"></i></div> -->
                        </div>
                        <h5><a href="#"><?php echo $userinfo['name'] .' '.$userinfo['lastname']; ?></a> </h5>
                        <ul class="list-inline">
                            <li class="list-inline-item">
                                <a href="<?php echo site_url('admin/profile'); ?>" >
                                    <i class="mdi mdi-settings"></i>
                                </a>
                            </li>

                            <li class="list-inline-item">
                                <a href="<?php echo site_url('admin/logout'); ?>" class="text-custom">
                                    <i class="mdi mdi-power"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <!-- End User -->

                    <!--- Sidemenu -->
  <div id="sidebar-menu">
   <ul>
                          <!-- <li class="text-muted menu-title">Navigation</li> -->
     <li>
      <a href="<?php echo site_url(); ?>admin" class="waves-effect"> <i class="fas fa-tachometer-alt"></i>  <span> <?php echo $this->lang->line('dashboard'); ?> </span> </a>
    </li>
    
  <?php if(check_permission(VIEW,"user_list")==1){ ?>
    
    <li class="has_sub">
      <a href="<?php echo site_url(); ?>admin/users" class="waves-effect">
        <i class="fas fa-user-cog"></i> <span><?php echo $this->lang->line('users_management'); ?></span> 
      </a>
    </li> 
      <?php } if(check_permission(VIEW,"instructor_list")==1){ ?>
    <li class="has_sub">
      <a href="<?php echo site_url(); ?>admin/trainers" class="waves-effect">
        <i class="fas fa-book-reader"></i> <span><?php echo $this->lang->line('instructor_management'); ?></span> 
      </a>    
    </li>
      <?php } if(check_permission(VIEW,"owner_list")==1 || check_permission(VIEW,"business_list")==1){ ?>
    <li class="has_sub">
      <a href="javascript:void(0);" class="waves-effect">
        <i class="fas fa-user-lock"></i> <span><?php echo $this->lang->line('studio_management'); ?></span> <span class="menu-arrow"></span>
      </a>
      <ul class="list-unstyled">
          <?php if(check_permission(VIEW,"owner_list")==1){ ?>
        <li><a href="<?php echo site_url(); ?>admin/StudioOwner"> <span><?php echo $this->lang->line('owner_list'); ?></span></a></li>     
         <?php } if(check_permission(VIEW,"business_list")==1){ ?>
        <li><a href="<?php echo site_url(); ?>admin/StudioOwner/business_list"> <span><?php echo $this->lang->line('business_list'); ?></span></a></li>  
          <?php }  ?>   
      </ul>
   
    </li> 
      <?php } if(check_permission(VIEW,"subcription_plans_list")==1){ ?>

    <li>
      <a href="<?php echo site_url(); ?>admin/subscription/plan_list"> <i class="fas fa-file-contract"></i> <span><?php echo $this->lang->line('subcription_plan'); ?></span></a>
    </li>
      <?php } ?> 
      <li>
      <a href="<?php echo site_url(); ?>admin/transaction"> <i class="fas fa-file-contract"></i> <span><?php echo $this->lang->line('transaction_management'); ?></span></a>
    </li>
     <?php if($userinfo['role_id'] == '1'){ ?>

    <li class="has_sub">
      <a href="javascript:void(0);" class="waves-effect">
        <i class="fa fa-folder-open" style="font-size: 22px;"></i> <span> <?php echo $this->lang->line('role_permission'); ?></span> <span class="menu-arrow"></span>
      </a>
      <ul class="list-unstyled">
        <li><a href="<?php echo site_url(); ?>admin/permission/role_list"><?php echo $this->lang->line('role_list'); ?></a></li>
        <li><a href="<?php echo site_url(); ?>admin/permission"><?php echo $this->lang->line('staff_list'); ?></a></li> 
      </ul>
    </li>
       <?php } if(check_permission(VIEW,"page_content")==1  || check_permission(VIEW,"contact_details")==1 || check_permission(VIEW,"website_settings")==1){ ?>
    <li class="has_sub">
      <a href="javascript:void(0);" class="waves-effect">
        <i class="fa fa-mobile" style="font-size: 22px;"></i> <span> <?php echo $this->lang->line('app_content'); ?></span> <span class="menu-arrow"></span>
      </a>
      <ul class="list-unstyled">
        <?php  if($userinfo['role_id'] == '1' || check_permission(VIEW,"page_content")==1){ ?>
        <li><a href="<?php echo site_url(); ?>admin/app"><?php echo $this->lang->line('static_content'); ?></a></li>
       <?php } if($userinfo['role_id'] == '1' || check_permission(VIEW,"contact_details")==1){ ?>
        <li><a href="<?php echo site_url(); ?>admin/app/contact_us"><?php echo $this->lang->line('contact_details'); ?></a></li> 
        <?php } if($userinfo['role_id'] == '1' || check_permission(VIEW,"website_settings")==1){ ?>
        <li><a href="<?php echo site_url(); ?>admin/app/app_settings"><span><?php echo $this->lang->line('web_setting'); ?> </span></a></li> 
        <?php }  ?>
     
      </ul>
    </li>
      <?php } if(check_permission(VIEW,"manage_email_template")==1){ ?>
     <li>
      <a href="<?php echo site_url(); ?>admin/email_template"> <i class="fas fa-envelope-open-text"></i> <span><?php echo $this->lang->line('email_template'); ?></span></a>
    </li>
      <?php } ?>


          </ul>
          <div class="clearfix"></div>
      </div>
      <!-- Sidebar -->
      <div class="clearfix"></div>

  </div>

</div>
<!-- Left Sidebar End --> 
<div class="content-page"> 
  <div class="content">
    <div class="container-fluid">


