
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
      <div class="card-box"> 
          <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>
        <table id="planlist" class="table table-bordered">
            <thead>
            <tr>
              <th><?php echo $this->lang->line('sr_no'); ?></th>
              <th><?php echo $this->lang->line('tb_plan_name'); ?></th>
              <th><?php echo $this->lang->line('tb_plan_price'); ?></th> 
              <th><?php echo $this->lang->line('tb_plan_max_users'); ?></th> 
              <th><?php echo $this->lang->line('tb_create'); ?></th> 
              <th><?php echo $this->lang->line('tb_active_deactive'); ?></th>
              <th><?php echo $this->lang->line('tb_action'); ?></th>
          </thead>
        <!--   <tbody>
            <?php if(!empty($plandata)){
                 $i=1;
                foreach($plandata as $value){
                ?>
                <tr>
                    <td><?php echo $i;?> </td>
                    <td><?php echo $value['plan_name'];?> </td>
                    <td><?php echo $value['amount'];?> </td>
                    <td><?php echo $value['max_users'];?> </td>
                    <td><?php echo $value['create_dt'];?> </td>
                    <td><?php echo $value['status'];?> </td>
                    <td> </td>
                </tr>
               <?php  
               $i++;
              } 
             }
            ?>
          </tbody> -->
        </table>
      </div>
    </div>
  </div>  


<script type="text/javascript">
       $(document).ready(function(){  
        var sort_table = [0,4,5,6];
        var postListingUrl =  BASEURL+"admin/Subscription/planAjaxlist";
        var table = setTable('#planlist',postListingUrl,sort_table);
    });



 

</script>
