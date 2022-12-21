<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div> -->
<?php 
// echo '<pre>';
// print_r($data); 
?>
<div class="content-wrapper">
<section class="content">
  <div class="row"> 
   
    <div class="col-md-12">
      <a href="<?php echo site_url();?>admin/FurnitureShowrooms/companyshowrooms/<?php echo $cid;?>" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      <div class="card-box">
            <!-- <h4 class="header-title m-t-0 m-b-30"><?php echo $this->lang->line('basic_info'); ?></h4> -->
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            <?php 
              $attributes = array('class' => 'form-horizontal', 'id' => '', 'enctype' => 'multipart/form-data' );
              $hidden = array('is_submit' => 1);
              $parsle = 'data-parsley-validate novalidate';
              echo form_open_multipart('admin/FurnitureShowrooms/imageUpdateSubmit/'.$cid, $attributes, $hidden);
              //echo "<pre>";print_r($userinfo);


               $product = $this->db->select('*')->where(array('showroom_id'=>$data['showroom_id'],'is_showrooms_coordinates'=>1))->get('product')->result_array();
                          //print_r($product); 
            ?>
                <div class="row">
                  <div class="col-md-12">
                    <label for="inputName1" class="col-sm-3 control-label">Descripition</label>
                    <textarea class="form-control" name="description" placeholder="Write Descripition" ><?= $data['description']?></textarea>
                  </div>
                </div>
                
                
                <br>
                <div class="row">
                      <div class="col-md-6">
                          <label for="inputName1" class="col-sm-4 control-label">
                          Retailer </label>
                          <input type="text" name="retailer"  class="form-control" id="inputName1"  value="<?= $data['retailer1']?>" / >
                      </div>
                      <div class="col-md-6">
                          <label for="inputName1" class="col-sm-4 control-label">
                          Retailer Email</label>
                          <input type="email" name="retaileremail"  class="form-control" id="inputName1" value="<?= $data['retaileremail']?>"  / >
                      </div>

                      <div class="col-md-6">
                          <label for="inputName1" class="col-sm-4 control-label">Retailer Image</label>
                          <input type="file"  name="retailer2" class="form-control"  / >
                          <br>
                          <img src="<?= site_url()?>uploads/showroom_media/<?= $data['reatilerimage']?>" style="width: 50px; height: 50px;">
                      </div>
                      <input type="hidden" name="showroom_id" value="<?= $data['showroom_id']?>">
                  </div><br>

                  <div class="row">
                  
                     <div class="col-md-6">
                      <label for="inputName1" >Arrow Image 360 </label><br>
                       <select class="form-control" name="arrow_image360_id" >
                         <option value="">Select 360 Image</option>
                         <?php
                           if(!empty($image360)){
                            foreach ($image360 as $key => $value) {
                         ?>

                         
                         <option <?php if ($value['id']  == $data['arrow_image360_id']): ?> selected
                           
                         <?php endif ?>  value="<?=  $value['id'] ?>"><?=  $value['description'] ?></option>

                         <?php   } } ?>
                       </select>                      
                       <br><br>
                    </div>
                     <div class="col-md-6">
                      <label for="inputName1" >Image 360 Arrow Coordinate </label><br>
                        X : <input type="number" step="0.00000001" value="<?= $data['arrow_image360_xval']?>"  name="arrow_image360_xval" style="width:20%" >
                        Y : <input type="number" step="0.00000001" value="<?= $data['arrow_image360_yval']?>"  name="arrow_image360_yval" style="width:20%" >
                        Z : <input type="number" step="0.00000001"  value="<?= $data['arrow_image360_zval']?>" name="arrow_image360_zval" style="width:20%" >
                      <br><br>
                    </div>
                </div>  
                <div class="row">
                    <div class="col-md-6">
                      <label for="inputName1" class="col-sm-3 control-label"><?php echo $this->lang->line('tb_showroom_img_360'); ?></label>
                       <input type="hidden" name="nos360[]" value="0">
                     
                      <input type="file" style="width:247px" name="update360pic"  class="form-control" id="inputName1"  accept="image/x-png,image/gif,image/jpeg" / >
                      <br>
                     
                      <img src="<?= site_url()?>uploads/showroom_media/<?= $data['image360']?>" style="width:50px; height: 50px;">
                    <br><br>

                    </div>
                    <div class="col-md-6">
                       <label for="inputName1" class="col-sm-3 control-label">Thumbnail Image 360</label>
                       <input type="hidden" name="thumbnail" value="0">
                     
                      <input type="file" style="width:247px" name="thumbnail"  class="form-control" id="inputName1"  accept="image/x-png,image/gif,image/jpeg" / >
                      <br>
                          <img src="<?= site_url()?>uploads/showroom_media/<?= $data['thumbnail']?>" style="width: 50px; height: 50px;">
                      <br>
                    </div>
                    <?php 
                       $cod = $this->db->where('image360_id' ,$data['id'])->get('furiture_product')->result_array();        
                    ?>
                      <div class="col-md-12">
                       <div class="row showcod<?= count($product)+1 ?>">
                          <div class="col-sm-4">
                            <input type="hidden" name="codeno0[]" value="0" >
                              X : <input type="number" step="0.00000001"  name="xval0" value="<?= $cod[0]['xval'] ?>" style="width:20%" >
                              Y : <input type="number" step="0.00000001"  name="yval0" value="<?= $cod[0]['yval'] ?>"  style="width:20%" >
                              Z : <input type="number" step="0.00000001"  name="zval0" value="<?= $cod[0]['zval'] ?>"  style="width:20%" >
                          </div>

                          <div class="col-sm-3">
                              <textarea  name="coordinate_360_info0"  class="form-control" placeholder="Info text"><?= $cod[0]['info'] ?></textarea>
                          </div>
                          <div class="col-sm-2">
                             <input type="text" class="form-control" name="product_name0" placeholder="Product Name" value="<?= $cod[0]['product_name'] ?>">
                          </div>
                          <div class="col-md-3">
                              <label for="inputName1" class="control-label">Images</label>
                              <input type="file" style="width:247px" name="image0[]"  class="form-control" id="inputName" multiple  accept="image/x-png,image/gif,image/jpeg" / >
                              <input type="hidden" name="oldimg0" value="<?= $cod[0]['image'] ?>">
                          </div>
                          <?php 
                            $modal2 = $this->db->where('img360_id' ,$cod[0]['id'])->get('furitureshowroom_3d_models')->result_array();
                           ?>
                          
                           <?php if(!empty($modal2)){ ?>
                          <div id="education_fields0">
                             <div  class="row">
                                 <div class="col-md-4">
                                  <label for="inputName1" class="control-label">3D Modals</label>
                                  <input type="file" style="width:247px" name="3dmodals0[]"  class="form-control" id="inputName1" multiple / >
                                  <input type="hidden" name="oldmodal0[]" value="<?= $modal2[0]['modals3d']?>">
                                  <a download href="<?= site_url()?>uploads/showroom_media/<?= $modal2[0]['modals3d']?>"><?= $modal2[0]['modals3d']?></a>
                                  </div>

                                  <div class="col-md-4">
                                      <label for="inputName1" class="control-label">Modals Color</label>
                                      <input type="color" style="width:247px" name="modals_color0[]"  class="form-control" id="inputName1"  value="<?= $modal2[0]['color'] ?>" / >
                                  </div>
                                  <div class="col-md-4">
                                     <button style="margin: 30px 21px; float: left;" class="btn btn-success" type="button"  onclick="education_fields(0);">+</button>
                                   </div>
                             </div>
                           <?php }else{ ?>
                            <div id="education_fields0">
                             <div  class="row">
                                 <div class="col-md-4">
                                  <label for="inputName1" class="control-label">3D Modals</label>
                                  <input type="file" style="width:247px" name="3dmodals0[]"  class="form-control" id="inputName1" multiple / >
                                  </div>

                                  <div class="col-md-4">
                                      <label for="inputName1" class="control-label">Modals Color</label>
                                      <input type="color" style="width:247px" name="modals_color0[]"  class="form-control" id="inputName1"  value="" / >
                                  </div>
                                  <div class="col-md-4">
                                     <button style="margin: 30px 21px; float: left;" class="btn btn-success" type="button"  onclick="education_fields(0);">+</button>
                                   </div>
                             </div>
                           <?php }  ?>
                             <?php 
                              if(!empty($modal2)){
                                  for ($kyy=1; $kyy < count($modal2) ; $kyy++) { 
                               
                              ?>
                              <div class="row form-group removeclass<?= $modal2[$kyy]['id'] ?>0">
                                <div class="col-md-4">
                                  <label for="inputName1" class="control-label">3D Modals</label>
                                  <input type="file" style="width:247px" name="3dmodals0[]" class="form-control" id="inputName1">

                                  <input type="hidden" name="oldmodal0[]" value="<?= $modal2[$kyy]['modals3d']?>">
                                   <a download href="<?= site_url()?>uploads/showroom_media/<?= $modal2[$kyy]['modals3d']?>"><?= $modal2[$kyy]['modals3d']?></a>
                                </div>
                                <div class="col-md-4">
                                  <label for="inputName1" class="control-label">Modals Color</label>
                                  <input type="color" style="width:247px" name="modals_color0[]" class="form-control" id="inputName1" value="<?= $modal2[$kyy]['color'] ?>">
                                </div>
                                <div class="col-md-4">
                                  <button style="margin: 30px 21px;" class="btn btn-danger" type="button" onclick="remove_education_fields(<?= $modal2[$kyy]['id'].'0' ?>);"> Remove</button>
                                </div>
                              </div>      

                              <?php  } } ?>
                             
                          </div> 
                         
                          <div class="col-md-12" style="text-align:right;">
                        <input type="button" class="btn btn-success addmore" att="1" style="height: min-content;" onclick="add_coodi(<?= count($product)+1 ?>);"  value="Add More">
                          <div>
                        
                      </div>
                    </div>
                   
                  </div>                
                </div>                    
                    
                  <!-- First add more end -->
                </div>

              <?php 
              if(!empty($cod)){
                for ($key=1; $key < count($cod) ; $key++) { 
                  
              ?>     
                <div style="padding:15px 0px" class="row showcod<?= $key+1 ?>' removecla<?= $key+1 ?>">
                    <input type="hidden" name="nos360[]" value="<?= $key+1 ?>">
                    <div class="col-sm-4"> X : <input type="number" required step="0.00000001" value="<?= $cod[$key]['xval'] ?>" name="xval<?= $key+1 ?>" style="width:20%"> Y : <input type="number" value="<?= $cod[$key]['yval'] ?>" required step="0.00000001" name="yval<?= $key+1 ?>" style="width:20%"> Z : <input type="number" required step="0.00000001" value="<?= $cod[$key]['zval'] ?>" name="zval<?= $key+1 ?>" style="width:20%">
                    </div>
                    <div class="col-sm-3">
                      <textarea required style="width: 1200px;" name="coordinate_360_info<?= $key+1 ?>" class="form-control" placeholder="Info text"><?= $cod[$key]['info'] ?></textarea>
                    </div>
                    <div class="col-sm-2">
                      <input type="text" class="form-control" value="<?= $cod[$key]['product_name'] ?>" name="product_name<?= $key+1 ?>" placeholder="Product Name">
                    </div>
                    <div class="col-md-3">
                      <label for="inputName1" class="control-label">Images</label>
                      <input type="file" style="width:247px" name="image<?= $key+1 ?>[]" class="form-control" id="inputName1" multiple accept="image/x-png,image/gif,image/jpeg" />

                      <input type="hidden" name="oldimg<?= $key+1 ?>" value="<?= $cod[$key]['image'] ?>">
                    </div>
                    <?php 
                      $modal = $this->db->where('img360_id' ,$cod[$key]['id'])->get('showroom_3d_models')->result_array();
                   
                     ?>
                    <div id="education_fields<?= $key+1 ?>">
                      <div class="row">
                        <div class="col-md-4">
                          <label for="inputName1" class="control-label">3D Modals</label>
                          <input type="file" style="width:247px" name="3dmodals<?= $key+1 ?>[]"  class="form-control" id="inputName1" />
                           <input type="hidden" name="oldmodal<?= $key+1 ?>[]" value="<?= $modal[0]['modals3d']?>">
                                 
                           <a download href="<?= site_url()?>uploads/showroom_media/<?= $modal[0]['modals3d']?>"><?= $modal[0]['modals3d']?></a>
                        </div>
                        <div class="col-md-4">
                          <label for="inputName1" class="control-label">Modals Color</label>
                          <input type="color" style="width:247px" name="modals_color<?= $key+1 ?>[]" value="<?= $modal[0]['color'] ?>" class="form-control" id="inputName1" />
                        </div>
                        <div class="col-md-4">
                          <button style="margin: 30px 21px; float: left;" class="btn btn-success" type="button" onclick="education_fields(<?= $key+1 ?>);">+</button>
                        </div>
                      </div>
                    <?php 
                    if(!empty($modal)){
                        for ($kyy=1; $kyy < count($modal) ; $kyy++) { 
                  
                    ?>
                    <div class="row form-group removeclass<?= $modal[$kyy]['id'].$key ?>">
                      <div class="col-md-4">
                        <label for="inputName1" class="control-label">3D Modals</label>
                        <input type="file" style="width:247px" name="3dmodals<?= $key+1 ?>[]" class="form-control" id="inputName1">
                        <input type="hidden" name="oldmodal<?= $key+1 ?>[]" value="<?= $modal[$kyy]['modals3d']?>">
                           
                        <a download href="<?= site_url()?>uploads/showroom_media/<?= $modal[$kyy]['modals3d']?>"><?= $modal[$kyy]['modals3d']?></a>
                      </div>
                      <div class="col-md-4">
                        <label for="inputName1" class="control-label">Modals Color</label>
                        <input type="color" style="width:247px" name="modals_color<?= $key+1 ?>[]" class="form-control" id="inputName1" value="<?= $modal[$kyy]['color'] ?>">
                      </div>
                      <div class="col-md-4">
                        <button style="margin: 30px 21px;" class="btn btn-danger" type="button" onclick="remove_education_fields(<?= $modal[$kyy]['id'].$key ?>);"> Remove</button>
                      </div>
                    </div>      

                    <?php  } } ?>
                    </div>
                    <div class="col-sm-12" style="text-align:right">
                      <button class="btn btn-danger" type="button" onclick="remove_education_fields2(<?= $key+1 ?>);"> Remove</button>
                    </div>
                  </div>
<?php  } } ?>
                
                  
                 
                   
                </div>
                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                      <button type="submit" class="btn btn-info">Update</button>
                    </div>
                  </div>

                 
             <?php echo form_close(); ?> 
        </div> 
      </div> 
      </div>
      <!-- /.row -->

    </section>

