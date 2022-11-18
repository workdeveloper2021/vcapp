<?PHP if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
   <?php 
	error_reporting(0);
	$this->load->view('part/head'); 
   ?>

    <body class="fixed-left">

        <!-- Begin page -->
        <div id="wrapper">

         <?php $this->load->view('part/header'); ?>
			

            <!-- ========== Left Sidebar Start ========== -->
            <div class="left side-menu">
                
				<?php $this->load->view('part/sidebar'); ?>
            </div>
            <!-- Left Sidebar End -->



            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="content-page">
                <!-- Start content -->
					<div class="content">
				<?php
					// display success/error message
					echo $this->messages->getMessageFront();
					// load content area
					echo $content
				?>
				</div>
                
        <!-- END wrapper -->
	<?php
		//footer of template
		$this->load->view('part/footer')
	?>

    </body>
</html>
