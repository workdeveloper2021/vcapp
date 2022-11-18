
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
          <?php   if(check_permission(STATUS,"instructor_list")==1){?>

         <form action="<?php echo base_url() ?>admin/trainers/block_user/<?php echo encode($this->session->userdata("bid")); ?>" class="d-inline" data-parsley-validate novalidate method="post">
             <input type="hidden" name="ids" value="" class="getIds">
             <button type="submit" class="btn btn-primary">Active</button>
         </form>
          <form action="<?php echo base_url() ?>admin/trainers/unblock_user/<?php echo encode($this->session->userdata("bid")); ?>" class="d-inline" data-parsley-validate novalidate method="post">
             <input type="hidden" name="ids" value="" class="getIds">
             <button type="submit" class="btn btn-primary">Inactive</button>
         </form>

         <?php } ?>  
                
           </div>
                <div class="col-md-6 text-right">
                 
                  <?php 
                  if(!empty($userdata)){?> 

                   <!-- <a class="btn btn-active waves-effect m-b-10" style="width: 15%;" href="<?php echo base_url("admin/users/exportUsercsv/csv");?>" title="<?php echo $this->lang->line('btn_export_csv_text'); ?>"><?php echo $this->lang->line('btn_export_csv_text'); ?></a>  -->

                  <a class="btn btn-active waves-effect m-b-10" style="width: 15%;" href="<?php echo base_url("admin/trainers/exportCsvBusinesssTrainers");?>" title="<?php echo $this->lang->line('btn_export_csv_text'); ?>"><?php echo $this->lang->line('btn_export_csv_text'); ?></a>

                  <a class="btn btn-primary waves-effect m-b-10"  href="<?php echo base_url("admin/StudioOwner/business_list");?>" title="">Back</a>
                   
                  <?php } ?>

                  
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
              <th><?php echo $this->lang->line('tb_image'); ?></th>
              <th><?php echo $this->lang->line('tb_full_name'); ?></th> 
              <th><?php echo $this->lang->line('tb_email'); ?></th>
              <th><?php echo $this->lang->line('tb_phone'); ?></th>
              <th><?php echo $this->lang->line('tb_country'); ?></th>
              <th><?php echo $this->lang->line('tb_state'); ?></th>
              <th><?php echo $this->lang->line('tb_city'); ?></th>
              <th class="address_box"><?php echo $this->lang->line('tb_address'); ?></th>
              <th><?php echo $this->lang->line('tb_gender'); ?></th>
              <th><?php echo $this->lang->line('tb_dob'); ?></th>
              <th><?php echo $this->lang->line('tb_create'); ?></th> 
              <th><?php echo $this->lang->line('tb_active_deactive'); ?></th>
              <th width="1%"><?php echo $this->lang->line('tb_action'); ?></th>
          </thead>
        </table>
      </div>
    </div>
  </div>  


<script type="text/javascript">
        $(document).ready(function(){  
        var sort_table = [0,1,2,12,13,14];

        var postListingUrl =  BASEURL+"admin/trainers/businessTrainersAjaxlist/"+<?php echo $this->session->userdata("bid"); ?>;

        var table = setTable('#userList',postListingUrl,sort_table);
    });


  // var postListingUrl =  BASEURL+"admin/users/usersAjaxlist";
  // $('#userList').dataTable({
  //   "bPaginate": true,
  //   "bLengthChange": true,
  //   "bFilter": true,
  //   "bSort": true,
  //   "bInfo": true,
  //   "bAutoWidth": false,
  //   "processing": true,
  //   "serverSide": true,
  //   "stateSave": false,
  //   "ajax": postListingUrl,
  //   "order": [[3,"asc"]],
  //   "columnDefs": [ { "targets": 0, "bSortable": true,"orderable": true, "visible": true } ],
  //         'aoColumnDefs': [{'bSortable': false,'aTargets': [0,-1,1]}],
  //     });


 

</script>
