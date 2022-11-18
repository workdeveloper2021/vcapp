<!-- Main Footer -->
    <footer class="footer-wrap">
            <div class="container footer-block pt-4">
                <div class="row">
                    <div class="col-xl-2 col-lg-2 col-md-12 col-sm-12 col-12 pb-3">
                        <div class="footer-logo">
                            <img class="img-fluid" src="<?php echo site_url(); ?>assets_web/img/logo.png">
                        </div>
                    </div>
                    <div class="col-xl-7 col-lg-7 col-md-12 col-sm-12 col-12 pb-3 pt-3 footer-nav-block">
                        <div class="footer-nav">
                            <ul class="navbar-nav clearfix">
                                <li class="nav-item active">
                                    <a class="nav-link" href="<?php echo site_url('terms_of_use'); ?>">
                                        Terms of use
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo site_url('privacy_policy'); ?>">
                                        Privacy policy
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo site_url('help_support'); ?>">
                                        Help & Support 
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        More info
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo site_url('contact_us'); ?>">
                                        Contact us
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12 pb-3 pt-3 footer-nav-block">
                        <div class="footer-socail-nav">
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
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 pb-2 foot-copyright">
                        <div class="foot-copyright text-center">
                            <a href="#">
                                © 2019 MOMCRED
                            </a>
                        </div>
                    </div>
                </div>
            </div>
    </footer>
    <!-- End of Main Footer -->


    <!-- JS Files -->
   <!-- ==== JQuery 3.3.1 js file==== -->
    <script src="<?php echo site_url(); ?>assets_web/js/jquery-3.3.1.min.js"></script>

    <!-- ==== Bootstrap js file==== -->
    <script src="<?php echo site_url(); ?>assets_web/js/bootstrap.min.js"></script>

    <!-- ==== Custom js file==== -->
    <script src="<?php echo site_url(); ?>assets_web/js/custom.js"></script>

</body>
</html>
