
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
          <?php   if(check_permission(STATUS,"user_list")==1){?>
         <form action="<?php echo base_url() ?>admin/users/block_user" class="d-inline" data-parsley-validate novalidate method="post">
             <input type="hidden" name="ids" value="" class="getIds">
             <button type="submit" class="btn btn-primary">Active</button>
         </form>
          <form action="<?php echo base_url() ?>admin/users/unblock_user" class="d-inline" data-parsley-validate novalidate method="post">
             <input type="hidden" name="ids" value="" class="getIds">
             <button type="submit" class="btn btn-danger">Inactive</button>
         </form>
         <?php } ?>

           </div>
                <div class="col-md-6 text-right">

                  <a class="btn btn-active waves-effect m-b-10" style="width: 15%;" href="<?php echo base_url("admin/users/addUser");?>" title="<?php echo $this->lang->line('btn_add_user'); ?>"><?php echo $this->lang->line('btn_add_user'); ?></a>


              </div>

            </div>
      <div class="card-box">
          <?php if ($this->session->flashdata('updateerror') != '') {
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>
        <table id="userList" class="table table-bordered">
            <thead>
            <tr>
              <th> <input type="checkbox" name="checkAll[]" id="checkall"></th>
              <th><?php echo $this->lang->line('sr_no'); ?></th>
              <th><?php echo $this->lang->line('tb_image'); ?></th>
              <th><?php echo $this->lang->line('tb_full_name'); ?></th>
              <th><?php echo $this->lang->line('tb_email'); ?></th>


<!--               <th><?php echo $this->lang->line('tb_phone'); ?></th>
              <th><?php echo $this->lang->line('tb_country'); ?></th>
              <th><?php echo $this->lang->line('tb_state'); ?></th>
              <th><?php echo $this->lang->line('tb_city'); ?></th>
              <th class="address_box"><?php echo $this->lang->line('tb_address'); ?></th>
              <th><?php echo $this->lang->line('tb_gender'); ?></th>
              <th><?php echo $this->lang->line('tb_dob'); ?></th>
 -->              

              <th><?php echo $this->lang->line('tb_create'); ?></th>
              <th><?php echo $this->lang->line('tb_active_deactive'); ?></th>
              <th><?php echo $this->lang->line('tb_action'); ?></th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>


<script type="text/javascript">
        $(document).ready(function(){
        // var sort_table = [0,1,2,7];
        var postListingUrl =  BASEURL+"admin/users/usersAjaxlist";
        // var table = setTable('#userList',postListingUrl,sort_table);
 


            // var postListingUrl =  BASEURL+"admin/users/usersAjaxlist";
          $('#userList').dataTable({
            "bPaginate": true,
            "bLengthChange": true,
            "bFilter": true,
            "bSort": true,
            "bInfo": true,
            "bAutoWidth": false,
            "processing": true,
            "serverSide": true,
            "stateSave": false,
            "ajax": postListingUrl,
            "order": [[3,"asc"]],
            "columnDefs": [ { "targets": 0, "bSortable": true,"orderable": true, "visible": true } ],
                  'aoColumnDefs': [{'bSortable': false,'aTargets': [0,1,2,7]}],
              }).on('xhr.dt', function ( e, settings, json, xhr ) {

                          $(document).ready(function(){

                            $(".deleteUser").click(function(){
                                if(confirm("Are You Sure To Delete This Record?")){
                                    window.location.href = $(this).attr("id")
                                }
                            })

                          })


              } );
          });



</script>
