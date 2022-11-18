<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="content-wrapper">
<section class="content">
  <div class="row">
   
    <div class="col-md-12">
      <a href="<?php echo site_url();?>admin/StudioOwner" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      <div class="card-box">
            <h4 class="header-title m-t-0 m-b-30"><?php echo $this->lang->line('basic_info'); ?></h4>
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            <?php 
              $attributes = array('class' => 'form-horizontal', 'id' => '', 'enctype' => 'multipart/form-data' );
              $hidden = array('is_submit' => 1);
              $parsle = 'data-parsley-validate novalidate';
              echo form_open_multipart('admin/StudioOwner/userProfileUpdate', $attributes, $hidden);
            ?>
                <div class="row">
                  <div class="col-md-6 col-sm-6">
                  <div class="form-group">
                    <label for="inputName" class="control-label"><?php echo $this->lang->line('tb_first_name'); ?>
                    </label>
                      <input type="text" name="updatename" class="form-control text_name" id="inputName" value="<?php echo $userinfo['name'] ? $userinfo['name'] : ''; ?>" placeholder="<?php echo $this->lang->line('lab_firstname'); ?>" required>
                  </div>
                </div>
                <div class="col-md-6 col-sm-6">
                  <div class="form-group">
                    <label for="inputName" class=" control-label"><?php echo $this->lang->line('tb_last_name'); ?>
                    </label>
                      <input type="text" name="updatelastname" class="form-control text_name" id="inputName" value="<?php echo $userinfo['lastname'] ? $userinfo['lastname'] : ''; ?>" placeholder="<?php echo $this->lang->line('lab_lastname'); ?>" required>
                  </div>
                </div>
                </div>
                <div class="row">
                  <div class="col-md-6 col-sm-6">
                  <div class="form-group">
                   <label for="inputEmail" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_email'); ?></label>
                      <input type="email" name="updateemail" class="form-control" id="inputEmail" value="<?php echo $userinfo['email'] ? $userinfo['email'] : ''; ?>" placeholder="<?php echo $this->lang->line('lab_email'); ?>" readonly >
                  </div>
                </div>
                <div class="col-md-6 col-sm-6">
                   <div class="form-group">  
                    <label>Email Verification</label>
                   <?php if($userinfo['email_verified']==1){?>
                      <div class=" col-md-12 col-sm-12">
                      <span class="badge badge-info"><i class="fa fa-check" aria-hidden="true"></i> Verified</span> </div>
                      <?php }else{ ?> 
                        <div class="col-md-12 col-sm-12">
                         <label class="switch">
                        <input type="checkbox" name="email_verified" value="1" <?php echo ($userinfo['email_verified']== 1 ? 'checked' : '');?>>
                        <span class="slider round"></span>
                      </label>
                       </div>
                     <?php } ?>
                   </div>
                  </div>
                </div>
                 <div class="form-group">
                  <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_userpic'); ?></label>
                  <div class="col-sm-12">
                    <input class="dropify" data-height="200" type="file" name="updateuserpic" class="form-control" id="inputName1" data-default-file="<?php echo site_url().'uploads/user/'. $userinfo['profile_img']; ?>" accept="image/x-png,image/gif,image/jpeg" / >
                    <input type="hidden" name="oldpic" value="<?php echo $userinfo['profile_img']; ?>">
                  </div>
                </div>
                <!-- <div class="row">
                  <div class="col-md-6 col-sm-6">
                  <div class="form-group">
                    <label for="inputName" class="control-label"><input type="radio" name="merchant_type" id="merchant_type_usa" class="merchant_type" value="1" <?php echo ($userinfo['marchant_id_type']==1) ? 'checked' : '' ; ?> > USA Merchant Clover Details 
                    </label>
                    &nbsp;&nbsp;&nbsp;
                    <label for="cad_marchant_id" class="control-label"><input type="radio" name="merchant_type" id="merchant_type_cad" class="merchant_type" value="2" <?php echo ($userinfo['marchant_id_type']==2) ? 'checked' : '' ; ?>> CAD Merchant Clover Details 
                    </label>
                      <br><br>
                      <label for="cad_marchant_id" class="control-label">Clover Merchant Id</label>
                      <input type="text" name="marchant_id" class="form-control " id="usa_merchant_id" value="<?php echo $userinfo['marchant_id'] ? $userinfo['marchant_id'] : ''; ?>" placeholder="Clover <?php echo $this->lang->line('tb_marchant_id'); ?>" <?php echo (!empty($userinfo['marchant_id'])) ? '' : '' ; ?>  >
                      <br><br>
                      <label for="cad_marchant_id" class="control-label">Clover Key</label>
                      <input type="text" name="clover_key" class="form-control " id="usa_merchant_id" value="<?php echo $userinfo['clover_key'] ? $userinfo['clover_key'] : ''; ?>" placeholder="Clover Key" <?php echo (!empty($userinfo['clover_key'])) ? '' : '' ; ?>  >

                      <br><br>
                      <label for="cad_marchant_id" class="control-label">Clover Access Token</label>
                      <input type="text" name="access_token" class="form-control " id="usa_merchant_id" value="<?php echo $userinfo['access_token'] ? $userinfo['access_token'] : ''; ?>" placeholder="Clover Access Token" <?php echo (!empty($userinfo['access_token'])) ? '' : '' ; ?>  >
                  </div>
                </div> -->

                <div class="col-md-6 col-sm-6">
                  <div class="form-group">
                    
                     <!--  <input style="display: none;" type="text" name="cad_marchant_id" class="form-control " id="cad_marchant_id" value="<?php //echo $userinfo['cad_marchant_id'] ? $userinfo['cad_marchant_id'] : ''; ?>" placeholder="CAD <?php //echo $this->lang->line('tb_marchant_id'); ?>" <?php //echo (!empty($userinfo['cad_marchant_id'])) ? '' : 'readonly' ; ?> > -->

                    </div>
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

    <script type="text/javascript">
      /*$('.merchant_type').on('click', function(){
          var check_type = $(this).val();
          if (check_type == 1) {
            $('#usa_merchant_id').removeAttr('readonly');
            $('#cad_marchant_id').attr('readonly', true);
            $('#usa_merchant_id').attr('required', true);
            $('#cad_marchant_id').removeAttr('required');
            $('#cad_marchant_id').val('');
          }
          else{
            $('#cad_marchant_id').removeAttr('readonly');
            $('#usa_merchant_id').attr('readonly', true);
            $('#cad_marchant_id').attr('required', true);
            $('#usa_merchant_id').removeAttr('required');
            $('#usa_merchant_id').val('');
          }
      });*/
    </script>