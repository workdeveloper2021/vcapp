<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div>  -->
<div class="row">
    <div class="col-sm-12"> 

        <div class="card-box">

  <?php if ($this->session->flashdata('appsettingerror') != '') { 
    echo '<h6 class="'.$this->session->flashdata('appsettingclass').'">'.$this->session->flashdata('appsettingerror').'</h6>';
  } ?>

    <?php 
    //  echo'<pre>';
   
    // foreach ($country_code as $code_val) {
    //      print_r($code_val);
    // }
    // die;
      $attributes = array('class' => 'form-horizontal', 'id' => '', "data-parsley-validate" => "");
      $hidden = array('is_submit' => 1);
      echo form_open_multipart('admin/app/app_settings_submit', $attributes, $hidden );
    ?>
            <div class="form-group ">
                <label class="col-2 col-form-label"><?php echo $this->lang->line('lab_site_title'); ?></label>
                <div class="col-10">
                    <input type="text" name="site_title" id="contact_email" class="form-control" placeholder="<?php echo $this->lang->line('lab_site_title'); ?>" required="" value="<?php echo (!empty($sitetitle)) ? $sitetitle : '' ; ?>" >
                </div>
            </div> 
              <div class="form-group">
                <label for="inputName1" class="col-2 col-form-label"><?php echo $this->lang->line('lab_site_logo'); ?></label>
                <div class="col-10">
                  <input class="dropify" data-height="200" type="file" name="site_logo" class="form-control" id="inputName1" data-default-file="<?php echo (!empty($sitetitle)) ? site_url().$sitelogo  : '' ; ?>" accept="image/x-png,image/gif,image/jpeg" / >
                  <input type="hidden" name="oldpic" value="<?php echo $sitelogo; ?>">
                </div>
              </div>  
            <div class="form-group" style="margin: 0px;">
               <?php $has_permission= check_permission(EDIT,"website_settings");?>
                 <input type="submit" class="btn btn-info" value="<?php echo $this->lang->line('update');?>" <?php echo ($has_permission==1) ? "" : "disabled";?> >  
            
            </div>
    <?php echo form_close(); ?>
        </div>
    </div><!-- end col -->
</div>
<!-- End row -->