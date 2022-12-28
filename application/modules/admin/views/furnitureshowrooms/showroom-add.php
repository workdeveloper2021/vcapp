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
      <a href="<?php echo site_url();?>admin/FurnitureShowrooms/companyshowrooms/<?php echo $cid;?>" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      <div class="card-box">
            <!-- <h4 class="header-title m-t-0 m-b-30"><?php echo $this->lang->line('basic_info'); ?></h4> -->
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            <?php 
              $attributes = array('class' => 'form-horizontal', 'id' => '', 'enctype' => 'multipart/form-data' );
              $hidden = array('is_submit' => 1);
              $parsle = 'data-parsley-validate novalidate';
              echo form_open_multipart('admin/FurnitureShowrooms/showroomAddSubmit/'.$cid, $attributes, $hidden);
              //echo "<pre>";print_r($userinfo);
            ?>
                <div class="row">
                  <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                      <label for="inputName" class="control-label"><?php echo $this->lang->line('tb_showroom_name'); ?>
                      </label>
                        <input type="text" name="updatename" class="form-control" id="inputName" value="" required>
                    </div>
                  </div>
                </div>
                <br><br>
                <div class="row">
                  <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                      <label for="inputName" class="control-label">Showroom Information
                      </label>
                        <textarea name="information" class="form-control" id="inputName" value="" required></textarea>
                    </div>
                  </div>
                   <div class="col-md-6 col-sm-6">
                      <label nowrap="" for="inputName1" class="col-sm-6 control-label">Infor Hide/Show</label>
                      <select class="form-control" name="info_status" id="info_status">
                        <option value="0">Hide</option>
                        <option value="1" selected>Show</option>
                      </select>
                    </div>
                </div>
                <br><br>


                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_showroom_thumbnail'); ?></label>
                      <input class="dropify" data-height="200" type="file" name="updateuserpic" class="form-control" id="inputName1"  accept="image/x-png,image/gif,image/jpeg" / >
                    </div>
                  </div>
                </div>
                <br><br>


                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <label nowrap="" for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_showroom_bkground_video'); ?></label>
                      <input class="dropify" data-height="200" type="file" name="updatevideo" class="form-control" id="inputName1" accept="video/mp4,video/x-m4v,video/*" / >
                    </div>
                  </div>
                </div>
                <br><br>
                

                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <label nowrap="" for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_showroom_play_video'); ?></label>
                      <input class="dropify" data-height="200" type="file" name="updateplayvideo" class="form-control" id="inputName1" accept="video/mp4,video/x-m4v,video/*" / >
                    </div>
                  </div>
                </div>

                <br><br>
              
                
                
                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                      <button type="submit" class="btn btn-info"><?php echo $this->lang->line('btn_add_showroom'); ?></button>
                    </div>
                  </div>

                 
             <?php echo form_close(); ?> 
        </div> 
      </div> 
      </div>
      <!-- /.row -->

    </section>


    </div>
