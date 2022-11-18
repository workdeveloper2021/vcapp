<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div>  -->
<div class="content-wrapper">

<section class="content ">
  <div class="row">
   
    <div class="col-md-12">
        <a href="<?php echo site_url();?>admin/email_template" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      <div class="card-box">
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            <?php     
              $attributes = array('class' => 'form-horizontal', 'id' => '', 'enctype' => 'multipart/form-data' );
              $hidden = array('is_submit' => 1);
              $parsle = 'data-parsley-validate novalidate';
              echo form_open_multipart('admin/email_template/editMailAction',$attributes,$hidden);
            ?>
                  <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_template_subject'); ?>
                    </label>
                    <div class="col-sm-12">
                       <input type="text" name="subject"  required="" placeholder="Enter Subject" class="form-control" value="<?php echo $email_data[0]['subject']; ?>">
                    </div>
                  </div>
                  
                   <div class="form-group">
                    <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('lab_description'); ?></label>
                    <div class="col-sm-12">
                       <textarea id="elm" name="description" required="" ><?php echo $email_data[0]['description']; ?></textarea>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                    <input type="hidden" name="template_id" class="form-control" value="<?php echo encode($email_data[0]['id']); ?>" />
                      <button type="submit" class="btn btn-info"><?php echo $this->lang->line('btn_update_template'); ?></button>
                    </div>
                  </div>
             <?php echo form_close(); ?> 
        </div> 
      </div> 
      </div>
      <!-- /.row -->

    </section>
    </div>

<script src="https://cdn.ckeditor.com/4.7.3/standard/ckeditor.js"></script>

<script type="text/javascript">
  CKEDITOR.replace( 'elm' );
  $('#elm').attr('required', '');

//deal with copying the ckeditor text into the actual textarea
CKEDITOR.on('instanceReady', function (){
    $.each(CKEDITOR.instances, function (instance) {
        CKEDITOR.instances[instance].document.on("keyup", CK_jQ);
        CKEDITOR.instances[instance].document.on("paste", CK_jQ);
        CKEDITOR.instances[instance].document.on("keypress", CK_jQ);
        CKEDITOR.instances[instance].document.on("blur", CK_jQ);
        CKEDITOR.instances[instance].document.on("change", CK_jQ);
    });
});

function CK_jQ() {
    for (instance in CKEDITOR.instances) {
        CKEDITOR.instances[instance].updateElement();
    }
}
</script>