<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div> -->

<div class="content-wrapper">
<section class="content">
  <div class="row">
   
    <div class="col-md-12">
      <a href="<?php echo site_url();?>admin/categoryMaster" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      <div class="card-box">
            <h4 class="header-title m-t-0 m-b-30"><?php echo $this->lang->line('basic_info'); ?></h4>
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            <?php 
              $attributes = array('class' => 'form-horizontal', 'id' => '');
              $hidden = array('is_submit' => 1);
              $parsle = 'data-parsley-validate novalidate';
              echo form_open_multipart('admin/categoryMaster/categoryUpdate', $attributes, $hidden);
              //echo "<pre>";print_r($catinfo);
            ?>
                 <div class="row">
                  <div class="col-md-6 col-sm-6">
                  <div class="form-group">
                    <label for="inputName" class="control-label"><?php echo $this->lang->line('tb_category_name'); ?>
                    </label>
                      <input type="text" name="category_name" class="form-control text_name" id="inputName" value="<?php echo $catinfo['category_name'] ? $catinfo['category_name'] : ''; ?>" placeholder="<?php echo $this->lang->line('tb_category_name'); ?>" required>
                  </div>
                </div>
                <div class="col-md-6 col-sm-6">
                  <div class="form-group">
                    <label for="inputName" class=" control-label"><?php echo $this->lang->line('tb_category_type'); ?>
                    </label>
                      
                      <select class="form-control" name="category_type" id="inputName" required>
                        <option value=""><?php echo $this->lang->line('tb_category_type'); ?></option>
                        <option <?php if($catinfo['category_type']=='1'){ ?>selected<?php } ?> value="1"><?php echo $this->lang->line('category_type_1'); ?></option>
                        <option <?php if($catinfo['category_type']=='2'){ ?>selected<?php } ?> value="2"><?php echo $this->lang->line('category_type_2'); ?></option>
                        <option <?php if($catinfo['category_type']=='3'){ ?>selected<?php } ?> value="3"><?php echo $this->lang->line('category_type_3'); ?></option>
                      </select>
                  </div>
                </div>
                </div>
                <div class="row">
                  <div class="col-md-6 col-sm-6">
                  <div class="form-group">
                      <label for="inputName" class="control-label"><?php echo $this->lang->line('price'); ?>
                      </label>
                      <input type="text" name="price" class="form-control" id="inputName" value="<?php echo $catinfo['price'] ? $catinfo['price'] : ''; ?>" placeholder="<?php echo $this->lang->line('price'); ?>" required data-parsley-type="number" data-parsley-type-message="please enter valid price">
                  </div>
                </div>
                <div class="col-md-6 col-sm-6">
                   <div class="form-group">  
                    <label for="inputName" class="control-label"><?php echo $this->lang->line('no_of_days'); ?></label>

                    <input type="text" name="no_of_days" class="form-control" id="inputName" value="<?php echo $catinfo['no_of_days'] ? $catinfo['no_of_days'] : ''; ?>" placeholder="<?php echo $this->lang->line('no_of_days'); ?>" required data-parsley-type="number" data-parsley-type-message="please enter valid no. of days.">
                    
                   </div>
                  </div>
                </div>
                
                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                    <input type="hidden" name="updateid" class="form-control" value="<?php echo $catinfo['id']; ?>" />
                      <button type="submit" class="btn btn-info"><?php echo $this->lang->line('btn_update_category'); ?></button>
                    </div>
                  </div>
             <?php echo form_close(); ?> 
        </div> 
      </div> 
      </div>
      <!-- /.row -->

    </section>
    </div>
