<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?> 
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div>  -->
  <div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">    
      <div class="card-box"> 
        <table id="mailist" class="table table-bordered">
            <thead>
            <tr>
              <th><?php echo $this->lang->line('sr_no'); ?></th>
                <th><?php echo $this->lang->line('tb_title'); ?></th>
               <th><?php echo $this->lang->line('tb_content'); ?></th>
              <th><?php echo $this->lang->line('tb_action'); ?></th>
          </thead>
            <tbody> 
              <?php 
              $i=1;
              foreach($email_data as $value) { 
                    $mail_id = $value['id'];
                ?>
                <tr>
                  <td><?php echo $i;?></td>
                  <td><?php echo $value['subject']; ?></td>
                <td><?php echo substr($value['description'],0,200).'...'; ?></td> 
                 <td> 
                    <?php 
                     if(check_permission(EDIT,"manage_email_template")==1){ 
                     $urls = base_url('admin/email_template/editMail/'.encode($value['id']));
                         echo '<a href="'.$urls.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                       }
                     ?>        
                  </td> 
                </tr> 
              <?php $i++;} ?>
            </tbody>
        </table>
      </div>
    </div>
  </div>  

<script type="text/javascript">
    $(function () {
   $("#mailist").DataTable({
                  aoColumnDefs: [
                    {
                       bSortable: false,
                       aTargets: [0,2,3]
                    }
                  ]
                  // ,
                  // order: [[1, 'asc']]
              });
  })
</script>

