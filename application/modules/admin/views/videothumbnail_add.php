<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?> 
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div>    -->
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
      <div class="card-box">

        <?php
         //echo phpinfo();
//         echo $ffmpeg = trim(shell_exec('which ffmpeg')); // or better yet:
//         echo $ffmpeg = trim(shell_exec('type -P ffmpeg'));
//         if (empty($ffmpeg))
//         {
//             die('ffmpeg not available');
//         }
//       shell_exec($ffmpeg . ' -i ...');
// die;
         if ($this->session->flashdata('asseterror') != '') { 
          echo '<h6 class="'.$this->session->flashdata('assetclass').'">'.$this->session->flashdata('asseterror').'</h6>';
        } ?>
        <?php 
          $attributes = array('class' => 'form-horizontal', 'id' => '');
          $hidden = array('is_submit' => 1);
          echo form_open_multipart('admin/videothumbnail/assets_submit', $attributes, $hidden );
        ?>

          <div class="form-group row file_section">
              <label class="col-2 col-form-label"><?php echo $this->lang->line('lab_select_asset'); ?></label>
              <div class="col-10">
                <input type="file" name="assets_image" class="dropify" data-height="300" data-default-file="">
              </div>
          </div>
          
        <div class="form-group row">
          <label class="col-2 col-form-label"></label>
            <div class="col-10">
             <button type="submit" class="btn btn-info"><?php echo $this->lang->line('btn_save'); ?></button>
            </div>
        </div>
          <?php echo form_close(); ?> 
      </div>
    </div>
  </div>  

 



