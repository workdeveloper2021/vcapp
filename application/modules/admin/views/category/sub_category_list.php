
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

      <a href="<?php echo site_url('admin/categoryMaster');?>" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
    
     <div class="row">
        <div class="col-md-6">
          <!-- <?php   if(check_permission(STATUS,"user_list")==1){?> -->
         <form action="<?php echo base_url() ?>admin/categoryMaster/block_subcategories" class="d-inline" data-parsley-validate novalidate method="post">
             <input type="hidden" name="ids" value="" class="getIds">
             <input type="hidden" name="blocksubcat" value="<?php echo $catid; ?>">
             <button type="submit" class="btn btn-primary">Active</button>
         </form>
          <form action="<?php echo base_url() ?>admin/categoryMaster/unblock_subcategories" class="d-inline" data-parsley-validate novalidate method="post">
             <input type="hidden" name="ids" value="" class="getIds">
             <input type="hidden" name="unblocksubcat" value="<?php echo $catid; ?>">
             <button type="submit" class="btn btn-danger">Inactive</button>
         </form>
         <!-- <?php } ?> -->

           </div>
                <div class="col-md-6 text-right">

                  <?php
                  if(!empty($userdata)){?>

                   <!-- <a class="btn btn-active waves-effect m-b-10" style="width: 15%;" href="<?php echo base_url("admin/users/exportUsercsv/csv");?>" title="<?php echo $this->lang->line('btn_export_csv_text'); ?>"><?php echo $this->lang->line('btn_export_csv_text'); ?></a>  -->

                    <a class="btn btn-active waves-effect m-b-10" style="width: 15%;" href="<?php echo base_url("admin/users/exportCsvUsers");?>" title="<?php echo $this->lang->line('btn_export_csv_text'); ?>"><?php echo $this->lang->line('btn_export_csv_text'); ?></a>

                  <?php } ?>


                  <a class="btn btn-active waves-effect m-b-10" style="width: 15%;" href="<?php echo base_url("admin/categoryMaster/addSubCat/".$catid);?>" title="<?php echo $this->lang->line('add_sub_category'); ?>"><?php echo $this->lang->line('add_sub_category'); ?></a>



              </div>

            </div>

            <input type="hidden" id="cat_id" value="<?php echo $catid; ?>" />

      <div class="card-box">
          <?php if ($this->session->flashdata('updateerror') != '') {
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

          <!-- <table id="userList" class="table table-bordered table-responsive"> -->
          <table id="userList" class="table table-bordered">
            <thead>
            <tr>
              <th> <input type="checkbox" name="checkAll[]" id="checkall"></th>
              <th><?php echo $this->lang->line('sr_no'); ?></th>
              <th><?php echo $this->lang->line('tb_category_name'); ?></th>
              <th><?php echo $this->lang->line('tb_category_type'); ?></th>
              <th><?php echo $this->lang->line('price'); ?></th>
              <th><?php echo $this->lang->line('no_of_days'); ?></th>
              <th><?php echo $this->lang->line('tb_active_deactive'); ?></th>
              <th><?php echo $this->lang->line('tb_action'); ?></th>
          </thead>
        </table>
      </div>
    </div>
  </div>


<script type="text/javascript">
    $(document).ready(function(){

        var catid = $("#cat_id").val();

        var sort_table = [0,1,7];
        var postListingUrl =  BASEURL+"admin/categoryMaster/subcategoryAjaxlist/"+catid;
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
