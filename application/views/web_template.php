<?PHP if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
       

        <!-- Begin page -->
        <?php
           $ci = & get_instance();  
           $slug = !empty($ci->uri->segment(1)) ? $ci->uri->segment(1) : "";
          // $sluglogin = !empty($ci->uri->segment(0)) ? $ci->uri->segment(0) : "";
    
        ?>
         <?php if(!empty($slug) && ($slug == 'loginweb') || ($slug == 'select_account_type') || ($slug == 'forgot_password') || ($slug == 'enterpassword')) { 
                $this->load->view('web_part/head');
            }else{
                $this->load->view('web_part/header');

            } ?>
			
       
            <!-- ========== Left Sidebar Start ========== -->
           
            <!-- Left Sidebar End -->



            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
           
                <!-- Start content -->
					<!-- <div class="content"> -->
				<?php
					// display success/error message
					echo $this->messages->getMessageFront();
					// load content area
					echo $content
				?>
				<!-- </div>
                 -->
        <!-- END wrapper -->
	<?php
		//footer of template
		$this->load->view('web_part/footer')
	?>


