<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div> -->

<div class="content-wrapper">
<section class="content">
  <div class="row">
   
    <div class="col-md-12">
      <a href="<?php echo site_url();?>admin/showrooms/companyshowrooms/<?php echo $cid;?>" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      <div class="card-box">
            <!-- <h4 class="header-title m-t-0 m-b-30"><?php echo $this->lang->line('basic_info'); ?></h4> -->
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            <?php 
              $attributes = array('class' => 'form-horizontal', 'id' => '', 'enctype' => 'multipart/form-data' );
              $hidden = array('is_submit' => 1);
              $parsle = 'data-parsley-validate novalidate';
              echo form_open_multipart('admin/showrooms/imageAddSubmit/'.$cid, $attributes, $hidden);
              //echo "<pre>";print_r($userinfo);
            ?>
                <div class="row">
                  <div class="col-md-12">
                    <label for="inputName1" class="col-sm-3 control-label">Descripition</label>
                    <textarea class="form-control" name="description" placeholder="Write Descripition"></textarea>
                  </div>
                </div>
                
                
                <br>
                <div class="row">
                      <div class="col-md-6">
                          <label for="inputName1" class="col-sm-4 control-label">
                          Retailer </label>
                          <input type="text" name="retailer"  class="form-control" id="inputName1" multiple  / >
                      </div>

                      <div class="col-md-6">
                          <label for="inputName1" class="col-sm-4 control-label">Retailer Image</label>
                          <input type="file"  name="retailer2" class="form-control"  / >
                      </div>
                  </div><br>
                <div class="row">
                    <div class="col-md-12">
                      <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_showroom_img_360'); ?></label>
                       <input type="hidden" name="nos360[]" value="0">
                     
                      <input type="file" style="width:247px" name="update360pic" required class="form-control" id="inputName1"  accept="image/x-png,image/gif,image/jpeg" / >
                      <br>
                    </div>
                    <div class="col-md-12">
                       <div class="row showcod1">
                          <div class="col-sm-5">
                            <input type="hidden" name="codeno0[]" value="0" >
                              X : <input type="number" step="0.00000001"  name="xval0" style="width:20%">
                              Y : <input type="number" step="0.00000001"  name="yval0" style="width:20%">
                              Z : <input type="number" step="0.00000001"  name="zval0" style="width:20%">
                          </div>

                          <div class="col-sm-4">
                              <textarea  name="coordinate_360_info0" class="form-control" placeholder="Info text"></textarea>
                          </div>
                          <div class="col-md-3">
                              <label for="inputName1" class="control-label">Images</label>
                              <input type="file" style="width:247px" name="image0[]"  class="form-control" id="inputName" multiple  accept="image/x-png,image/gif,image/jpeg" / >
                          </div>
                          <div id="education_fields0">
                             <div  class="row">
                                 <div class="col-md-4">
                                  <label for="inputName1" class="control-label">3D Modals</label>
                                  <input type="file" style="width:247px" name="3dmodals0[]"  class="form-control" id="inputName1" multiple / >
                                  </div>

                                  <div class="col-md-4">
                                      <label for="inputName1" class="control-label">Modals Color</label>
                                      <input type="text" style="width:247px" name="modals_color0[]"  class="form-control" id="inputName1" multiple / >
                                  </div>
                                  <div class="col-md-4">
                                     <button style="margin: 30px 21px; float: left;" class="btn btn-success" type="button"  onclick="education_fields(0);">+</button>
                                   </div>
                             </div>
                             
                          </div> 
                         
                          <div class="col-md-12" style="text-align:right;">
                        <input type="button" class="btn btn-success addmore" att="1" style="height: min-content;" onclick="add_coodi(1);"  value="Add More">
                          <div>
                        
                      </div>
                    </div>
                   
                  </div>                
                </div>
                
                  
                 
                   
                </div>
                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                      <button type="submit" class="btn btn-info"><?php echo $this->lang->line('btn_add_showroom'); ?></button>
                    </div>
                  </div>

                 
             <?php echo form_close(); ?> 
        </div> 
      </div> 
      </div>
      <!-- /.row -->

    </section>

<script type="text/javascript">


var noc = 1;
function add_coodi(no){
  
     $(".showcod"+no).after('<div style="padding:15px 0px" class="row showcod'+ noc +' removecla'+ noc +'"><input type="hidden" name="nos360[]" value="'+ noc +'"><div class="col-sm-5"> X : <input type="number" step="0.00000001" name="xval'+ noc +'" style="width:20%"> Y : <input type="number" step="0.00000001" name="yval'+ noc +'" style="width:20%"> Z : <input type="number" step="0.00000001" name="zval'+ noc +'" style="width:20%"></div><div class="col-sm-4"><textarea style="width: 1200px;" name="coordinate_360_info'+ noc +'" class="form-control" placeholder="Info text"></textarea></div><div class="col-md-3"> <label for="inputName1" class="control-label">Images</label> <input type="file" style="width:247px" name="image'+ noc +'[]" class="form-control" id="inputName1" multiple accept="image/x-png,image/gif,image/jpeg" / > </div> <div id="education_fields'+ noc +'"> <div class="row"> <div class="col-md-4"> <label for="inputName1" class="control-label">3D Modals</label> <input type="file" style="width:247px" name="3dmodals'+ noc +'" class="form-control" id="inputName1"  / > </div> <div class="col-md-4"> <label for="inputName1" class="control-label">Modals Color</label> <input type="text" style="width:247px" name="modals_color'+ noc +'" class="form-control" id="inputName1"  / > </div> <div class="col-md-4"> <button style="margin: 30px 21px; float: left;" class="btn btn-success" type="button" onclick="education_fields('+ noc +');">+</button> </div> </div> </div>  <div class="col-sm-12" style="text-align:right"><button class="btn btn-danger" type="button" onclick="remove_education_fields2('+ noc +');"> Remove</button><div></div>'); 
      noc++;

}


function remove_education_fields2(rid) {
 $('.removecla'+rid).remove();
}




var room = 1;
function education_fields(no) {
 
    room++;
    var objTo = document.getElementById('education_fields'+no)
    var divtest = document.createElement("div");
  divtest.setAttribute("class", "row form-group removeclass"+room);
  var rdiv = 'removeclass'+room;
    divtest.innerHTML = ' <div class="col-md-4"> <label for="inputName1" class="control-label">3D Modals</label> <input type="file" style="width:247px" name="3dmodals'+no+'[]" class="form-control" id="inputName1"  / > </div> <div class="col-md-4"> <label for="inputName1" class="control-label">Modals Color</label> <input type="text" style="width:247px" name="modals_color'+no+'[]" class="form-control" id="inputName1" / > </div><div class="col-md-4"><button style="margin: 30px 21px;" class="btn btn-danger" type="button" onclick="remove_education_fields('+ room +');"> Remove</button></div>';
    
    objTo.appendChild(divtest)
}
function remove_education_fields(rid) {
 $('.removeclass'+rid).remove();
}
</script>

    </div>
