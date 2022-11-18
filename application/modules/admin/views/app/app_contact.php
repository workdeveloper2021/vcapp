<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div>  -->
<div class="row">
    <div class="col-sm-12"> 

        <div class="card-box">

  <?php if ($this->session->flashdata('appcontacterror') != '') { 
    echo '<h6 class="'.$this->session->flashdata('contactclass').'">'.$this->session->flashdata('appcontacterror').'</h6>';
  } ?>

    <?php 
    //  echo'<pre>';
   
    // foreach ($country_code as $code_val) {
    //      print_r($code_val);
    // }
    // die;
      $attributes = array('class' => 'form-horizontal', 'id' => '', "data-parsley-validate" => "");
      $hidden = array('is_submit' => 1);
      echo form_open_multipart('admin/app/contact_us_submit', $attributes, $hidden );
    ?>

  
            <div class="form-group row">
                <label class="col-2 col-form-label"><?php echo $this->lang->line('contact_email'); ?></label>
                <div class="col-10">
                    <input type="text" name="contact_email" id="contact_email" class="form-control" placeholder="<?php echo $this->lang->line('contact_email'); ?>" required="" value="<?php echo $contactemail; ?>" >
                </div>
            </div> 
            <div class="form-group row">
                <label class="col-2 col-form-label"><?php echo $this->lang->line('contact_address'); ?></label>
                <div class="col-10">
                    <input type="text" name="contact_address" id="contact_address" class="form-control fff" placeholder="<?php echo $this->lang->line('contact_address'); ?>" required="" value="<?php echo $contactaddress; ?>" >
                </div>
            </div>
            <div class="form-group row">
                <label class="col-2 col-form-label"><?php echo $this->lang->line('contact_number'); ?></label>
                <div class="col-10">
                    <input type="text" pattern="\d*" maxlength="12" name="contact_number" id="contact_number" class="form-control" placeholder="<?php echo $this->lang->line('contact_number'); ?>" required=""  data-parsley-minlength="8" data-parsley-maxlength="12" value="<?php echo  $contactphone; ?>" >                     
                </div>
            </div>
            <div class="form-group" style="margin: 0px;">
                <?php $has_permission= check_permission(EDIT,"contact_details");?>
                 <input type="submit" class="btn btn-info" value="<?php echo $this->lang->line('update');?>" <?php echo ($has_permission==1) ? "" : "disabled";?> >   
            </div>
    <?php echo form_close(); ?>
        </div>
    </div><!-- end col -->
</div>
<!-- End row -->
