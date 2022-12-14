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
      <a href="<?php echo site_url();?>admin/companies" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      <div class="card-box">
            <h4 class="header-title m-t-0 m-b-30"><?php echo $this->lang->line('basic_info'); ?></h4>
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            <?php 
              $attributes = array('class' => 'form-horizontal', 'id' => '', 'enctype' => 'multipart/form-data' );
              $hidden = array('is_submit' => 1);
              $parsle = 'data-parsley-validate novalidate';
              echo form_open_multipart('admin/companies/companyUpdate', $attributes, $hidden);
              //echo "<pre>";print_r($userinfo);
            ?>
                <div class="row">
                  <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                      <label for="inputName" class="control-label"><?php echo $this->lang->line('company_name'); ?>
                      </label>
                        <input type="text" name="updatename" class="form-control" id="inputName" value="<?php echo $companyinfo[0]['company_name'] ? $companyinfo[0]['company_name'] : ''; ?>" required>
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                      <label for="inputName" class="control-label"><?php echo $this->lang->line('tb_company_location'); ?>
                      </label>

                      <select name="updatelocation" class="form-control" id="inputName" required="">
                        <option value="">Select Location</option>
                        <?php foreach ($locations as $key => $value) {
                          ?>
                            <option value="<?php echo $value["location"]; ?>" <?php if($value["location"]==$companyinfo[0]['location']){ ?> selected <?php } ?> ><?php echo $value["location"]; ?></option>
                          <?php
                        } ?>
                      </select>

                    </div>
                  </div>
                </div>



                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                    <label for="inputName" class="control-label"><?php echo $this->lang->line('tb_company_info'); ?>
                      </label>
                      <textarea style="width: 1100px;" name="updateinfo" class="form-control" id="inputName"><?php echo $companyinfo[0]['info'] ? $companyinfo[0]['info'] : ''; ?></textarea>
                    </div>
                  </div>
                </div>


                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_company_thumbnail'); ?></label>
                      <input class="dropify" data-height="200" type="file" name="updateuserpic" class="form-control" id="inputName1" data-default-file="<?php echo site_url().'uploads/company_media/'. $companyinfo[0]['thumbnail']; ?>" accept="image/x-png,image/gif,image/jpeg" / >
                      <input type="hidden" name="oldpic" value="<?php echo $companyinfo[0]['thumbnail']; ?>">
                    </div>
                  </div>
                </div>

                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <label nowrap="" for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_company_video'); ?></label>
                      <input class="dropify" data-height="200" type="file" name="updatevideo" class="form-control" id="inputName1" data-default-file="<?php echo site_url().'uploads/company_media/'. $companyinfo[0]['video_url']; ?>" accept="video/mp4,video/x-m4v,video/*" / >
                      <input type="hidden" name="oldvid" value="<?php echo $companyinfo[0]['video_url']; ?>">
                    </div>
                  </div>
                </div>

                
                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                    <input type="hidden" name="updateid" class="form-control" value="<?php echo $companyinfo[0]['id']; ?>" />
                      <button type="submit" class="btn btn-info"><?php echo $this->lang->line('btn_company_update'); ?></button>
                    </div>
                  </div>

                 
             <?php echo form_close(); ?> 
        </div> 
      </div> 
      </div>
      <!-- /.row -->

    </section>
    </div>
