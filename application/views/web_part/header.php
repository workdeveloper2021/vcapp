<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Document Title -->
    <title></title>

    <!-- Favicon -->
    <!-- <link rel="shortcut icon" type="image/png" href="<?php echo site_url(); ?>assets_web/img/favicon.png"> -->

    <!-- CSS Files -->
    <!--==== Google Fonts ====-->
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,600,700,800,900&display=swap" rel="stylesheet">

    <!--==== Font-Awesome css file ====-->
    <link rel="stylesheet" href="<?php echo site_url(); ?>assets_web/css/font-awesome.min.css">

    <!--==== Bootstrap css file ====-->
    <link rel="stylesheet" href="<?php echo site_url(); ?>assets_web/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo site_url(); ?>assets_web/multiselect/css/multi-select.css">
    <link rel="stylesheet" href="<?php echo site_url(); ?>assets_web/select2/css/select2.min.css">
    <!--==== Custom css file ====-->
    <link rel="stylesheet" href="<?php echo site_url(); ?>assets_web/css/custom-theme.css">

    <!--==== Custom css file ====-->
    <link rel="stylesheet" href="<?php echo site_url(); ?>assets_web/css/custom.css">
    <link href="<?php echo base_url('template/css/parsley.css'); ?>" rel="stylesheet" type="text/css" />
    <!--==== Responsive css file ====-->
    <link rel="stylesheet" href="<?php echo site_url(); ?>assets_web/css/responsive.css">
    <script type="text/javascript" src="<?php echo base_url('template/js/parsley.js'); ?>"></script>
  
    <script src="<?php echo base_url('template/assets/js/jquery.min.js') ?>"></script>
   <script type="text/javascript" src="<?php echo base_url('template/js/parsley.js'); ?>"></script>
<!--   <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script> -->
   <script type="text/javascript" src="<?php echo base_url('assets_web/select2/js/select2.min.js'); ?>" defer></script> 
 <script type="text/javascript">
    $(document).ready(function() {
     // select4,select5 is in use in myaccount page 
    $(".select2").select2({
        placeholder: 'Select',
        maximumSelectionSize: 6,
        width: '100%'
    });
    
    $(".select3").select2({
        placeholder: 'Select',
        maximumSelectionSize: 6,
        width: '100%'
    });
    $(".select6").select2({
        placeholder: 'Select',
        maximumSelectionSize: 6,
        width: '100%'
    });
    $(".select7").select2({
        placeholder: 'Select',
        maximumSelectionSize: 6,
        width: '100%'
    });       

    });

  </script>
   <script type="text/javascript" src="<?php echo base_url('assets_web/js/jquery.serializeToJSON.js'); ?>"></script>

</head>

