<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div>  -->
<div class="content-wrapper">
<section class="content">
  <div class="row">
   
    <div class="col-md-8">
      <a href="<?php echo site_url();?>admin/subscription/plan_list" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      <div class="card-box">
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            <?php 
              $attributes = array('class' => 'form-horizontal', 'id' => '', 'enctype' => 'multipart/form-data' );
              $hidden = array('is_submit' => 1);
              $parsle = 'data-parsley-validate novalidate';
              echo form_open_multipart('admin/subscription/plan_update_submit', $attributes, $hidden);
            ?>
                  <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_plan_name'); ?>
                    </label>
                    <div class="col-sm-12">
                      <input type="text" name="plan_name" class="form-control" id="inputName" value="<?php echo $plandata[0]['plan_name'] ? $plandata[0]['plan_name'] : ''; ?>" placeholder="<?php echo $this->lang->line('tb_plan_name'); ?>" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_plan_price'); ?>
                    </label>
                    <div class="col-sm-12">
                      <input type="text" name="amount" class="form-control" id="inputName" value="<?php echo $plandata[0]['amount'] ? $plandata[0]['amount'] : ''; ?>" placeholder="<?php echo $this->lang->line('tb_plan_price'); ?>" required data-parsley-type="number" data-parsley-type-message="please enter valid plan price">
                    </div>
                  </div>
                    
                    <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_subscription_users'); ?>
                    </label>
                    <div class="col-sm-12">
                     <input type="text" name="max_users" class="form-control" id="inputName" value="<?php echo $plandata[0]['max_users'] ? $plandata[0]['max_users'] : '0'; ?>" placeholder="<?php echo $this->lang->line('tb_subscription_users'); ?>" required data-parsley-type="number" data-parsley-type-message="please enter valid number">
                    </div>
                  </div> 
                

                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                    <input type="hidden" name="updateid" class="form-control" value="<?php echo $plandata[0]['id']; ?>" />
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