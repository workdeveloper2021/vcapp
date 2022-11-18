
<!-- <style>
  .d-inline {
    display: inline-block;
  }
</style> -->
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?> 
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div>  -->
  <div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">    
     <div class="row">
      <div class="col-md-6 ">
        <a href="<?php echo site_url();?>admin/permission" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      </div>
       <div class="col-md-6 text-right">
          <!-- <a class="btn btn-primary" href="<?php echo base_url("admin/permission/roles");?>" title="<?php echo $this->lang->line('btn_add_role'); ?>"><?php echo $this->lang->line('btn_add_role'); ?></a>   -->              
       </div>
     </div>
      <div class="card-box"> 
          <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

             <?php 
              $attributes = array('class' => 'form-horizontal', 'id' => '');
              $hidden = array('is_submit' => 1);
              echo form_open_multipart('admin/permission/permission_action', $attributes, $hidden );
            ?>
         <table  id="permissionTab" class="table table-bordered" >
            <thead>
                <tr>
                    <th><?php echo $this->lang->line('sr_no'); ?></th>           
                    <th  class="role-text" width="10%"><?php echo $this->lang->line('module'); ?></th>
                    <th  class="role-text"><?php echo $this->lang->line('function'); ?></th>
                    <th><?php echo $this->lang->line('view'); ?> &nbsp; <input type="checkbox" id="fn_view" class="role-check" value="1" /> </th>                  
                    <th><?php echo $this->lang->line('add'); ?>  &nbsp;<input type="checkbox" id="fn_add"  class="role-check" value="1" /></th>                  
                    <th><?php echo $this->lang->line('edit'); ?>  &nbsp;<input type="checkbox" id="fn_edit" class="role-check" value="1" /></th> 
                   <!--  <th><?php echo $this->lang->line('delete'); ?>  &nbsp; <input type="checkbox" id="fn_delete" class="role-check" value="1" /></th> -->
                  <th><?php echo $this->lang->line('status'); ?>  &nbsp; <input type="checkbox" id="fn_status" class="role-check" value="1" /></th>  
                 </tr>
            </thead>
            <tbody>
                 <?php if(isset($modules) && !empty($modules)){ ?>
                 <?php $module = 1; foreach($modules as $value){ ?> 
                     <tr>           
                        <td class="role-text" style="background: #ececec; font-weight: bold;"><?php echo $module; ?></td>
                        <td colspan="6" class="role-text" style="background: #ececec; font-weight: bold;"><?php echo $value['module_name']; ?> &raquo;</td>                          
                     </tr>
                    <?php $operations = get_operation_by_module($value['id']); ?> 
                    <?php if(isset($operations) && !empty($operations)){ ?>
                       <?php $operaton=0; foreach($operations as $op){ 
                        $permission = get_permission_by_operation(decode($user_id),$op['id']);
                        ?> 
                        <tr>           
                            <td class="role-text"><?php echo $module; ?>.<?php echo $operaton++; ?></td>                     
                            <td colspan="2" class="role-text" style="padding-left: 120px;">
                               <?php echo $op['operation_name']; ?>
                               <input type="hidden" name="operation[<?php echo $op['id']; ?>]" id="operatio[]" value="<?php echo $op['id']; ?>" />
                            </td>    
                            <td>
                               <?php if($op['is_view_vissible']){ ?>
                              <input type="checkbox" class="fn_view" name="is_view[<?php echo $op['id']; ?>]" value="1" <?php if(isset($permission[0]['is_view']) && $permission[0]['is_view'] == 1){ echo 'checked="checked"'; } ?> />
                               <?php } ?>
                            </td>
                              
                            <td>
                              <?php if($op['is_add_vissible']){ ?>
                                <input type="checkbox" class="fn_add"  name="is_add[<?php echo $op['id']; ?>]" value="1" <?php if(isset($permission[0]['is_add']) && $permission[0]['is_add'] == 1){ echo 'checked="checked"'; } ?> />
                                <?php } ?>
                              </td>
                               
                            <td>
                              <?php if($op['is_edit_vissible']){ ?>
                                <input type="checkbox" class="fn_edit" name="is_edit[<?php echo $op['id']; ?>]" value="1" <?php if(isset($permission[0]['is_edit']) && $permission[0]['is_edit'] == 1){ echo 'checked="checked"'; } ?> />
                                <?php } ?>
                              </td>
                              <!-- <td>
                              <?php if($op['is_delete_vissible']){ ?>
                                <input type="checkbox" class="fn_delete"  name="is_delete[<?php echo $op['id']; ?>]" value="1" <?php if(isset($permission[0]['is_delete']) && $permission[0]['is_delete'] == 1){ echo 'checked="checked"'; } ?> />
                                <?php } ?>
                              </td> -->  
                                <td>
                              <?php if($op['is_status_vissible']){ ?>
                                <input type="checkbox" class="fn_status"  name="is_status[<?php echo $op['id']; ?>]" value="1" <?php if(isset($permission[0]['is_status']) && $permission[0]['is_status'] == 1){ echo 'checked="checked"'; } ?> />
                                <?php } ?>
                              </td>  
                                                
                         </tr>                                            
                      <?php } ?>
                       
                    <?php } ?>  
                
                <?php $module++; } ?>
                <?php }else{ ?>
                <tr><td colspan="7" class="not-found"><?php echo $this->lang->line('no_data_found'); ?></td></tr>
                <?php } ?>
            </tbody> 
        </table>
        <br>
           <div class="form-group">
            <div class="col-sm-offset-3 col-sm-12">
            <input type="hidden" name="updateid" class="form-control" value="<?php echo (!empty($user_id)) ? $user_id : '';?>" />
              <button type="submit" class="btn btn-info"><?php echo $this->lang->line('btn_update_profile'); ?></button>
            </div>
          </div>
        <?php echo form_close(); ?> 
      </div>
    </div>
  </div>   

