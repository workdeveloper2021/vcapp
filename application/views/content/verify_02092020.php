<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Signal Health Group Inc</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <!-- ===== Import Stylesheets ==== -->
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>website/assets/css/font-awesome.min.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>website/assets/css/animate.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>website/assets/css/main.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>website/assets/css/style.css">
  <!-- favicon icon -->
   <link rel="shortcut icon" href="<?php echo base_url();?>website/assets/img/favicon.png"> 
<body>
<style type="text/css">
  .title{
    font-family: arial-rounded; 
font-size: 1.1875rem;
line-height: 1.6875rem;
color: #000;
font-weight: 100;
text-transform: uppercase!important;
  }
  .rightcircle{
color: #0e8542;
font-size: 100px;
font-weight: 500;
margin-bottom: 20px;

  }
  .crosscircle{
   color: #ff0000; font-size: 100px; font-weight: 500; margin-bottom: 20px;">
  }
</style>


<!-- About Content Section -->
 <section class="about-content-height">
    <div class="background-before">
      <div class="py-5">
        <div class="container">
          <div class="text-center">
            <?php if($email_status=='1'){?>
         <i class="fas fa-check-circle rightcircle"></i> <h2 class="dark-color title font-w-900 pb-4"><?php echo isset($email_verified) ?  $email_verified : 'Not Verify Please Try again Later'; ?></h2> 
       <?php }else{
        ?>
          <i class="far fa-times-circle crosscircle"></i> <h2 class="dark-color title font-w-900 pb-4"><?php echo isset($email_verified) ?  $email_verified : 'Not Verify Please Try again Later'; ?></h2>
        <?php  }
         ?>
          
          </div>
        </div>
      </div>
    </div>
 </section>
<!-- End of About Content Section -->

  
</body>
</html>
<!-- ======================================== -->

<!-- Scrits Required -->
<script src="<?php echo base_url();?>website/assets/js/jquery.min.js"></script>
<script src="<?php echo base_url();?>website/assets/js/bootstrap.min.js"></script>
<script src="<?php echo base_url();?>website/assets/js/plugin.js"></script>
<script src="<?php echo base_url();?>website/assets/js/script.js"></script>
  
