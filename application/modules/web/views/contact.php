<?php
 defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!-- Pages Banner Area -->   
  <section class="pages_banner" id="parallax">   
    <img src="<?php echo base_url(); ?>front_assets/images/bannar-shap-1.png" alt="" class="layer layer_1" data-depth="0.10">
    <img src="<?php echo base_url(); ?>front_assets/images/bannar-shap-2.png" alt="" class="layer layer_2" data-depth="0.35">
    <div class="container">
      <img src="<?php echo base_url(); ?>front_assets/images/pages-banner-4.png" alt="" class="bannar_img wow fadeInRight">
      <h2 class="wow fadeInUp">Contact Us <br></h2> 
      <p class="wow fadeInUp" data-wow-delay="0.3s">For any questions, comments, or concerns, please feel free to reach us:</p> 
     <img src="<?php echo base_url(); ?>front_assets/images/p-banner-shap.png" alt="" class="layer_3">
    </div> 
  </section>
<!-- End of Pages Banner Area -->  
    

<!-- Get In Touch With Us Area --> 
  <section class="get_touch_area">
    <div class="container">
      <h2>Get In Touch With Us</h2>
      <div class="row">
        <div class="col-lg-6 map_area">
          <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d15962.749973379458!2d90.35358130400583!3d23.861833852439656!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sbd!4v1542189963132"></iframe>
        </div>
        <div class="col-lg-6"> 
          <form class="from_main" action="http://themazine.com/html/kabbo/php/contact.php" method="post" id="phpcontactform" novalidate="novalidate"> 
            <div class="form-group">
              <input type="text" class="form-control" id="name" name="name" placeholder="Full Name">
            </div>  
            <div class="row"> 
              <div class="form-group col-lg-6">
                <input type="email" class="form-control col-md-6" id="email" name="email" placeholder="Email"> 
              </div>
              <div class="form-group col-lg-6">   
                <input type="text" class="form-control" id="subject" name="subject" placeholder="Phone"> 
              </div>
            </div>
            <div class="form-group">
              <textarea class="form-control" id="message" name="message" placeholder="Your Message"></textarea>
            </div>
            <div class="form-group m-0">
              <button class="theme_btn btn" id="js-contact-btn" type="submit">Send</button> 
              <div id="js-contact-result" data-success-msg="Form submitted successfully." data-error-msg="Messages Successfully"></div>
            </div>
          </form>
        </div>
      </div> 
    </div>
  </section>
<!-- End of Get In Touch With Us Area --> 
 

<!--General communication Area -->
  <section class="general_communication">
    <div class="container">
      <div class="row">
        <div class="col-lg-4">
          <ul class="quick_find">
            <li>Quick Find Us </li>
            <li>Email: <a href="javascript:void(0);">info@xyz.com</a></li>
            <li>213-334-6562 <span>Monday–Friday 9am-6pm</span></li>
            <li>Address <span>Positoot, 7190 W Sunset Blvd #83 Los Angeles, <br>CA 90046  United States </span></li>
          </ul>
        </div>
        <div class="col-lg-8 help_support row">
          <div class="col-md-6">
            <div class="support"> 
              <h4 class="blue-color">Help and Support</h4>
              <p>We’re here to help with any questions or code.</p>
              <a href="javascript:void(0);"></a>
            </div>
          </div>
          <div class="col-md-6">
            <div class="support"> 
              <h4 class="blue-color">General communication</h4>
              <p>For general queries,please email info@xyz.com</p>
              <a href="javascript:void(0);"></a>
            </div>
          </div>
          <ul class="socail_icons col-12"> 
            <li><a href="javascript:void(0);"></a></li>
            <li><a href="javascript:void(0);"></a></li>
            <li><a href="javascript:void(0);"></a></li>
            <li><a href="javascript:void(0);"></a></li>
            <li><a href="javascript:void(0);"></a></li> 
          </ul>
        </div>
      </div>
    </div>
  </section>
<!--General of communication Area -->
     