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
      <a href="<?php echo site_url();?>admin/permission" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      <div class="card-box">
            <h4 class="header-title m-t-0 m-b-30"><?php echo $this->lang->line('basic_info'); ?></h4>
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            <?php 
              $attributes = array('class' => 'form-horizontal', 'id' => '', 'enctype' => 'multipart/form-data' );
              $hidden = array('is_submit' => 1);
              $parsle = 'data-parsley-validate novalidate';
              echo form_open_multipart('admin/permission/userProfileSubmit',$attributes,$hidden);
            ?>
                  <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_first_name'); ?>
                    </label>
                    <div class="col-sm-12">
                      <input type="text" name="name" class="form-control text_name" id="inputName" value="<?php echo $userinfo['name'] ? $userinfo['name'] : ''; ?>" placeholder="<?php echo $this->lang->line('lab_firstname'); ?>" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_last_name'); ?>
                    </label>
                    <div class="col-sm-12">
                      <input type="text" name="lastname" class="form-control text_name" id="inputName" value="<?php echo $userinfo['lastname'] ? $userinfo['lastname'] : ''; ?>" placeholder="<?php echo $this->lang->line('lab_lastname'); ?>" required>
                    </div>
                  </div>     
                 <div class="form-group">
                    <label for="inputEmail" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_email'); ?></label>
                    <div class="col-sm-12">
                      <input type="email" name="email" class="form-control" id="inputEmail" value="<?php echo (!empty($userinfo['email'])) ? $userinfo['email'] : ''; ?>" placeholder="<?php echo $this->lang->line('lab_email'); ?>" <?php echo (!empty($userinfo['email'])) ? 'readonly' : 'required'; ?>>
                    </div>
                  </div> 
                 <div class="form-group">
                    <label for="inputPhone" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_phone'); ?></label>
                    <div class="col-sm-12">
                      <input type="text" name="phone" class="form-control" id="inputPhone" value="<?php echo (!empty($userinfo['mobile'])) ? $userinfo['mobile'] : ''; ?>" placeholder="<?php echo $this->lang->line('lab_phone'); ?>" <?php echo (!empty($userinfo['mobile'])) ? 'readonly' : 'required'; ?>>
                    </div>
                  </div> 
                  <?php if(empty($userinfo['password'])){?>
                    <div class="form-group">
                    <label for="inputPhone" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_password'); ?></label>
                    <div class="col-sm-12">
                      <input type="password" name="passowrd" class="form-control" id="inputPhone" placeholder="<?php echo $this->lang->line('lab_password'); ?>" required >
                    </div>
                  </div> 
                <?php }?>
                  <div class="form-group">
                    <label for="inputPhone" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_role'); ?></label> 
                    <div class="col-sm-12">
                      <select name="roles" class="form-control" required>
                        <option value="">-Please Select-</option>
                        <?php if(!empty($roledata)){ foreach($roledata as $value){?>
                        <option  value="<?php echo $value['id'];?>" <?php echo ($value['id']==$userinfo['role_id']) ? "selected" : "";?> ><?php echo $value['role_name'];?></option>
                      <?php }}?>
                      </select>

                    </div>
                  </div> 
                  
                   <div class="form-group">
                    <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_userpic'); ?></label>
                    <div class="col-sm-12">
                      <input class="dropify" data-height="200" type="file" name="userpic" class="form-control" id="inputName1" data-default-file="<?php echo site_url().'uploads/user/'. $userinfo['profile_img']; ?>" accept="image/x-png,image/gif,image/jpeg" / >
                      <input type="hidden" name="oldpic" value="<?php echo $userinfo['profile_img']; ?>">
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                    <input type="hidden" name="updateid" class="form-control" value="<?php echo (!empty($userinfo['id'])) ? encode($userinfo['id']) : ''; ?>" />
                         <button type="submit" class="btn btn-info"><?php echo (!empty($userinfo['id'])) ? $this->lang->line('btn_update_profile') : $this->lang->line('btn_save'); ?></button>
                    </div>
                  </div>
             <?php echo form_close(); ?> 
        </div> 
      </div> 
      </div>
      <!-- /.row -->

    </section>
    </div>