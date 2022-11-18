<?php
 defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Contact Us Area --> 
  <section class="contact_us">
    <div class="container">
      <div class="contact_inner">
        <h2>We are creatives, so it might be about minions and stuff</h2>
        <p class="black">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam</p>
        <a href="<?php echo site_url('web/contact'); ?>" class="theme_btn">Contact us</a>
      </div>
    </div>
  </section> 
<!-- End Contact Us Area -->   
   

<!-- Footer Area -->  
  <footer class="footer_area"> 
    <img src="<?php echo base_url(); ?>front_assets/images/footer-shap.png" alt="" class="shap">
    <div class="round_shap"></div>
    <div class="footer_inner row">   
      <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 footer_logo wow fadeIn">
        <a href="index.html"><img src="<?php echo base_url(); ?>front_assets/images/Logo.png" alt=""></a> 
        <!-- <div class="language">
          <h6>Language :</h6> 
          <div class="language_select"> 
            <select class="post_select">
              <option>English (UK)</option>
              <option>English (US)</option>
              <option>Bangla (BN)</option> 
            </select> 
          </div>
        </div> --> 
        <ul class="footer_menu"> 
          <li><a href="<?php echo site_url('web/contact'); ?>">CONTACT</a></li>
          <li><a href="<?php echo site_url('web/privacy'); ?>">PRIVACY POLICY</a></li>
          <li><a href="<?php echo site_url('web/terms_condition'); ?>">TERMS & CONDITIONS</a></li>
          <li><a href="<?php echo site_url('web/faq'); ?>">FAQ</a></li>
        </ul>
        <ul class="footer_social"> 
          <li><a href="javascript:void(0);"><i class="fab fa-facebook-f"></i></a></li>
          <li><a href="javascript:void(0);"><i class="fab fa-linkedin-in"></i></a></li>
          <li><a href="javascript:void(0);"><i class="fab fa-dribbble"></i></a></li> 
        </ul>
      </div>
      <div class="footer_widget fw_1 col-xl-2 col-lg-3 col-md-3 col-sm-6 wow fadeIn" data-wow-delay="0.2s">
        <h4>Core Link</h4>
        <ul class="footer_nav"> 
          <li><a href="javascript:void(0);">Team Member</a></li>
          <li><a href="javascript:void(0);">Pricing plan</a></li> 
          <li><a href="javascript:void(0);">Google Map</a></li> 
          <li><a href="javascript:void(0);">Apps store</a></li> 
          <li><a href="about.html">About Company</a></li> 
        </ul>
      </div>  
      <div class="footer_widget fw_2 col-xl-2 col-lg-3 col-md-3 col-sm-6 wow fadeIn" data-wow-delay="0.4s">
        <h4>Information</h4>
        <address>
          7190 W Sunset Blvd #83 <br>Los Angeles, CA 90046 <br>United States 
          <a href="javascript:void(0);" class="email">xyz@gmail.com</a>
          <a href="javascript:void(0);" class="phone">213-334-6562</a>
        </address>
      </div>   
      <div class="footer_widget fw_3 col-xl-3 col-lg-3 col-md-4 col-sm-6 wow fadeIn" data-wow-delay="0.6s">
        <h4>Stay In  Loop</h4>
        <p>Subscribe to receive biweekly tips on creative automation and digital advertising!</p>
        <div class="input-group">
          <input type="text" class="form-control" placeholder="What’s Your email">
          <div class="input-group-append">
            <span class="input-group-text"><i class="fa fa-caret-right"></i></span>
          </div>
        </div>
      </div>   
      <div class="footer_widget fw_4 col-xl-2 col-lg-3 col-md-4 col-sm-6 wow fadeIn" data-wow-delay="0.8s">
        <h4>About Company</h4>
        <ul class="footer_nav">  
          <li><a href="javascript:void(0);">How it works</a></li>
          <li><a href="javascript:void(0);">Development </a></li>
          <li><a href="javascript:void(0);">Digital markeing </a></li>
          <li><a href="javascript:void(0);">Services</a></li>
          <li><a href="javascript:void(0);">Security</a></li> 
        </ul>
      </div>   
    </div> 
    <div class="container btn_container">
      <a href="javascript:void(0);" class="theme_btn apple"><i class="fab fa-apple"></i>App Store</a>
      <a href="javascript:void(0);" class="theme_btn"><i class="fab fa-google-play"></i></i>Play Store</a> 
    </div>
    <p class="copy_right">© 2020 All rights reserved</p>
  </footer>
<!-- End Footer Area --> 
    

<!-- Scroll Top Button -->
  <button class="scroll-top">
    <i class="fa fa-arrow-up"></i>
  </button>
    

<!-- Preloader -->  
  <div class="preloader"></div>
    

  <script src="<?php echo base_url(); ?>front_assets/js/jquery-3.3.1.min.js"></script>
  <script src="<?php echo base_url(); ?>front_assets/js/popper.min.js"></script>
  <script src="<?php echo base_url(); ?>front_assets/js/bootstrap.min.js"></script>
  <script src="<?php echo base_url(); ?>front_assets/vendors/animate-css/wow.min.js"></script> 
  <script src="<?php echo base_url(); ?>front_assets/vendors/parallaxmouse/parallax.min.js"></script> 
  <script src="<?php echo base_url(); ?>front_assets/vendors/counterup/jquery.waypoints.min.js"></script> 
  <script src="<?php echo base_url(); ?>front_assets/vendors/counterup/jquery.counterup.min.js"></script>  
  <script src="<?php echo base_url(); ?>front_assets/vendors/parallaxmouse/jquery.jqlouds.min.js"></script>  
  <script src="<?php echo base_url(); ?>front_assets/vendors/magnify-popup/jquery.magnific-popup.min.js"></script> 
  <script src="<?php echo base_url(); ?>front_assets/vendors/isotope/imagesloaded.pkgd.min.js"></script>
  <script src="<?php echo base_url(); ?>front_assets/vendors/isotope/isotope.pkgd.min.js"></script>     
  <script src="<?php echo base_url(); ?>front_assets/vendors/bootstrap-selector/jquery.nice-select.min.js"></script>
  <script src="<?php echo base_url(); ?>front_assets/js/theme.js"></script> 
</body>

</html>