
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

         <a href="<?php echo site_url();?>admin/furnitureshowrooms" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />

         <input type="hidden" name="cid" id="cid" value="<?php echo $cid; ?>" class="getIds">

         <!-- <form action="<?php echo base_url() ?>admin/showrooms/block_showroom" class="d-inline" data-parsley-validate novalidate method="post">
             <input type="hidden" name="ids" value="" class="getIds">
             <button type="submit" class="btn btn-primary">Active</button>
         </form>
          <form action="<?php echo base_url() ?>admin/showrooms/unblock_showroom" class="d-inline" data-parsley-validate novalidate method="post">
             <input type="hidden" name="ids" value="" class="getIds">
             <button type="submit" class="btn btn-danger">Inactive</button>
         </form> -->

           </div>
                <div class="col-md-6 text-right">

                   <a class="btn btn-active waves-effect m-b-10" style="width: 15%;" href="<?php echo base_url("admin/furnitureshowrooms/addShowrooms/".encode($cid));?>" title="<?php echo $this->lang->line('btn_add_showroom'); ?></a>"><?php echo $this->lang->line('btn_add_showroom'); ?></a>

              </div>

            </div>
      <div class="card-box">
          <?php if ($this->session->flashdata('updateerror') != '') {
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>
        <table id="userList" class="table table-bordered table-responsive">
            <thead>
            <tr>
              <th nowrap=""> <input type="checkbox" name="checkAll[]" id="checkall"></th>
              <th nowrap=""><?php echo $this->lang->line('sr_no'); ?></th>
              <th nowrap=""><?php echo $this->lang->line('tb_showroom_thumbnail'); ?></th>
              <th nowrap=""><?php echo $this->lang->line('tb_showroom_name'); ?></th>
              <th nowrap=""><?php echo $this->lang->line('tb_showroom_bkground_video'); ?></th>
              <th nowrap=""><?php echo $this->lang->line('tb_showroom_play_video'); ?></th>
              <th nowrap=""><?php echo $this->lang->line('tb_showroom_visit_count'); ?></th>
              <th nowrap=""><?php echo $this->lang->line('tb_showroom_video_visit_count'); ?></th>
              <th nowrap=""><?php echo $this->lang->line('tb_active_deactive'); ?></th>
              <th nowrap=""><?php echo $this->lang->line('tb_action'); ?></th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>


<script type="text/javascript">
    $(document).ready(function(){

        var cid = $("#cid").val()
        // console.log(cid)

        var sort_table = [0,1,2,4,5,6,7,8,9];
        var postListingUrl =  BASEURL+"admin/FurnitureShowrooms/showroomAjaxlist/"+cid;
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
