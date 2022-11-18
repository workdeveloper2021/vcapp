
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
         <form action="<?php echo base_url() ?>admin/companies/block_company" class="d-inline" data-parsley-validate novalidate method="post">
             <input type="hidden" name="ids" value="" class="getIds">
             <button type="submit" class="btn btn-primary">Active</button>
         </form>
          <form action="<?php echo base_url() ?>admin/companies/unblock_company" class="d-inline" data-parsley-validate novalidate method="post">
             <input type="hidden" name="ids" value="" class="getIds">
             <button type="submit" class="btn btn-danger">Inactive</button>
         </form>

           </div>
                <div class="col-md-6 text-right">



                   <a class="btn btn-active waves-effect m-b-10" style="width: 15%;" href="<?php echo base_url("admin/companies/addCompany");?>" title="<?php echo $this->lang->line('add_company'); ?>"><?php echo $this->lang->line('add_company'); ?></a>



              </div>

            </div>
      <div class="card-box">
          <?php if ($this->session->flashdata('updateerror') != '') {
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>
        <table id="userList" class="table table-responsive table-bordered">
            <thead>
            <tr>
              <th nowrap=""> <input type="checkbox" name="checkAll[]" id="checkall"></th>
              <th nowrap=""><?php echo $this->lang->line('sr_no'); ?></th>
              <th nowrap=""><?php echo $this->lang->line('tb_company_thumbnail'); ?></th>
              <th nowrap="" width="100"><?php echo $this->lang->line('tb_company_name'); ?></th>
              <th nowrap=""><?php echo $this->lang->line('tb_company_video'); ?></th>
              <th nowrap="" width="100"><?php echo $this->lang->line('tb_company_location'); ?></th>
              <th nowrap="" width="250"><?php echo $this->lang->line('tb_company_info'); ?></th>
              <th nowrap=""><?php echo $this->lang->line('tb_company_visit_count'); ?></th>
              <th nowrap=""><?php echo $this->lang->line('tb_showroom_portal_tapped_count'); ?></th>
              <th nowrap=""><?php echo $this->lang->line('tb_active_deactive'); ?></th>
              <th nowrap="" width="500"><?php echo $this->lang->line('tb_action'); ?></th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>


<script type="text/javascript">
        $(document).ready(function(){
        var sort_table = [0,1,2,4,7,8,10];
        var postListingUrl =  BASEURL+"admin/companies/companyAjaxlist";

                      $('#userList').dataTable({
                              "bDestroy": true,
                              "scrollX": true
                     }).fnDestroy(); 

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
