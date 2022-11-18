
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
                <div class="col-md-6">
                </div>
                <div class="col-md-6 text-right">
                 
                  <?php 
                  if(!empty($trxdata)){?> 

                   <!-- <a class="btn btn-active waves-effect m-b-10" style="width: 15%;" href="<?php echo base_url("admin/transaction/exportUsercsv/csv");?>" title="<?php echo $this->lang->line('btn_export_csv_text'); ?>"><?php echo $this->lang->line('btn_export_csv_text'); ?></a>  -->

                  <a class="btn btn-active waves-effect m-b-10" href="<?php echo base_url("admin/transaction/exportCsvTrxns");?>" title="<?php echo $this->lang->line('btn_export_csv_text'); ?>"><?php echo $this->lang->line('btn_export_csv_text'); ?></a>

                  <?php } ?>

                  
              </div>
               
            </div>
      <div class="card-box"> 
          <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>
        <table id="userList" class="table table-bordered table-responsive1">
            <thead>
            <tr>
              <th><?php echo $this->lang->line('sr_no'); ?></th>
              <th><?php echo $this->lang->line('tb_full_name'); ?></th>    
              <th><?php echo $this->lang->line('tb_business_name'); ?></th>
              <th><?php echo $this->lang->line('tb_class_name'); ?></th>
              <th><?php echo $this->lang->line('tb_service_type'); ?></th>
              <th><?php echo $this->lang->line('tb_trxid'); ?></th>
              <th><?php echo $this->lang->line('tb_amount'); ?></th>
              <th><?php echo $this->lang->line('tb_tax'); ?></th>
              <th><?php echo $this->lang->line('tb_trx_status'); ?></th>
              <th><?php echo $this->lang->line('tb_create'); ?></th> 
              <!-- <th><?php echo $this->lang->line('tb_action'); ?></th> -->
          </thead>
        </table>
      </div>
    </div>
  </div>  

<script type="text/javascript">
        $(document).ready(function(){  
        var sort_table = [0,1,4,5,6];
        var postListingUrl =  BASEURL+"admin/transaction/trxAjaxlist";
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
