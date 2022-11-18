
<style>
  .d-inline {
    display: inline-block;
  }
</style>


<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?> 
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div> -->
  <div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">  
      <div class="row">
        <div class="col-md-6">
         <?php   if(check_permission(STATUS,"business_list")==1){?>
         <form action="<?php echo base_url('admin/StudioOwner/business_status_change/'.encode(1)) ?>" class="d-inline" data-parsley-validate novalidate method="post">
             <input type="hidden" name="ids" value="" class="getIds">
             <button type="submit" class="btn btn-primary">Active</button>
         </form>
          <form action="<?php echo base_url('admin/StudioOwner/business_status_change/'.encode(2)) ?>" class="d-inline" data-parsley-validate novalidate method="post">
             <input type="hidden" name="ids" value="" class="getIds">
             <button type="submit" class="btn btn-primary">Inactive</button>
         </form> 
       <?php }?>
                 
           </div>
               <div class="col-md-6 text-right">

                  <!-- <a href="<?php echo base_url() ?>admin/StudioOwner/add_studio_owner" class="btn btn-primary"><?php echo $this->lang->line('btn_add_owner'); ?></a> -->

                  <a class="btn btn-active waves-effect m-b-10" href="<?php echo base_url("admin/StudioOwner/exportCsvBusiness");?>" title="<?php echo $this->lang->line('btn_export_csv_text'); ?>"><?php echo $this->lang->line('btn_export_csv_text'); ?></a>

              </div>
               
            </div>  
      <div class="card-box"> 
          <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>
        <table id="userList" class="table table-bordered table-responsive">
            <thead>
            <tr>
              <th> <input type="checkbox" name="checkAll[]" id="checkall"></th> 
              <th><?php echo $this->lang->line('sr_no'); ?></th>
              <th><?php echo $this->lang->line('tb_full_name'); ?></th> 
              <th><?php echo $this->lang->line('tb_business_name'); ?></th>
              <th><?php echo $this->lang->line('tb_business_image'); ?></th> 
              <th><?php echo $this->lang->line('tb_email'); ?></th>
              <th><?php echo $this->lang->line('tb_phone'); ?></th>
              <th><?php echo $this->lang->line('tb_country'); ?></th>
              <th><?php echo $this->lang->line('tb_state'); ?></th>
              <th><?php echo $this->lang->line('tb_city'); ?></th>
              <th class="address_box"><?php echo $this->lang->line('tb_address'); ?></th>
              <!-- <th width="20%"><?php echo $this->lang->line('tb_business_category'); ?></th> -->
              <th><?php echo $this->lang->line('tb_area'); ?></th>
              <th><?php echo $this->lang->line('tb_service_type'); ?></th> 
              <th><?php echo $this->lang->line('tb_business_type'); ?></th>
              <th><?php echo $this->lang->line('tb_create'); ?></th>
              <th><?php echo $this->lang->line('tb_assigned_instructor'); ?></th>
              <th><?php echo $this->lang->line('tb_business_classes'); ?></th>
              <!-- <th><?php echo $this->lang->line('tb_business_workshops'); ?></th> -->
              <th><?php echo $this->lang->line('tb_active_deactive'); ?></th>
             <th><?php echo $this->lang->line('tb_action'); ?></th>
          </thead>
        </table>
      </div>
    </div>
  </div>  


<script type="text/javascript">
        $(document).ready(function(){  
        var sort_table = [0,1,4,15,16,17];
        var postListingUrl =  BASEURL+"admin/StudioOwner/businessAjaxlist";
        var table = setTable('#userList',postListingUrl,sort_table);
    });

</script>
