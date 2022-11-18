
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
     <a href="<?php echo site_url();?>admin/manage_video/videos_update" class="btn btn-back"><?php echo $this->lang->line('add_video'); ?></a>  <br /><br />  
      <div class="card-box"> 
          <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>
        <table id="planlist" class="table table-bordered">
          <thead>
            <tr>
              <th nowrap><?php echo $this->lang->line('sr_no'); ?></th>
              <th nowrap><?php echo $this->lang->line('category_title'); ?></th>
              <th nowrap><?php echo $this->lang->line('sub_category_label'); ?></th>
              <th nowrap><?php echo $this->lang->line('tb_video_thumbnail'); ?></th>
              <th nowrap><?php echo $this->lang->line('tb_video_url'); ?></th>
              <th nowrap><?php echo $this->lang->line('tb_video_name'); ?></th>
              <th nowrap><?php echo $this->lang->line('tb_video_desc'); ?></th>
              <th nowrap><?php echo $this->lang->line('tb_video_duration'); ?></th>
              <th nowrap><?php echo $this->lang->line('is_vimeo_video'); ?></th>
              <th nowrap><?php echo $this->lang->line('tb_create'); ?></th> 
              <th nowrap><?php echo $this->lang->line('tb_video_status'); ?></th>
              <th><?php echo $this->lang->line('tb_action'); ?></th>
            </tr>
          </thead>
        
        </table>
      </div>
    </div>
  </div>  

  

<script type="text/javascript">



    function deleteTheRecord(ids, status, urls, table, field,idField=''){

    var idField = (idField!='') ? idField : '';
    swal({
      title: "Are you sure to delete this record ?",
      // text: "Once deleted, you will not be able to recover this imaginary file!",
      icon: "warning",
      buttons: true,
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
          var formData = {
              'ids': ids,
              'status': status,
              'table': table,
              'field': field,
              'idField': idField,
          };
          $.ajax({
              type: 'POST',
              url: urls,
              dataType: 'json',
              async: false,
              data: formData,
              success: function(data) {
                console.log(data)

          if (data.isSuccess == true) {
                  refreshPge();   
              } else if(data.isSuccess == false && data.error == 'error' && data.message != ''){
                swal(data.message);
              }else  {
                swal("Server error, please try again!");
              }
              },
          });
      } 
    });
    }




    $(document).ready(function(){  
        var sort_table = [0,3,4,9,11];
        var postListingUrl =  BASEURL+"admin/manage_video/videosAjaxlist";
        var table = setTable('#planlist',postListingUrl,sort_table);
    });

</script>