<script type="text/javascript">


var noc = '<?= count($cod)+1 ?>';
function add_coodi(no){
  
     $(".showcod"+no).after('<div style="padding:15px 0px" class="row showcod'+ noc +' removecla'+ noc +'"><input type="hidden" name="nos360[]" value="'+ noc +'"><div class="col-sm-4"> X : <input type="number" required step="0.00000001" name="xval'+ noc +'" style="width:20%"> Y : <input type="number" required step="0.00000001" name="yval'+ noc +'" style="width:20%"> Z : <input type="number" required step="0.00000001" name="zval'+ noc +'" style="width:20%"></div><div class="col-sm-3"><textarea required style="width: 1200px;" name="coordinate_360_info'+ noc +'" class="form-control" placeholder="Info text"></textarea></div> <div class="col-sm-2"><input type="text" class="form-control" name="product_name'+ noc +'" placeholder="Product Name"></div><div class="col-md-3"> <label for="inputName1" class="control-label">Images</label> <input type="file" style="width:247px" name="image'+ noc +'[]" class="form-control" id="inputName1" multiple accept="image/x-png,image/gif,image/jpeg" / > <input type="hidden" name="oldimg'+ noc +'" ></div> <div id="education_fields'+ noc +'"> <div class="row"> <div class="col-md-4"> <label for="inputName1" class="control-label">3D Modals</label> <input type="file" style="width:247px" name="3dmodals'+ noc +'" class="form-control" id="inputName1"  / > </div> <div class="col-md-4"> <label for="inputName1" class="control-label">Modals Color</label> <input type="color" style="width:247px" name="modals_color'+ noc +'" class="form-control" id="inputName1"  / > </div> <div class="col-md-4"> <button style="margin: 30px 21px; float: left;" class="btn btn-success" type="button" onclick="education_fields('+ noc +');">+</button> </div> </div> </div>  <div class="col-sm-12" style="text-align:right"><button class="btn btn-danger" type="button" onclick="remove_education_fields2('+ noc +');"> Remove</button></div></div>'); 
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
    divtest.innerHTML = ' <div class="col-md-4"> <label for="inputName1" class="control-label">3D Modals</label> <input type="file" style="width:247px" name="3dmodals'+no+'[]" class="form-control" id="inputName1"  / > </div> <div class="col-md-4"> <label for="inputName1" class="control-label">Modals Color</label> <input type="color" style="width:247px" name="modals_color'+no+'[]" class="form-control" id="inputName1" / > <input type="hidden" name="oldmodal'+no+'[]" ></div><div class="col-md-4"><button style="margin: 30px 21px;" class="btn btn-danger" type="button" onclick="remove_education_fields('+ room +');"> Remove</button></div>';
    
    objTo.appendChild(divtest)
}
function remove_education_fields(rid) {
 $('.removeclass'+rid).remove();
}
</script>

    </div>
