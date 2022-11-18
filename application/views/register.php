<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc.">
        <meta name="author" content="Coderthemes">
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <title><?php echo SITE_NAME; ?> - Register</title>
        <!-- App css -->
        <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/css/icons.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/css/style.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/css/custom.css" rel="stylesheet" type="text/css" />
        <!-- <script src="<?php echo base_url(); ?>assets/js/modernizr.min.js"></script> -->

        <link href="<?php echo base_url(); ?>assets/plugins/fileuploads/css/dropify.min.css" rel="stylesheet" type="text/css" />



        <style>
            .wrapper-page1{
                margin: 5% auto;
                position: relative;
                width: 900px;
            }
        </style>
    </head>
    <body>
        <div class="account-pages"></div>
        <div class="clearfix"></div>
        <div class="wrapper-page1">
            <div class="text-center">
                <a href="<?php echo base_url(); ?>" class="logo">
                    <img src="<?php echo base_url(); ?>assets/static_image/email_logo.png" alt="" width="60%" />
                    <!-- <span>Posi<span>TooT</span></span> -->
                </a> 
            </div>
        	<div class="m-t-40 card-box">
                <div class="text-center">
                    <h4 class="text-uppercase font-bold m-b-0">Register</h4>
                </div>
                <div class="p-20">

    <?php $attributes = array('class' => 'form-horizontal m-t-20', 'id' => '');
         $hidden = array('is_submit' => 1);
        echo form_open_multipart('restaurant/signup_submit', $attributes, $hidden); ?> 


    


    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="inputEmail4" class="col-form-label">Email</label>
            <input type="email" name="email" class="form-control" id="inputEmail4" placeholder="Email" required="" />
        </div>
        <div class="form-group col-md-6">
            <label for="inputmobile" class="col-form-label">Mobile Number</label>
            <input type="text" id="inputmobile" name="phoneno" class="form-control" placeholder="Mobile Number" required="">            
        </div>
    </div>
 
    <div class="form-row">
        <div class="form-group col-md-6"> 
            <label for="inputpassword" class="col-form-label">Password</label>
            <input class="form-control" id="inputpassword" name="password" type="password" required="" placeholder="Password"> 
        </div>

        <div class="form-group col-md-6"> 
            <label for="inputconfirmpass" class="col-form-label">Confirm Password</label>
            <input class="form-control" type="password" name="confirm_password" id="inputconfirmpass" required="" placeholder="Confirm Password"> 
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6"> 
            <label for="inputbusinessname" class="col-form-label">Business Name</label>
            <input class="form-control" name="businessname" id="inputbusinessname" type="text" required="" placeholder="Business Name">
        </div>

        <div class="form-group col-md-6"> 
            <label for="inputbusinessaddress" class="col-form-label">Business Address</label>
            <input class="form-control" name="businessaddress" id="inputbusinessaddress" type="text" required="" placeholder="Business Name">
            <input type="hidden" name="businesslat" value="" />
            <input type="hidden" name="businesslng" value="" />
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6"> 
            <label for="inputwebsite" class="col-form-label">Website</label>
            <input class="form-control" name="businesswebsite" id="inputwebsite" type="text" required="" placeholder="Business Website">
        </div>

        <div class="form-group col-md-6"> 
           <label for="inputbusinesscategory" class="col-form-label">Category</label>
            <select class="form-control" name="businesscategory">
                <?php if(!empty($category_datail)){ 
                    foreach($category_datail as $cat_info){ ?>
                    <option value="<?php echo encode($cat_info['id']); ?>"><?php echo $cat_info['cat_name']; ?></option>                    
                <?php } } ?>
            </select>
        </div>
    </div>

	<div class="form-group">
        <div class="col-xs-12"> 
            <label for="inputbusinessimage" class="col-form-label">Business Image</label>
            <input class="dropify" name="businessimage" id="inputbusinessimage" type="file" data-height="200" /> 
        </div>
    </div>
     
       <!--  <div class="form-group">
           <div class="col-xs-12">
               <label for="inputcat" class="col-form-label">Category</label>
           </div>
           <div class="col-xs-12">
               <div class="checkbox checkbox-success form-check-inline">
                   <input type="checkbox" id="inlineCheckbox2" value="option1" checked="">
                   <label for="inlineCheckbox2"> Inline TwoInline TwoInline TwoInline Two </label>
               </div>
               <div class="checkbox checkbox-success form-check-inline">
                   <input type="checkbox" id="inlineCheckbox2" value="option1" checked="">
                   <label for="inlineCheckbox2"> Inline Two </label>
               </div>                  
           </div>         
       </div> -->




    <div class="form-group">
		<div class="col-xs-12">
			<div class="checkbox checkbox-custom">
				<input id="checkbox-signup" type="checkbox" checked="checked" required="">
				<label for="checkbox-signup">I accept <a href="#">Terms and Conditions</a></label>
			</div>
		</div>
	</div>

	<div class="form-group text-center m-t-40">
		<div class="col-xs-12">
			<button class="btn btn-custom btn-bordred btn-block waves-effect waves-light" type="submit">
				Register
			</button>
		</div>
	</div>

	<?php echo form_close(); ?>

                </div>
            </div>
            <!-- end card-box -->

			<div class="row">
				<div class="col-sm-12 text-center">
					<p class="text-muted">Already have account?<a href="<?=base_url()."admin"?>" class="text-primary m-l-5"><b>Sign In</b></a></p>
				</div>
			</div>

        </div>
        <!-- end wrapper page -->


        <!-- jQuery  -->
        <script src="<?php echo base_url(); ?>assets/js/jquery.min.js"></script> 
        <script src="<?php echo base_url(); ?>assets/js/custom.js"></script>
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
        <!-- file uploads js -->
        <script src="<?php echo base_url(); ?>assets/plugins/fileuploads/js/dropify.min.js"></script>

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