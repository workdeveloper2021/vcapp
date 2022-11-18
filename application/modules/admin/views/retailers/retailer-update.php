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
              echo form_open_multipart('admin/retailers/retailerUpdate', $attributes, $hidden);
              //echo "<pre>";print_r($userinfo);
            ?>
               

                <div class="row">
                  <div class="col-md-8">
                    <div class="form-group">
                        <label for="inputName1" class="control-label"><?php echo $this->lang->line('company_retailer_name'); ?></label>                      </label>
                        <input type='text' name='retail_name' class='form-control' value="<?php echo $retailerinfo[0]['name']; ?>" required="" />
                    </div>
                  </div>
                </div>



                <div class="row">
                    <div class="col-md-8">
                   <div class="form-group">

                        <label for="inputName1" class="control-label"><?php echo $this->lang->line('company_retailer_email'); ?></label>
                        <input type='email' name='retail_email' class='form-control' value="<?php echo $retailerinfo[0]['email']; ?>" required="" />

                    </div>
                  </div>
                </div>


                <div class="row">
                    <div class="col-md-8">
                   <div class="form-group">

                          <label for="inputName" class="control-label"><?php echo $this->lang->line('company_retailer_country'); ?>
                          </label>

                          <select name="retailer_country" class="form-control" id="inputName" required="">
                            <option value="">Select Country</option>
                            <?php foreach ($locations as $key => $value) {
                              ?>
                                <option <?php if($retailerinfo[0]['country']==$value["id"]){ ?> selected <?php } ?> value="<?php echo $value["id"]; ?>" ><?php echo $value["location"]; ?></option>
                              <?php
                            } ?>
                          </select>

                    </div>
                  </div>
                </div>


                <div class="row">
                    <div class="col-md-8">
                   <div class="form-group">
                        <label for="inputName1" class="control-label"><?php echo $this->lang->line('company_retailer_city'); ?></label>
                        <input type='text' name='retail_city' class='form-control' required="" value="<?php echo $retailerinfo[0]['city']; ?>" />
                    </div>
                  </div>
                </div>

                
                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                    <input type="hidden" name="updateid" class="form-control" value="<?php echo $retailerinfo[0]['id']; ?>" />
                    <input type="hidden" name="company_id" class="form-control" value="<?php echo $retailerinfo[0]['company_id']; ?>" />
                      <button type="submit" class="btn btn-info"><?php echo $this->lang->line('btn_retailer_update'); ?></button>
                    </div>
                  </div>

                 
             <?php echo form_close(); ?> 
        </div> 
      </div> 
      </div>
      <!-- /.row -->

    </section>
    </div>
