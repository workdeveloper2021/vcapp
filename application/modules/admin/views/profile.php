<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div>  -->
<div class="content-wrapper">
<section class="content">
      <div class="row">

      <div class="col-md-6">
        <div class="card-box">
          <h4 class="header-title m-t-0 m-b-30"><?php echo $this->lang->line('changepassword'); ?></h4>
          <div class="box box-primary">
            <div class="box-body box-profile">
            
  <?php if ($this->session->flashdata('passworderror') != '') { 
    echo '<h6 class="'.$this->session->flashdata('changepasswordclass').'">'.$this->session->flashdata('passworderror').'</h6>';
  } ?>

  <?php 
    $attributes = array('class' => 'form-horizontal', 'id' => '');
    $hidden = array('is_submit' => 1);
    echo form_open_multipart('admin/changepassword', $attributes, $hidden );
  ?>
  
    <div class="form-group">
      <label for="inputNamee" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_current_pass'); ?></label>
      <div class="col-sm-12">
        <input type="password" name="updateoldpass" class="form-control" id="inputNamee" placeholder="<?php echo $this->lang->line('lab_current_pass'); ?>" required>
      </div>
    </div> 

    <div class="form-group">
      <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_new_password'); ?></label>
      <div class="col-sm-12">
        <input type="password" name="updatenewnm" class="form-control" id="new_password" placeholder="<?php echo $this->lang->line('lab_new_password'); ?>" required  data-parsley-required-message=" New password is required" data-parsley-minlength="6" data-parsley-maxlength="16" data-parsley-type="alphanum" data-parsley-minlength-message="New password is too short. It should have 6 characters or more."  data-parsley-maxlength-message=" New password is too long. It should have 16 characters or fewer.">
      </div>
    </div>
    <div class="form-group">
      <label for="inputName2" class="col-sm-12 control-label"><?php echo $this->lang->line('lab_new_confirm_password'); ?></label>
      <div class="col-sm-12">
        <input type="password" name="updatecofpassnm" class="form-control" id="inputName2" placeholder="<?php echo $this->lang->line('lab_new_confirm_password'); ?>"  required data-parsley-required-message=" Confirm password is required" data-parsley-equalto="#new_password" data-parsley-equalto-message=" Confirm password should be same" >
      </div>
    </div> 

    <div class="form-group">
      <div class="col-sm-offset-3 col-sm-12">
        <button type="submit" class="btn btn-info"><?php echo $this->lang->line('btn_change_pass'); ?></button>
      </div>
    </div>

    <?php echo form_close(); ?> 

            </div> 
          </div>

        </div> 
      </div>   
        <div class="col-md-6">
          <div class="card-box">
            <h4 class="header-title m-t-0 m-b-30"><?php echo $this->lang->line('basic_info'); ?></h4>
  <?php if ($this->session->flashdata('updateerror') != '') { 
    echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
  } ?>

    <?php 
      $attributes = array('class' => 'form-horizontal', 'id' => '');
      $hidden = array('is_submit' => 1, 'asset_type'=>'Picture');
      echo form_open_multipart('admin/profileupdate', $attributes, $hidden );
    ?>
                  <div class="form-group">
                    <label for="inputfName" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_firstname'); ?></label>
                <div class="col-sm-12">
                      <input type="text" name="updatefirstnm" class="form-control text_name" id="inputfName" value="<?php echo $userinfo['name'] ? $userinfo['name'] : ''; ?>" placeholder="<?php echo $this->lang->line('lab_firstname'); ?>" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputlastName" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_lastname'); ?></label>
                <div class="col-sm-12">
                      <input type="text" name="updatelastnm" class="form-control text_name" id="inputlastName" value="<?php echo $userinfo['lastname'] ? $userinfo['lastname'] : ''; ?>" placeholder="<?php echo $this->lang->line('lab_lastname'); ?>">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputEmail" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_email'); ?></label>
                    <div class="col-sm-12">
                      <input type="email" name="updateemail" class="form-control" id="inputEmail" value="<?php echo $userinfo['email'] ? $userinfo['email'] : ''; ?>" placeholder="<?php echo $this->lang->line('lab_email'); ?>" readonly  >
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputPhone" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_phone'); ?></label>
                    <div class="col-sm-12">
                      <input type="number" name="updatephone" class="form-control" id="inputPhone" value="<?php echo $userinfo['mobile'] ? $userinfo['mobile'] : ''; ?>" placeholder="<?php echo $this->lang->line('lab_phone'); ?>" readonly  >
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_current_pass'); ?></label>
                    <div class="col-sm-12">
                      <input type="password" name="updateoldpass" class="form-control" id="inputName" placeholder="<?php echo $this->lang->line('lab_current_pass'); ?>" required>
                    </div>
                  </div> 
                   <div class="form-group">
                    <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_userpic'); ?></label>
                    <div class="col-sm-12">
                      <input class="dropify" data-height="200" type="file" accept="image/*" name="updateuserpic" class="form-control" id="inputName1" data-default-file="<?php echo site_url().'uploads/user/'. $userinfo['profile_img']; ?>" >
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