
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

         <a href="<?php echo site_url();?>admin/companies" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />

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

           <a class="btn btn-active waves-effect m-b-10" style="width: 15%;" href="<?php echo base_url("admin/products/addProduct/".encode($cid));?>" title="<?php echo $this->lang->line('btn_add_products'); ?></a>"><?php echo $this->lang->line('btn_add_products'); ?></a>

        </div>

    </div>


     <div class="row">
        <div class="col-md-6">
            <select id="select_category" class="form-control">
              <option value=""><?php echo $this->lang->line('option_title_all_categories'); ?></option>
              <?php foreach ($catList as $key => $value) {
                ?>

                <option value="<?php echo $value["id"] ?>"><?php echo $value["category_name"] ?></option>

                <?php
              } ?>
              <!-- <option value="">test</option> -->

            </select>
        </div>
        <div class="col-md-6 text-right">
        </div>

    </div>



      <div class="card-box">
          <?php if ($this->session->flashdata('updateerror') != '') {
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>
        <table id="userList" class="table table-responsive table-bordered">
           <thead>
            <tr>
              <th> <input type="checkbox" name="checkAll[]" id="checkall"></th>
              <th><?php echo $this->lang->line('sr_no'); ?></th>
              <th><?php echo $this->lang->line('products_category'); ?></th>
              <th><?php echo $this->lang->line('product_name'); ?></th>
              <th>Product Unit Name</th>
              <th><?php echo $this->lang->line('product_details1'); ?></th>
              <th><?php echo $this->lang->line('product_details2'); ?></th>
              <th><?php echo $this->lang->line('product_details3'); ?></th>
              <th><?php echo $this->lang->line('product_images'); ?></th>
              <th><?php echo $this->lang->line('product_3d_rendered_images'); ?></th>
              <th><?php echo $this->lang->line('product_3d_model_glb'); ?></th>
              <th><?php echo $this->lang->line('product_3d_model_usdz'); ?></th>
              <th><?php echo $this->lang->line('product_360_image'); ?></th>
              <th><?php echo $this->lang->line('product_video'); ?></th>
              <th><?php echo $this->lang->line('product_colours'); ?></th>
              <th><?php echo $this->lang->line('tb_product_visit_count'); ?></th>
              <th><?php echo $this->lang->line('tb_products_render_icon_count'); ?></th>
              <th><?php echo $this->lang->line('tb_3d_model_view_count'); ?></th>
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

        function loadReq(){

                  var cid = $("#cid").val()
                  var sort_table = [0,1,8,9,10,11,12,13,14,15,16,18,19];

                  var postListingUrl =  BASEURL+"admin/products/productAjaxlist/"+cid+"/"+$("#select_category").val();

                //   $('#userList').dataTable({
                //               "bDestroy": true,
                //               "scrollX": true
                //      }).fnDestroy(); 

                // var table = setTable('#userList',postListingUrl,sort_table);




          $('#userList').dataTable({
            "bPaginate": true,
            "bLengthChange": true,
            "bFilter": true,
            "bSort": true,
            "bDestroy": true,
            "scrollX": true,
            "bInfo": true,
            "bAutoWidth": false,
            "processing": true,
            "serverSide": true,
            "stateSave": false,
            "ajax": postListingUrl,
            "order": [[3,"asc"]],
            "columnDefs": [ { "targets": 0, "bSortable": true,"orderable": true, "visible": true } ],
                  'aoColumnDefs': [{'bSortable': false,'aTargets': sort_table}],
              }).on('xhr.dt', function ( e, settings, json, xhr ) {

                          $(document).ready(function(){

                            $(".deleteUser").click(function(){
                                if(confirm("Are You Sure To Delete This Record?")){
                                    window.location.href = $(this).attr("id")
                                }
                            })

                          })


              } ); 




        }

        loadReq();

        $("#select_category").change(function(){
            loadReq();
        })

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