<!-- <script type="text/javascript">
        $(document).ready(function(){  
        var sort_table = [0,1,3];
        var postListingUrl =  BASEURL+"admin/permission/rolesAjaxlist";
        var table = setTable('#rolesList',postListingUrl,sort_table);
    });

</script>  -->


<!-- datatable with buttons -->
 <script type="text/javascript">
         /* Permission */
        $(document).ready(function() {
        $('#fn_view').click(function(){;
         if($(this).is(':checked')){           
             $(".fn_view").prop("checked", true);
         }else{
            $(".fn_view").prop("checked", false);
         }
        }); 
         $('#fn_add').click(function(){
           if($(this).is(':checked')){           
               $(".fn_add").prop("checked", true);
           }else{
              $(".fn_add").prop("checked", false);
           }
        });
        $('#fn_edit').click(function(){
           if($(this).is(':checked')){           
               $(".fn_edit").prop("checked", true);
           }else{
              $(".fn_edit").prop("checked", false);
           }
        });
        $('#fn_delete').click(function(){
           if($(this).is(':checked')){           
               $(".fn_delete").prop("checked", true);
           }else{
              $(".fn_delete").prop("checked", false);
           }
        });
         $('#fn_status').click(function(){
           if($(this).is(':checked')){           
               $(".fn_status").prop("checked", true);
           }else{
              $(".fn_status").prop("checked", false);
           }
        });

          
        });
 // $('#permissionTab').DataTable({
 //              dom: 'Bfrtip',
 //              iDisplayLength: 1,
 //              buttons: [
 //                  'copyHtml5',
 //                  'excelHtml5',
 //                  'csvHtml5',
 //                  'pdfHtml5',
 //                  'pageLength'
 //              ],
 //              search: true,              
 //              responsive: true
 //          });
     $(function () {
   $("#permissionTab").DataTable({
                  order: [[1, 'asc']]
              });
  })

</script>
