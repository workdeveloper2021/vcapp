<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 $category_name = $category_parent = $category_type =  $category_id = '' ;
if(!empty($category_data)){
    $category_name = $category_data[0]['category_name'];
    $category_parent = $category_data[0]['category_parent'];
    $category_type = $category_data[0]['category_type'];
    $category_id = $category_data[0]['id'];
}
?>
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div>  -->
<div class="content-wrapper">
<section class="content">
  <div class="row">
   
    <div class="col-md-8">
      <a href="<?php echo site_url();?>admin/app/category_list" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      <div class="card-box">
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            <?php 
              $attributes = array('class' => 'form-horizontal', 'id' => '', 'enctype' => 'multipart/form-data' );
              $hidden = array('is_submit' => 1);
              $parsle = 'data-parsley-validate novalidate';
              echo form_open_multipart('admin/app/category_update_submit', $attributes, $hidden);
            ?>
                  <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_category_name'); ?>
                    </label>
                    <div class="col-sm-12">
                      <input type="text" name="category_name" class="form-control" id="inputName" value="<?php echo $category_name ?>" placeholder="<?php echo $this->lang->line('tb_category_name'); ?>" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_category_type'); ?>
                    </label>
                    <div class="col-sm-12">
                      <select  name="category_type" class="form-control" id="category_type" required="" data-parsley-type-message="Please select type">
                          <option value="">--Select--</option>
                          <option value="1" <?php echo ($category_type == '1') ?  'selected' :'' ; ?> >Skills Catgeory</option>
                          <option value="2" <?php echo ($category_type == '2') ?  'selected' : '' ; ?> >Business Category</option>
                          <option value="3" <?php echo ($category_type == '3') ? 'selected' : '' ; ?> >Product Category</option>
                      </select>

                    </div>
                  </div>

                   <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_category_parent_name'); ?>
                    </label>
                    <div class="col-sm-12">
                      <select  name="category_parent" class="form-control" id="category_parent" >
                          <option value="">--Select--</option>
                          <?php if (!empty($parent_data)) {
                              foreach ($parent_data as $p_data) { ?> 
                                <option value="<?php echo $p_data['id'] ?>" <?php echo ($category_parent == $p_data['id'] ) ?  'selected' : '' ; ?> ><?php echo $p_data['category_name'] ?></option>
                            <?php }
                          } ?>
                      </select>

                    </div>
                  </div>
                    
                    
                

                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                        <?php if(!empty($category_id)){
                            $butn_lang = $this->lang->line('btn_update');
                         } else{ 
                            $butn_lang = $this->lang->line('btn_save');
                         } ?>
                         <input type="hidden" name="updateid" class="form-control" value="<?php echo $category_id; ?>" />
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