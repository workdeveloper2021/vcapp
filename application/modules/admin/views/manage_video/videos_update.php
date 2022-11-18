<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 $category_name = $category_parent = $category_type =  $category_id = '' ;
if(!empty($category_data)){
    $category_name = $category_data[0]['category_name'];
    $category_parent = $category_data[0]['category_parent'];
    $category_type = $category_data[0]['category_type'];
    $category_id = $category_data[0]['id'];
}
?>
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div>  -->
<div class="content-wrapper">
<section class="content">
  <div class="row">
   
    <div class="col-md-8">
      <a href="<?php echo site_url();?>admin/Manage_video" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      <div class="card-box">
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            <?php 
              $attributes = array('class' => 'form-horizontal', 'id' => 'formVideoAdd', 'enctype' => 'multipart/form-data' );
              $hidden = array('is_submit' => 1);
              $parsle = 'data-parsley-validate novalidate';
              echo form_open_multipart('admin/manage_video/postVideos', $attributes, $hidden);
            ?>
                  
                   <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_category'); ?>
                    </label>
                    <div class="col-sm-12">
                      <select  name="category_parent" class="form-control" id="category_parent" required>
                          <option value="">--Select--</option>
                          <?php if (!empty($parent_data)) {
                              foreach ($parent_data as $p_data) { ?> 
                                <option value="<?php echo $p_data['id'] ?>" <?php echo ($category_parent == $p_data['id'] ) ?  'selected' : '' ; ?> ><?php echo $p_data['category_name'] ?></option>
                            <?php }
                          } ?>
                      </select>

                    </div>
                  </div>
                    

                <div class="form-group">
                  <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_sub_category'); ?>
                  </label>
                  <div class="col-sm-12">
                    <select  name="category_child" class="form-control" id="category_child" required>
                          <option value="">--Select--</option>
                    </select>

                    </div>
                  </div>



                  <div class="form-group">
                  <label for="inputName" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_video'); ?>
                  </label>
                  <div class="col-sm-12">
                    
                          <div class="overflow-auto" id="scrollingDiv" style="height: 300px;">
                            <ul class="list-group videoListToAppend">
                              
                            </ul>
                          </div>

                    </div>
                  </div>




                

                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                    <button type="submit" class="btn btn-info"><?php echo $this->lang->line('btn_save') ; ?></button>
                    </div>
                  </div>
             <?php echo form_close(); ?> 
        </div> 
      </div> 
      </div>
      <!-- /.row -->
      <script>

          $( document ).ready(function() {
            
            $("#category_parent").change(function(){
                // alert($("#category_parent").val())
                var postListingUrl =  BASEURL+"admin/manage_video/getSubCategory/"+$("#category_parent").val();
                $.get( postListingUrl, function( data ) {
                  var option = '<option value="">--Select--</option>';
                  data = JSON.parse(data)
                  data.forEach((e,i)=>{
                    option += '<option value="'+e.id+'">'+e.category_name+'</option>';
                  })
                  $("#category_child").html(option)

                });
            })

            var paging = 1

            function getVimeoVideo(page=1){
              let vimeoUrl = BASEURL+"admin/manage_video/getVimeoVideo/"+page
                $.get( vimeoUrl, function( data ) {
                  try{
                    data = JSON.parse(data)
                  }catch(e){
                    data = []
                  }
                  var htmlpost = ''
                  data.forEach((e,i)=>{

                    htmlpost += '<li class="list-group-item"><input type="checkbox" name="videos[]" value="'+[e.url,e.name,e.description,e.thumbnail,e.duration].join('/$/$')+'" style="margin-left: 10px;" class="videoChk" required/><img class="embed-responsive-item" src="'+e.thumbnail+'" style="height: 100px; width :100px; margin-left: 50px;"></img><a href="'+e.url+'" target="_blank"><b style="font-size: 30px; margin-left: 250px;"><u>PLAY VIDEO</u></b></a></li>'
                  })
                  $(".videoListToAppend").append(htmlpost)


                  $(".videoChk").change(function(){

                    if($(".videoChk").filter(":checked").length>0){
                      $(".videoChk").removeAttr("required")
                    }else{
                      $(".videoChk").attr('required', 'required');
                    }

                  })

                });              
            }

            getVimeoVideo(paging)

            $('#scrollingDiv').on('scroll', function() {
              if($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
                  paging = paging+1;
                  getVimeoVideo(paging)
              }
            })



            // $('#formVideoAdd').on('submit', function() {
            //     return $('#testForm').jqxValidator('validate');
            // });


          });
        
      </script>
    </section>
    </div>