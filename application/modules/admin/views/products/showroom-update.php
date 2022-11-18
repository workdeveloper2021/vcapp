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
              echo form_open_multipart('admin/showrooms/showroomUpdate', $attributes, $hidden);
              //echo "<pre>";print_r($userinfo);
            ?>
                <div class="row">
                  <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                      <label for="inputName" class="control-label"><?php echo $this->lang->line('tb_showroom_name'); ?>
                      </label>
                        <input type="text" name="updatename" class="form-control" id="inputName" value="<?php echo $showroominfo[0]['showroom_name'] ? $showroominfo[0]['showroom_name'] : ''; ?>" required>
                    </div>
                  </div>
                </div>




                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_showroom_thumbnail'); ?></label>
                      <input class="dropify" data-height="200" type="file" name="updateuserpic" class="form-control" id="inputName1" data-default-file="<?php echo site_url().'uploads/showroom_media/'. $showroominfo[0]['thumbnail']; ?>" accept="image/x-png,image/gif,image/jpeg" / >
                      <input type="hidden" name="oldpic" value="<?php echo $showroominfo[0]['thumbnail']; ?>">
                    </div>
                  </div>
                </div>



                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_showroom_img_360'); ?></label>
                      <input class="dropify" data-height="200" type="file" name="update360pic" class="form-control" id="inputName1" data-default-file="<?php echo site_url().'uploads/showroom_media/'. $showroominfo[0]['img_360']; ?>" accept="image/x-png,image/gif,image/jpeg" / >
                      <input type="hidden" name="old360pic" value="<?php echo $showroominfo[0]['img_360']; ?>">
                    </div>
                  </div>
                </div>





                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <label nowrap="" for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_showroom_bkground_video'); ?></label>
                      <input class="dropify" data-height="200" type="file" name="updatevideo" class="form-control" id="inputName1" data-default-file="<?php echo site_url().'uploads/showroom_media/'. $showroominfo[0]['video_url']; ?>" accept="video/mp4,video/x-m4v,video/*" / >
                      <input type="hidden" name="oldvid" value="<?php echo $showroominfo[0]['video_url']; ?>">
                    </div>
                  </div>
                </div>



                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <label nowrap="" for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_showroom_play_video'); ?></label>
                      <input class="dropify" data-height="200" type="file" name="updateplayvideo" class="form-control" id="inputName1" data-default-file="<?php echo site_url().'uploads/showroom_media/'. $showroominfo[0]['play_video_url']; ?>" accept="video/mp4,video/x-m4v,video/*" / >
                      <input type="hidden" name="oldplayvid" value="<?php echo $showroominfo[0]['play_video_url']; ?>">
                    </div>
                  </div>
                </div>

                
                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                    <input type="hidden" name="updateid" class="form-control" value="<?php echo $showroominfo[0]['id']; ?>" />
                    <input type="hidden" name="company_id" class="form-control" value="<?php echo $showroominfo[0]['company_id']; ?>" />
                      <button type="submit" class="btn btn-info"><?php echo $this->lang->line('btn_showroom_update'); ?></button>
                    </div>
                  </div>

                 
             <?php echo form_close(); ?> 
        </div> 
      </div> 
      </div>
      <!-- /.row -->

    </section>
    </div>
