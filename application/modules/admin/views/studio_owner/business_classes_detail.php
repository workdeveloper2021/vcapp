
<style>
  .d-inline {
    display: inline-block;
  }
</style>


<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?> 
  <div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">    
     <div class="row">
        <div class="col-md-6">
          <!-- <?php   if(check_permission(STATUS,"instructor_list")==1){?>

         <form action="<?php echo base_url() ?>admin/trainers/block_user/<?php echo encode($this->session->userdata("bid")); ?>" class="d-inline" data-parsley-validate novalidate method="post">
             <input type="hidden" name="ids" value="" class="getIds">
             <button type="submit" class="btn btn-primary">Active</button>
         </form>
          <form action="<?php echo base_url() ?>admin/trainers/unblock_user/<?php echo encode($this->session->userdata("bid")); ?>" class="d-inline" data-parsley-validate novalidate method="post">
             <input type="hidden" name="ids" value="" class="getIds">
             <button type="submit" class="btn btn-primary">Inactive</button>
         </form>

         <?php } ?>  
 -->                
           </div>
                <div class="col-md-6 text-right">

                  <a class="btn btn-active waves-effect m-b-10" style="width: 15%;" href="<?php echo base_url("admin/StudioOwner/exportCsvBusinessClasses");?>" title="<?php echo $this->lang->line('btn_export_csv_text'); ?>"><?php echo $this->lang->line('btn_export_csv_text'); ?></a>
                   
                  
              </div>
               
            </div>
      <div class="card-box"> 
          <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>
        <table id="userList" class="table table-bordered table-responsive">
            <thead>
            <tr>
              <!-- <th> <input type="checkbox" name="checkAll[]" id="checkall"></th> -->
              <th><?php echo $this->lang->line('sr_no'); ?></th>
              <th><?php echo $this->lang->line('class_name'); ?></th>
              <th><?php echo $this->lang->line('class_day'); ?></th>
              <th><?php echo $this->lang->line('from_time'); ?></th>
              <th><?php echo $this->lang->line('to_time'); ?></th>
              <th><?php echo $this->lang->line('class_instructor'); ?></th>
              <th><?php echo $this->lang->line('class_status'); ?></th>
              <th><?php echo $this->lang->line('class_schedule'); ?></th>
              <th><?php echo $this->lang->line('users_list'); ?></th> 
              <!-- <th><?php echo $this->lang->line('tb_active_deactive'); ?></th>
              <th width="1%"><?php echo $this->lang->line('tb_action'); ?></th> -->
          </thead>
        </table>
      </div>
    </div>
  </div>  


<script type="text/javascript">
        $(document).ready(function(){  
        var sort_table = [0];

        var postListingUrl =  BASEURL+"admin/classes/businessClassesDetailAjaxlist/"+<?php echo $this->session->userdata("cid"); ?>;

        var table = setTable('#userList',postListingUrl,sort_table);
    });


 

</script>