<body>

    <!-- Main Header -->
    <header class="header">
        <!-- Start Header Navbar-->
        <div class="main-menu-wrap">
            <div class="container">
                <div class="main-header">
                    <div class="header-nav">
                        <nav class="navbar navbar-expand-lg navbar-light">
                            <a class="navbar-brand" href="<?php echo site_url('home'); ?>">
                                <img src="<?php echo site_url(); ?>assets_web/img/logo.png" alt="MomCred">
                            </a>
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo03" aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            
                            <div class="collapse navbar-collapse header-nav-box" id="navbarTogglerDemo03">
                                <div class="row">
                                    <div class="space-box col-12 col-sm-2 col-md-4 col-lg-4 col-xl-5"></div>
                                    <div class="col-12 col-sm-10 col-md-8 col-lg-8 col-xl-7">
                                        <div class="header-top-nav overflow-v">
                                        <?php
                                        
                                            $getdata = GetSocialLinkUrl();
                                            $social_link = array();
                                            if(!empty($getdata)){
                                              foreach ($getdata as $getdata_val) {
                                                $social_link[$getdata_val['setting_name']] = $getdata_val;
                                              }
                                            }
                                            /*  Instagram */
                                            if(!empty($social_link['instagram'])){
                                              $instagram = $social_link['instagram']['setting_value'];
                                            }else{
                                               $instagram = "#";
                                            }

                                            /*  facebook */
                                            if(!empty($social_link['facebook'])){
                                              $facebook = $social_link['facebook']['setting_value'];
                                            }else{
                                               $facebook = "#";
                                            }

                                            /*  youtube */
                                            if(!empty($social_link['youtube'])){
                                              $youtube = $social_link['youtube']['setting_value'];
                                            }else{
                                               $youtube = "#";
                                            }

                                            /*  linkedin */
                                            if(!empty($social_link['linkedin'])){
                                              $linkedin = $social_link['linkedin']['setting_value'];
                                            }else{
                                               $linkedin = "#";
                                            }

                                            /*  twitter */
                                            if(!empty($social_link['twitter'])){
                                              $twitter = $social_link['twitter']['setting_value'];
                                            }else{
                                               $twitter = "#";
                                            }
                                                    
                                            ?>

                                            <ul class="navbar-nav clearfix">
                                                <li class="nav-item active instagram">
                                                  <a class="nav-link" href="<?php echo $instagram; ?>" target="_blank">
                                                      <img src="<?php echo site_url(); ?>assets_admin/img/header/insta.png">
                                                  </a>
                                                </li>
                                                <li class="nav-item facebook">
                                                  <a class="nav-link" href="<?php echo $facebook; ?>" target="_blank">
                                                      <img src="<?php echo site_url(); ?>assets_admin/img/header/facebook.png">
                                                  </a>
                                                </li>
                                                <li class="nav-item youtube">
                                                  <a class="nav-link" href="<?php echo $youtube; ?>" target="_blank">
                                                      <img src="<?php echo site_url(); ?>assets_admin/img/header/youtube.png">
                                                  </a>
                                                </li>
                                                <li class="nav-item linkedin">
                                                  <a class="nav-link" href="<?php echo $linkedin; ?>" target="_blank">
                                                      <img src="<?php echo site_url(); ?>assets_admin/img/header/linkdin.png">
                                                  </a>
                                                </li>
                                                <li class="nav-item twitter">
                                                  <a class="nav-link" href="<?php echo $twitter; ?>" target="_blank">
                                                      <img src="<?php echo site_url(); ?>assets_admin/img/header/twitter.png">
                                                  </a>
                                                </li>
                                                <!-- <li class="nav-item header-login">
                                                    <a class="nav-link" href="<?php //echo site_url('loginweb'); ?>">
                                                        Login
                                                    </a>
                                                </li> -->
                                                <!-- Add custom code by developer -->
                                                <li class="nav-item header-login">
                                                   <?php if($this->session->userdata('user_id')){ ?>
                                                       <a class="nav-link" href="<?php echo site_url('web_admin'); ?>">
                                                       Dashboard
                                                       <?php } else { ?>
                                                       <a class="nav-link" href="<?php echo site_url('loginweb'); ?>">
                                                       Login
                                                       <?php }  ?>   
                                                       </a>
                                                </li>
                                                <!-- End code -->

                                            </ul>
                                        </div>
                                    </div>                 
                                    <div class="col-12">
                                        <div class="header-bottom-nav">
                                            <ul class="navbar-nav">
                                                <li class="nav-item <?php if($this->uri->segment(1) == 'home') { echo "active"; } ?>">
                                                    <a class="nav-link" href="<?php echo site_url('home'); ?>">Home </a>
                                                </li>
                                                <li class="nav-item <?php if($this->uri->segment(1) == 'navigation_center') { echo "active"; } ?>">
                                                    <a class="nav-link" href="<?php echo site_url('navigation_center'); ?>">Navigation Center </a>
                                                </li>
                                                <!-- <li class="nav-item ">
                                                    <a class="nav-link" href="#">Build a Package </a>
                                                </li> -->
                                                <li class="nav-item <?php if($this->uri->segment(1) == 'additional_information') { echo "active"; } ?>">
                                                    <a class="nav-link" href="<?php echo site_url('additional_information'); ?>">Additional Information </a>
                                                </li>
                                                <li class="nav-item <?php if($this->uri->segment(1) == 'additional_service') { echo "active"; } ?>">
                                                    <a class="nav-link" href="<?php echo site_url('additional_service'); ?>">Additional Services </a>
                                                </li>
                                                <li class="nav-item <?php if($this->uri->segment(1) == 'featured') { echo "active"; } ?>">
                                                    <a class="nav-link" href="<?php echo site_url('featured'); ?>">Featured </a>
                                                </li>
                                                <li class="nav-item <?php if($this->uri->segment(1) == 'contact_us') { echo "active"; } ?>">
                                                    <a class="nav-link" href="<?php echo site_url('contact_us'); ?>">Contact Us </a>
                                                </li>
                                                <li class="nav-item <?php if($this->uri->segment(1) == 'blog' || $this->uri->segment(1) == 'blog-details') { echo "active"; } ?>">
                                                    <a class="nav-link" href="<?php echo site_url('blog'); ?>">Blog </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>

                            </div>

                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Header Navbar-->
    </header>
    <!-- End of Main Header -->
