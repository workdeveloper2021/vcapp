<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div>  -->
<div class="content-wrapper">
<section class="content">
  <div class="row">
   
    <div class="col-md-12">
      <a href="<?php echo site_url();?>admin/users" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      <div class="card-box">
            <h4 class="header-title m-t-0 m-b-30"><?php echo $this->lang->line('basic_info'); ?></h4>
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            <?php 
              $attributes = array('class' => 'form-horizontal', 'id' => '', 'enctype' => 'multipart/form-data' );
              $hidden = array('is_submit' => 1);
              $parsle = 'data-parsley-validate novalidate';
              echo form_open_multipart('admin/users/userProfileUpdate', $attributes, $hidden);
            ?>
                  <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_first_name'); ?>
                    </label>
                    <div class="col-sm-12">
                      <input type="text" name="updatename" class="form-control" id="inputName" value="<?php echo $userinfo['name'] ? $userinfo['name'] : ''; ?>" placeholder="<?php echo $this->lang->line('lab_firstname'); ?>" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_last_name'); ?>
                    </label>
                    <div class="col-sm-12">
                      <input type="text" name="updatelastname" class="form-control" id="inputName" value="<?php echo $userinfo['lastname'] ? $userinfo['lastname'] : ''; ?>" placeholder="<?php echo $this->lang->line('lab_lastname'); ?>" required>
                    </div>
                  </div>
                  
                  <!-- <div class="form-group">
                    <label for="inputEmail" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_email'); ?></label>
                    <div class="col-sm-12">
                      <input type="email" name="updateemail" class="form-control" id="inputEmail" value="<?php echo $userinfo['user_email'] ? $userinfo['user_email'] : ''; ?>" placeholder="<?php echo $this->lang->line('lab_email'); ?>" readonly required >
                    </div>
                  </div> -->
                  <!-- <div class="form-group">
                    <label for="inputPhone" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_phone'); ?></label>
                    <div class="col-sm-12">
                      <input type="email" name="updatephone" class="form-control" id="inputPhone" value="<?php echo $userinfo['user_phone'] ? $userinfo['user_phone'] : ''; ?>" placeholder="<?php echo $this->lang->line('lab_phone'); ?>" readonly required >
                    </div>
                  </div> -->
                  
                   <div class="form-group">
                    <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_userpic'); ?></label>
                    <div class="col-sm-12">
                      <input class="dropify" data-height="200" type="file" name="updateuserpic" class="form-control" id="inputName1" data-default-file="<?php echo site_url().'uploads/user/'. $userinfo['profile_img']; ?>" accept="image/x-png,image/gif,image/jpeg" / >
                      <input type="hidden" name="oldpic" value="<?php echo $userinfo['profile_img']; ?>">
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                    <input type="hidden" name="updateid" class="form-control" value="<?php echo $userinfo['id']; ?>" />
                      <button type="submit" class="btn btn-info"><?php echo $this->lang->line('btn_update_profile'); ?></button>
                    </div>
                  </div>
             <?php echo form_close(); ?> 
        </div> 
      </div> 
      </div>
      <!-- /.row -->

    </section>
    </div>