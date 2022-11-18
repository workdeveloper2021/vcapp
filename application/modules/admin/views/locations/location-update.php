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
      <a href="<?php echo site_url();?>admin/location" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      <div class="card-box">
            <h4 class="header-title m-t-0 m-b-30"><?php echo $this->lang->line('basic_info'); ?></h4>
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            <?php 
              $attributes = array('class' => 'form-horizontal', 'id' => '', 'enctype' => 'multipart/form-data' );
              $hidden = array('is_submit' => 1);
              $parsle = 'data-parsley-validate novalidate';
              echo form_open_multipart('admin/location/locationUpdate', $attributes, $hidden);
              //echo "<pre>";print_r($userinfo);
            ?>
                <div class="row">
                  <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                      <label for="inputName" class="control-label"><?php echo $this->lang->line('location_name'); ?>
                      </label>
                        <input type="text" name="updatename" class="form-control" id="inputName" value="<?php echo $locations[0]['location'] ? $locations[0]['location'] : ''; ?>" required>
                    </div>
                  </div>
                </div>

                
                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                    <input type="hidden" name="updateid" class="form-control" value="<?php echo $locations[0]['id']; ?>" />
                      <button type="submit" class="btn btn-info"><?php echo $this->lang->line('btn_location_update'); ?></button>
                    </div>
                  </div>

                 
             <?php echo form_close(); ?> 
        </div> 
      </div> 
      </div>
      <!-- /.row -->

    </section>
    </div>
