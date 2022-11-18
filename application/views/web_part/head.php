<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Document Title -->
    <title>Admin</title>

    <!-- Favicon -->
    <!-- <link rel="shortcut icon" type="image/png" href="<?php echo site_url(); ?>assets_web/img/favicon.png"> -->

    <!-- CSS Files -->
    <!--==== Google Fonts ====-->
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,600,700,800,900&display=swap" rel="stylesheet">

    <!--==== Font-Awesome css file ====-->
    <link rel="stylesheet" href="<?php echo site_url(); ?>assets_web/css/font-awesome.min.css">

    <!--==== Bootstrap css file ====-->
    <link rel="stylesheet" href="<?php echo site_url(); ?>assets_web/css/bootstrap.min.css">

    <!--==== Custom css file ====-->
    <link rel="stylesheet" href="<?php echo site_url(); ?>assets_web/css/custom-theme.css">

    <!--==== Custom css file ====-->
    <link rel="stylesheet" href="<?php echo site_url(); ?>assets_web/css/custom.css">

    <!--==== Login css file ====-->
    <link rel="stylesheet" href="<?php echo site_url(); ?>assets_web/css/loginsignup.css">

    <!--==== Responsive css file ====-->
    <link rel="stylesheet" href="<?php echo site_url(); ?>assets_web/css/responsive.css">
</head>

<body>
    

    <!-- Main Header -->
    <header class="header">
        <!-- Start Header Navbar-->
            <div class="main-menu-wrap">
                <div class="container">
                    <div class="login-header">
                        <div class="header-nav">
                            <nav class="navbar navbar-expand-lg navbar-light">
                                <a class="navbar-brand" href="<?php echo site_url('home'); ?>">
                                    <img src="<?php echo site_url(); ?>assets_web/img/logo.png" alt="MomCred">
                                </a>
                                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#loginnavbar" aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                                
                                <div class="collapse navbar-collapse login-header-box" id="loginnavbar">
                                    <div class="header-top-nav">
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
                                            <?php $slug = !empty($this->uri->segment(3)) ? $this->uri->segment(3) : ""; if(!empty($slug) && $slug == 'select_account_type') {  ?> 
                                                 <li class="nav-item header-login">
                                                <a class="nav-link" href="<?php echo site_url(); ?>loginweb">
                                                    Login1111
                                                </a>
                                                  </li>

                                                 <?php }else{ ?>
                                            <li class="nav-item header-login">
                                                <a class="nav-link" href="<?php echo site_url(); ?>select_account_type">
                                                    Signup
                                                </a>
                                            </li>

                                        <?php } ?>
                                        </ul>
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