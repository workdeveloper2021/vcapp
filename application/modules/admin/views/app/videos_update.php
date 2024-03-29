﻿<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$description = $url = $video_title = $videos_id = '' ;
if(!empty($videos_data)){
    $video_title = $videos_data[0]['title'];
    $url = $videos_data[0]['url'];
    $description = $videos_data[0]['description'];
    $videos_id = $videos_data[0]['id'];
}
?>
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div>  -->
<div class="content-wrapper">
<section class="content">
  <div class="row">
   
    <div class="col-md-8">
      <a href="<?php echo site_url();?>admin/app/videos_list" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      <div class="card-box">
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            <?php 
              $attributes = array('class' => 'form-horizontal', 'id' => '', 'enctype' => 'multipart/form-data' );
              $hidden = array('is_submit' => 1);
              $parsle = 'data-parsley-validate novalidate';
              echo form_open_multipart('admin/app/videos_update_submit', $attributes, $hidden);
            ?>
                  <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_video_title'); ?>
                    </label>
                    <div class="col-sm-12">
                      <input type="text" name="title" class="form-control" id="inputName" value="<?php echo $video_title ?>" placeholder="<?php echo $this->lang->line('tb_video_title'); ?>" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_video_url'); ?>
                    </label>
                    <div class="col-sm-12">
                      <input type="file" name="video_url" class="form-control" id="inputName" value="<?php echo $url ?>" placeholder="<?php echo $this->lang->line('tb_video_url'); ?>" <?php if($url==""){ ?> required <?php } ?>><br/>
                       <?php if($url!=""){ ?>
                      <video width="320" height="240" controls><source src="<?php echo base_url().'uploads/video/'.$url; ?>" type="video/mp4">Your browser does not support the video tag.</video>
                      <?php } ?>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_video_desc'); ?>
                    </label>
                    <div class="col-sm-12">
                      <textarea name="description" class="form-control" id="inputName" placeholder="<?php echo $this->lang->line('tb_video_desc'); ?>" required ><?php echo $description ?></textarea>
                      
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                        <?php if(!empty($videos_id)){
                            $butn_lang = $this->lang->line('btn_update');
                         } else{ 
                            $butn_lang = $this->lang->line('btn_save');
                         } ?>
                         <input type="hidden" name="updateid" class="form-control" value="<?php echo $videos_id; ?>" />
                        <button type="submit" class="btn btn-info"><?php echo $butn_lang ; ?></button>
                    </div>
                  </div>
             <?php echo form_close(); ?> 
        </div> 
      </div> 
      </div>
      <!-- /.row -->

    </section>
    </div>