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
              echo form_open_multipart('admin/companies/companyAddSubmit', $attributes, $hidden);
              //echo "<pre>";print_r($userinfo);
            ?>
                <div class="row">
                  <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                      <label for="inputName" class="control-label"><?php echo $this->lang->line('company_name'); ?>
                      </label>
                        <input type="text" name="updatename" class="form-control" id="inputName" value="" required>
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
                            <option value="<?php echo $value["location"]; ?>" ><?php echo $value["location"]; ?></option>
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
                      <textarea style="width: 1100px;" name="updateinfo" class="form-control" id="inputName" required=""></textarea>
                    </div>
                  </div>
                </div>


                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_company_thumbnail'); ?></label>
                      <input class="dropify" data-height="200" type="file" name="updateuserpic" class="form-control" id="inputName1"  accept="image/x-png,image/gif,image/jpeg" / >
                    </div>
                  </div>
                </div>

                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <label nowrap="" for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_company_video'); ?></label>
                      <input class="dropify" data-height="200" type="file" name="updatevideo" class="form-control" id="inputName1" accept="video/mp4,video/x-m4v,video/*" / >
                    </div>
                  </div>
                </div>



<!-- 
                <hr>
                <h4 class="header-title m-t-0 m-b-30"><?php echo $this->lang->line('title_add_company_retailer'); ?></h4>

                <br>
                <div id="file_div">
                  <div>                  
                    <div class="row">
                      <div class="col-sm-6">
                        <label for="inputName1" class="control-label"><?php echo $this->lang->line('company_retailer_name'); ?></label>
                        <input type='text' name='retail_name[]' class='form-control' required="" />
                      </div>
                      <div class="col-sm-6">
                        <label for="inputName1" class="control-label"><?php echo $this->lang->line('company_retailer_email'); ?></label>
                        <input type='email' name='retail_email[]' class='form-control' required="" />
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-6">
                          <label for="inputName" class="control-label"><?php echo $this->lang->line('company_retailer_country'); ?>
                          </label>

                          <select name="retailer_country[]" class="form-control" id="inputName" required="">
                            <option value="">Select Country</option>
                            <?php foreach ($locations as $key => $value) {
                              ?>
                                <option value="<?php echo $value["id"]; ?>" ><?php echo $value["location"]; ?></option>
                              <?php
                            } ?>
                          </select>
                      </div>
                      <div class="col-sm-6">
                        <label for="inputName1" class="control-label"><?php echo $this->lang->line('company_retailer_city'); ?></label>
                        <input type='text' name='retail_city[]' class='form-control' required="" />
                      </div>
                    </div>
                    <br>
                    <div class="text-center">

                      <input type='button' class='btn btn-danger'  value='REMOVE' onclick=remove_file(this);>

                    </div>
                  </div>
                </div>

                <br>
                <div class="text-center">
                  <input type="button" class="btn btn-success" onclick="add_file();" value="<?php echo $this->lang->line('title_add_more_company_retailer'); ?>">
                </div>

 -->


                  <br><br>
                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                      <button type="submit" class="btn btn-info"><?php echo $this->lang->line('add_company'); ?></button>
                    </div>
                  </div>

                 
             <?php echo form_close(); ?> 
        </div> 
      </div> 
      </div>
      <!-- /.row -->

    </section>
    </div>
    <script type="text/javascript">

        function add_file()
        {
         $("#file_div").append(`
                  <div>     
                    <hr style="border-top: 3px solid rgba(0,0,0,.1);">
             
                    <div class="row">
                      <div class="col-sm-6">
                        <label for="inputName1" class="control-label"><?php echo $this->lang->line('company_retailer_name'); ?></label>
                        <input type='text' name='retail_name[]' class='form-control' required="" />
                      </div>
                      <div class="col-sm-6">
                        <label for="inputName1" class="control-label"><?php echo $this->lang->line('company_retailer_email'); ?></label>
                        <input type='email' name='retail_email[]' class='form-control' required="" />
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-6">
                          <label for="inputName" class="control-label"><?php echo $this->lang->line('company_retailer_country'); ?>
                          </label>

                          <select name="retailer_country[]" class="form-control" id="inputName" required="">
                            <option value="">Select Country</option>
                            <?php foreach ($locations as $key => $value) {
                              ?>
                                <option value="<?php echo $value["id"]; ?>" ><?php echo $value["location"]; ?></option>
                              <?php
                            } ?>
                          </select>
                      </div>
                      <div class="col-sm-6">
                        <label for="inputName1" class="control-label"><?php echo $this->lang->line('company_retailer_city'); ?></label>
                        <input type='text' name='retail_city[]' class='form-control' required="" />
                      </div>
                    </div>
                    <div class="text-center">
                      <input type='button' class='btn btn-danger'  value='REMOVE' onclick=remove_file(this);>
                    </div>
                  </div>

                  `);
           $( document ).ready(function() {
              console.log( "ready!" );
          });

        }


      function remove_file(ele)
      {
       $(ele).parent().parent().remove();
      }



     $( document ).ready(function() {

        


    })



    </script>