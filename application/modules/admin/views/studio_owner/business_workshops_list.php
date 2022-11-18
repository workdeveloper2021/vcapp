
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

                  <a class="btn btn-active waves-effect m-b-10" style="width: 15%;" href="<?php echo base_url("admin/StudioOwner/exportCsvBusinessWorkshops");?>" title="<?php echo $this->lang->line('btn_export_csv_text'); ?>"><?php echo $this->lang->line('btn_export_csv_text'); ?></a>
                  <a class="btn btn-primary waves-effect m-b-10"  href="<?php echo base_url("admin/StudioOwner/business_list");?>" title="">Back</a>                   
                  
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
              <th><?php echo $this->lang->line('workshop_name'); ?></th>
              <th><?php echo $this->lang->line('workshop_id'); ?></th>
              <th><?php echo $this->lang->line('wrkshop_category'); ?></th>
              <th><?php echo $this->lang->line('start_date'); ?></th> 
              <th><?php echo $this->lang->line('end_date'); ?></th>
              <th><?php echo $this->lang->line('from_time'); ?></th>
              <th><?php echo $this->lang->line('to_time'); ?></th>
              <th><?php echo $this->lang->line('wrkshop_duration'); ?><br><small>(In minutes)</small></th>
              <th><?php echo $this->lang->line('wrkshop_no_of_days'); ?></th>
              <th><?php echo $this->lang->line('wrkshop_capacity'); ?></th>
              <th class="address_box"><?php echo $this->lang->line('wrkshop_disc'); ?></th>
              <th><?php echo $this->lang->line('wrkshop_location'); ?></th>
              <th><?php echo $this->lang->line('status'); ?></th> 
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

        var postListingUrl =  BASEURL+"admin/classes/businessWorkshopsAjaxlist/"+<?php echo $this->session->userdata("bid"); ?>;

        // $.get(postListingUrl,function(data){
        //   console.log(data)
        // })

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
