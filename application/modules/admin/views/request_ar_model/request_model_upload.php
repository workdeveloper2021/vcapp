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
      <a href="<?php echo site_url();?>admin/request_ar_model" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      <div class="card-box">
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            <?php 
              $attributes = array('class' => 'form-horizontal', 'id' => '', 'enctype' => 'multipart/form-data' );
              $hidden = array('is_submit' => 1);
              $parsle = 'data-parsley-validate novalidate';
              echo form_open_multipart('admin/request_ar_model/uploadReqModels', $attributes, $hidden);
              //echo "<pre>";print_r($userinfo);
            ?>
                 
               
                   <div class="form-group">
                    <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_requested_product_name'); ?></label>
                    <div class="col-sm-6">
                      <input type="text" required name="product_name" class="form-control" / >
                    </div>
                  </div>
              

               
                   <div class="form-group">
                    <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('product_3d_model_glb'); ?></label>
                    <div class="col-sm-6">
                      <input type="file" required name="glbFile" class="form-control" / >
                    </div>
                  </div>
              

                   <div class="form-group">
                    <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('product_3d_model_usdz'); ?></label>
                    <div class="col-sm-6">
                      <input type="file" required name="usdzFile" class="form-control" / >
                    </div>
                  </div>
                                 
                    <input type="hidden" name="reqId" class="form-control" value="<?php echo $reqId; ?>" />


                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                      <button type="submit" class="btn btn-info"><?php echo $this->lang->line('btn_submit'); ?></button>
                    </div>
                  </div>
             <?php echo form_close(); ?> 
        </div> 
      </div> 
      </div>
      <!-- /.row -->

    </section>
    </div>
