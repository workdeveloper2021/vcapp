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
              echo form_open_multipart('admin/showrooms/showroomAddSubmit/'.$cid, $attributes, $hidden);
              //echo "<pre>";print_r($userinfo);
            ?>
                <div class="row">
                  <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                      <label for="inputName" class="control-label"><?php echo $this->lang->line('tb_showroom_name'); ?>
                      </label>
                        <input type="text" name="updatename" class="form-control" id="inputName" value="" required>
                    </div>
                  </div>
                </div>
                <br><br>
                <div class="row">
                  <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                      <label for="inputName" class="control-label">Showroom Information
                      </label>
                        <textarea name="information" class="form-control" id="inputName" value="" required></textarea>
                    </div>
                  </div>
                </div>
                <br><br>


                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_showroom_thumbnail'); ?></label>
                      <input class="dropify" data-height="200" type="file" name="updateuserpic" class="form-control" id="inputName1"  accept="image/x-png,image/gif,image/jpeg" / >
                    </div>
                  </div>
                </div>
                <br><br>


                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <label nowrap="" for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_showroom_bkground_video'); ?></label>
                      <input class="dropify" data-height="200" type="file" name="updatevideo" class="form-control" id="inputName1" accept="video/mp4,video/x-m4v,video/*" / >
                    </div>
                  </div>
                </div>
                <br><br>
                

                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <label nowrap="" for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_showroom_play_video'); ?></label>
                      <input class="dropify" data-height="200" type="file" name="updateplayvideo" class="form-control" id="inputName1" accept="video/mp4,video/x-m4v,video/*" / >
                    </div>
                  </div>
                </div>

                <br><br>
                
                <div class="col-md-12">
                  <label for="inputName1" class="col-sm-3 control-label">Descripition</label>
                  <textarea class="form-control" name="description1" placeholder="Write Descripition"></textarea>
                </div>
                <br>
                <div class="row">
                      <div class="col-md-6">
                          <label for="inputName1" class="col-sm-4 control-label">
                          Retailer </label>
                          <input type="text" name="retailer11"  class="form-control" id="inputName1" multiple  / >
                      </div>

                      <div class="col-md-6">
                          <label for="inputName1" class="col-sm-4 control-label">Retailer Image</label>
                          <input type="file"  name="retailer21" class="form-control"  / >
                      </div>
                  </div><br>
                <div class="row">
                    <div class="col-md-12">
                      <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_showroom_img_360'); ?></label>
                       <input type="hidden" name="360nos[]" value="1">
                     
                      <input type="file" style="width:247px" name="update360pic[]" required class="form-control" id="inputName1"  accept="image/x-png,image/gif,image/jpeg" / >
                      <br>
                    </div>
                    <div class="col-md-12">
                       <div class="row showcod1">
                          <div class="col-sm-6">
                            <input type="hidden" name="codeno1[]" value="0" >
                              X : <input type="number" step="0.00000001"  name="xval1[]" style="width:20%">
                              Y : <input type="number" step="0.00000001"  name="yval1[]" style="width:20%">
                              Z : <input type="number" step="0.00000001"  name="zval1[]" style="width:20%">
                          </div>

                          <div class="col-sm-6">
                              <textarea style="width: 1200px;" name="coordinate_360_info1[]" class="form-control" placeholder="Info text"></textarea>
                          </div>
                          <div class="col-md-4">
                              <label for="inputName1" class="control-label">Images</label>
                              <input type="file" style="width:247px" name="image10[]"  class="form-control" id="inputName1" multiple  accept="image/x-png,image/gif,image/jpeg" / >
                          </div>

                          <div class="col-md-4">
                              <label for="inputName1" class="control-label">3D Modals</label>
                              <input type="file" style="width:247px" name="3dmodals10[]"  class="form-control" id="inputName1" multiple / >
                          </div>
                          <div class="col-md-12" style="text-align:right;">
                        <input type="button" class="btn btn-success addmore" att="1" style="height: min-content;" onclick="add_coodi(1);"  value="Add More">
                          <div>
                        
                      </div>
                    </div>
                   
                  </div>                
                </div>
                
                  
                   <button style="margin: 10px 0px;" class="btn btn-success" type="button"  onclick="education_fields();">Add More</button>
                   
                </div>

                <div class="row" id="education_fields">
          
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
var room = 1;
function education_fields() {
 
    room++;
    var objTo = document.getElementById('education_fields')
    var divtest = document.createElement("div");
  divtest.setAttribute("class", "col-md-12 form-group removeclass"+room);
  var rdiv = 'removeclass'+room;
    divtest.innerHTML = '<br><div class="col-md-12"> <label for="inputName1" class="col-sm-3 control-label">Descripition</label> <textarea class="form-control" name="description'+ room +'" placeholder="Write Descripition"></textarea> </div><br> <div class="row"> <div class="col-md-6"> <label for="inputName1" class="col-sm-4 control-label"> Retailer </label> <input type="text" name="retailer1'+ room +'" class="form-control" id="inputName1" multiple / > </div> <div class="col-md-6"> <label for="inputName1" class="col-sm-4 control-label">Retailer Image</label> <input type="file" name="retailer2'+ room +'" class="form-control" / > </div> </div><br><div class="col-md-12"><label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_showroom_img_360'); ?></label><input type="hidden" name="360nos[]" value="'+ room +'"><input type="file" style="width:247px" name="update360pic[]" required class="form-control" id="inputName1" accept="image/x-png,image/gif,image/jpeg" / ><br></div><div class="col-md-12"><div class="row showcod'+ room +'"><div class="col-sm-6"><input type="hidden" name="codeno'+ room +'[]" value="'+room+'" >  X : <input type="number" step="0.00000001" name="xval'+ room +'[]" style="width:20%"> Y : <input type="number" step="0.00000001" name="yval'+ room +'[]" style="width:20%"> Z : <input type="number" step="0.00000001" name="zval'+ room +'[]" style="width:20%"></div><div class="col-sm-6"><textarea style="width: 1200px;" name="coordinate_360_info'+ room +'[]" class="form-control" placeholder="Info text"></textarea></div> <div class="col-md-4"> <label for="inputName1" class="control-label">Images</label> <input type="file" style="width:247px" name="image'+ room +'2[]" class="form-control" id="inputName1" multiple accept="image/x-png,image/gif,image/jpeg" / > </div> <div class="col-md-4"> <label for="inputName1" class="control-label">3D Modals</label> <input type="file" style="width:247px" name="3dmodals'+ room +'2[]" class="form-control" id="inputName1" multiple / > </div><div class="col-md-12" style="text-align:right"><input type="button" class="btn btn-success" style="height: min-content;" onclick="add_coodi('+ room +')" value="Add More"><div></div></div></div></div></div></div><button style="margin: 10px 21px;" class="btn btn-danger" type="button" onclick="remove_education_fields('+ room +');"> Remove</button>';
    
    objTo.appendChild(divtest)
}
function remove_education_fields(rid) {
 $('.removeclass'+rid).remove();
}

