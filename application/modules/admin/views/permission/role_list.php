
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
     <div class="row">
      <div class="col-md-6 "></div>
       <div class="col-md-6 text-right">
          <a class="btn btn-primary" href="<?php echo base_url("admin/permission/roles");?>" title="<?php echo $this->lang->line('btn_add_role'); ?>"><?php echo $this->lang->line('btn_add_role'); ?></a>                
       </div>
     </div>
      <div class="card-box"> 
          <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>
        <table id="rolesList" class="table table-bordered table-responsive12">
            <thead>
            <tr>
              <th><?php echo $this->lang->line('sr_no'); ?></th>
              <th><?php echo $this->lang->line('tb_role_name'); ?></th>
              <th><?php echo $this->lang->line('tb_create'); ?></th> 
              <th><?php echo $this->lang->line('tb_action'); ?></th>
          </thead>
        </table>
      </div>
    </div>
  </div>  

<script type="text/javascript">
        $(document).ready(function(){  
        var sort_table = [0,1,3];
        var postListingUrl =  BASEURL+"admin/permission/rolesAjaxlist";
        var table = setTable('#rolesList',postListingUrl,sort_table);
    });

</script>
