
<style>
  .d-inline {
    display: inline-block;
  }
</style>
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?> 
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div>  -->
  <div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">  
     <a href="<?php echo site_url();?>admin/app/newsfeed_update" class="btn btn-back"><?php echo $this->lang->line('add_newsfeed'); ?></a>  <br /><br />  
      <div class="card-box"> 
          <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>
        <table id="planlist" class="table table-bordered">
            <thead>
            <tr>
              <th><?php echo $this->lang->line('sr_no'); ?></th>
              <th><?php echo $this->lang->line('tb_newsfeed_title'); ?></th>
              <th><?php echo $this->lang->line('tb_newsfeed_url'); ?></th>
              <th><?php echo $this->lang->line('tb_newsfeed_desc'); ?></th>
              <th><?php echo $this->lang->line('tb_create'); ?></th> 
              <th><?php echo $this->lang->line('tb_newsfeed_status'); ?></th>
              <th><?php echo $this->lang->line('tb_action'); ?></th>
          </thead>
       
        </table>
      </div>
    </div>
  </div>  


<script type="text/javascript">
       $(document).ready(function(){  
        var sort_table = [0,4];
        var postListingUrl =  BASEURL+"admin/app/newsfeedAjaxlist";
        var table = setTable('#planlist',postListingUrl,sort_table);
    });



 

</script>