var noc = Math.floor((Math.random() * 100) + 1);
function add_coodi(no){
  
     $(".showcod"+no).after('<div style="padding:15px 0px" class="row showcod'+ noc +' removecla'+ noc +'"><div class="col-sm-6"><input type="hidden" name="codeno'+ no +'[]" value="'+noc+'" > X : <input type="number" step="0.00000001" name="xval'+ no +'[]" style="width:20%"> Y : <input type="number" step="0.00000001" name="yval'+ no +'[]" style="width:20%"> Z : <input type="number" step="0.00000001" name="zval'+no +'[]" style="width:20%"></div><div class="col-sm-6"><textarea style="width: 1200px;" name="coordinate_360_info'+no +'[]" class="form-control" placeholder="Info text"></textarea></div><div class="col-md-4"> <label for="inputName1" class="control-label">Images</label> <input type="file" style="width:247px" name="image'+no +noc+'[]" class="form-control" id="inputName1" multiple accept="image/x-png,image/gif,image/jpeg" / > </div> <div class="col-md-4"> <label for="inputName1" class="control-label">3D Modals</label> <input type="file" style="width:247px" name="3dmodals'+ no +noc+'[]" class="form-control" id="inputName1" multiple / > </div><div class="col-sm-12" style="text-align:right"><button class="btn btn-danger" type="button" onclick="remove_education_fields2('+ noc +');"> Remove</button><div></div>'); 
      noc++;

}


function remove_education_fields2(rid) {
 $('.removecla'+rid).remove();
}
</script>

    </div>
