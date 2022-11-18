 <style type="text/css">
.page { display: none; padding: 0 0.5em; }

</style>
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div>  -->
        <!-- Add loader div -->
      <!--   <div id="loading"></div> -->
        <!-- End loader  -->
<div class="row page">
    <div class="col-sm-12">
        <?php if ($this->session->flashdata('appcontacterror') != '') { 
          echo '<h6 class="'.$this->session->flashdata('appcontactclass').'">'.$this->session->flashdata('appcontacterror').'</h6>';
        } ?>
        <div class="card-box">
            
        <?php 
          $attributes = array('class' => '', 'id' => '');
          $hidden = array('is_submit' => 1, 'form_type' => "about_us");
          echo form_open_multipart('admin/app/page_content_submit', $attributes, $hidden );
        ?>
        	<div class="form-group">
	            <h4 class="header-title m-b-30"><?php echo $this->lang->line('about_app'); ?></h4>
	            <textarea name="about_app" id="aboutus" name="area" ><?php echo (!empty($about_us)) ? $about_us[0]['discription'] : '' ;?></textarea>
        	</div>
            <div class="form-group">
            	<?php $has_permission= check_permission(EDIT,"page_content");?>
                 <input type="submit" class="btn btn-info" value="<?php echo $this->lang->line('update');?>" <?php echo ($has_permission==1) ? "" : "disabled";?> >
        	</div>
        </div>
    <?php echo form_close(); ?>


    <?php 
      $attributes = array('class' => '', 'id' => '');
       $hidden = array('is_submit' => 1, 'form_type' => "app_privacy_policy");
      echo form_open_multipart('admin/app/page_content_submit', $attributes, $hidden );
    ?>
        <div class="card-box">
        	<div class="form-group">
	          	<h4 class="header-title m-b-30"><?php echo $this->lang->line('app_privacy_policy'); ?></h4>
	            <textarea name="app_privacy_policy" id="contactus" name="area"> <?php echo (!empty($privacy_policy)) ? $privacy_policy[0]['discription'] : '' ;?></textarea>
        	</div>
        	<div class="form-group">
        		<?php $has_permission= check_permission(EDIT,"page_content");?>
                 <input type="submit" class="btn btn-info" value="<?php echo $this->lang->line('update');?>" <?php echo ($has_permission==1) ? "" : "disabled";?> >
        	</div>
        </div>
    <?php echo form_close(); ?>


    <?php 
      $attributes = array('class' => '', 'id' => '');
      $hidden = array('is_submit' => 1, 'form_type' => "app_term_us");
      echo form_open_multipart('admin/app/page_content_submit', $attributes, $hidden );
    ?>
        <div class="card-box">
        	<div class="form-group">
	        	<h4 class="header-title m-b-30"><?php echo $this->lang->line('app_term_us'); ?></h4>
	            <textarea name="app_term_us" id="termscondition" name="area" ><?php echo (!empty($terms_condition)) ? $terms_condition[0]['discription'] : '' ;?></textarea>
        	</div>
        	<div class="form-group">
        		<?php $has_permission= check_permission(EDIT,"page_content");?>
                 <input type="submit" class="btn btn-info" value="<?php echo $this->lang->line('update');?>" <?php echo ($has_permission==1) ? "" : "disabled";?> >
        	</div>
        </div>
    <?php echo form_close(); ?>

    </div><!-- end col -->

</div>

<!-- End row -->

 <script src="<?php echo base_url(); ?>assets/plugins/tinymce/tinymce.min.js"></script>
 <script type="text/javascript">

    /*$(document).ready(function () {
        if($("#aboutus").length > 0){
            tinymce.init({
                selector: "textarea#aboutus",
                theme: "modern",
                height:300,
                plugins: [
                    "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                    "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                    "save table contextmenu directionality emoticons template paste textcolor"
                ],
                toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons",
                style_formats: [
                    {title: 'Bold text', inline: 'b'},
                    {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
                    {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
                    {title: 'Example 1', inline: 'span', classes: 'example1'},
                    {title: 'Example 2', inline: 'span', classes: 'example2'},
                    {title: 'Table styles'},
                    {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
                ]
            });
        }
    });*/

    $(document).ready(function () {
        if($("#aboutus").length > 0 || $("#contactus").length > 0 || $("#termscondition").length > 0){
            tinymce.init({
                selector: "textarea#aboutus, textarea#contactus, textarea#termscondition",
                theme: "modern",
                height:300,
                plugins: [
                    "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                    "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                    "save table contextmenu directionality emoticons template paste textcolor"
                ],
                toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor " 
            });            
        }
    });
  </script>