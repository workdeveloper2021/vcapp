<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div> -->


<?php

 $prodName = !empty($prodInfo[0]["product_name"])?$prodInfo[0]["product_name"]:'';
 $details1 = !empty($prodListInfo[0]["details1"])?$prodListInfo[0]["details1"]:'';
 $details2 = !empty($prodListInfo[0]["details2"])?$prodListInfo[0]["details2"]:'';
 $details3 = !empty($prodListInfo[0]["details3"])?$prodListInfo[0]["details3"]:'';
 $product_unit = !empty($prodListInfo[0]["product_unit"])?$prodListInfo[0]["product_unit"]:'';




?>







<div class="content-wrapper">
<section class="content">
  <div class="row">
   
    <div class="col-md-12">
      <a href="<?php echo site_url();?>admin/products/companyProducts/<?php echo $cid;?>" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a>  <br /><br />
      <div class="card-box">
            <h4 class="header-title m-t-0 m-b-30"><?php echo $this->lang->line('basic_info'); ?></h4>
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            <?php 
              $attributes = array('class' => 'form-horizontal', 'id' => '', 'enctype' => 'multipart/form-data' );
              $hidden = array('is_submit' => 1);
              $parsle = 'data-parsley-validate novalidate';
              echo form_open_multipart('admin/products/productUpdateSubmit/'.$cid, $attributes, $hidden);
              //echo "<pre>";print_r($userinfo);
            ?>
                <div class="row">
                  <div class="col-md-4 col-sm-4">
                    <div class="form-group">
                      <label for="inputName" class="control-label"><?php echo $this->lang->line('products_category'); ?>
                      </label>

                      <select id="select_category" class="form-control" name="category_id" id="inputName" required>
                        <option value="">Select Category</option>
                        <?php foreach ($catList as $key => $value) {
                          ?>

                          <option <?php if($value["id"]==$prodListInfo[0]["category_id"]){ ?> selected <?php } ?> value="<?php echo $value["id"] ?>"><?php echo $value["category_name"] ?></option>

                          <?php
                        } ?>

                      </select>
                    </div>
                  </div>
                  <div class="col-md-4 col-sm-4">
                    <div class="form-group">
                      <label for="inputName" class="control-label"><?php echo $this->lang->line('product_name'); ?>
                      </label>
                        <input type="text" name="product_name" class="form-control" id="inputName" value="<?php echo $prodName; ?>" required>
                    </div>
                  </div>                  
                  <div class="col-md-4 col-sm-4">
                    <div class="form-group">
                      <label for="inputName" class="control-label">Product Unit Name
                      </label>
                        <input type="text" name="product_unit" class="form-control" id="inputName" value="<?php echo $product_unit; ?>" required>
                    </div>
                  </div>                  
                </div>





                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label for="inputName" class="control-label"><?php echo $this->lang->line('product_details1'); ?>
                        </label>
                          <textarea style="width: 1200px;" name="product_details1" class="form-control"><?php echo $details1; ?></textarea>
                      </div>
                    </div>
                  </div>
                </div>


                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label for="inputName" class="control-label"><?php echo $this->lang->line('product_details2'); ?>
                        </label>
                          <textarea style="width: 1200px;" name="product_details2" class="form-control"><?php echo $details2; ?></textarea>
                      </div>
                    </div>
                  </div>
                </div>


                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label for="inputName" class="control-label"><?php echo $this->lang->line('product_details3'); ?>
                        </label>
                          <textarea style="width: 1200px;" name="product_details3" class="form-control"><?php echo $details3; ?></textarea> 
                      </div>
                    </div>
                  </div>
                </div>


                <input type="hidden" name="company_id" value="<?php echo $cid;?>" />
                <input type="hidden" name="product_list_id" value="<?php echo $product_list_id;?>" />

<!-- 
                <br>



                <label for="inputName1" class="control-label"><?php echo $this->lang->line('product_images'); ?></label>
                <div id="file_div">
                 <div class="row">
                    <div class="col-sm-6">
                      <input type='file' name='updateuserpic[]' class='form-control'  accept='image/x-png,image/gif,image/jpeg' / >
                    </div><br>
                    <input type="button" class="btn btn-success" onclick="add_file();" value="ADD MORE PRODUCT IMAGES">
                  </div><br>
                </div> -->


                
                <br><br>




                    <label for="inputName1" class="control-label"><?php echo $this->lang->line('added_product_3d_rendered_images'); ?></label>
                <?php
                    if(!empty($threed_rendered_product_image)){

                      foreach ($threed_rendered_product_image as $key => $value) {
                        ?>

                         <div class="row">
                            <div class="col-sm-6">
                              <img src="<?php echo base_url()."uploads/company_media/".$value["image_name"]; ?>" width="100%">
                            </div>
                            <div class="col-sm-3" style="background-color: #F5F5F5;">
                              <div><b>Details About This Image : </b><small><?php echo $value["image_info"]; ?></small></div>
                            </div>
                            <small><input type="button" style="margin-top: 135px" class="btn btn-danger deleteOptionClass" id="3d_<?php echo $value["id"] ?>" value="DELETE THIS IMAGE"></small>
                          </div>
                          <hr>

                        <?php
                      }

                    }
                ?>
                <br>




                <label for="inputName1" class="control-label"><?php echo $this->lang->line('product_3d_rendered_images'); ?></label>
                <div id="file_div2">
                 <div class="row">

                    <div class="col-sm-4">
                      <input type='file' name='updateuserrenderpic[]' class='form-control'  accept='image/x-png,image/gif,image/jpeg' / >
                    </div>
                    <div class="col-sm-4">
                        <textarea style="width: 1200px;" name="renderPicInfo[]" class="form-control" placeholder="<?php echo $this->lang->line('placeholder_3d_rendered_img_info'); ?>"></textarea>
                    </div>
                    <br>
                  <input type="button" class="btn btn-success" style="height: min-content;" onclick="add_file2();" value="ADD MORE 3D RENDERED IMAGES">
                </div><br>
                </div>

                <br><br>






                    <label for="inputName1" class="control-label"><?php echo $this->lang->line('added_product_videos'); ?></label>
                <?php
                    if(!empty($videos)){

                      foreach ($videos as $key => $value) {
                        ?>

                         <div class="row">
                            <div>

                              <video width="300" controls>
                                <source src="<?php echo base_url()."uploads/company_media/".$value["media"] ?>">
                              </video>   

                            </div>
                            <small><input type="button" style="margin-top: 100px" class="btn btn-danger deleteOptionClass" id="media_<?php echo $value["id"] ?>" value="DELETE THIS VIDEO"></small>
                          </div>

                        <?php
                      }

                    }
                ?>
                <br>



                <label for="inputName1" class="control-label"><?php echo $this->lang->line('product_video'); ?></label>
                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <input type="file" name="updatevideo" class="form-control" id="inputName1" accept="video/mp4,video/x-m4v,video/*" / >
                    </div>
                  </div>
                </div>


                  <br><br><br>
                
                <!-- <label for="inputName1" class="control-label"><?php echo $this->lang->line('update_product_360_image'); ?></label>
                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <input type="file" name="update360img" class="form-control" id="inputName1" accept="image/x-png,image/gif,image/jpeg" / >
                    </div>
                  </div>
                </div>

                <label for="inputName1" class="control-label">360 Image Co-ordinates & Details</label>
                <div id="file_div3">

                  <?php
                      if(!empty($img_360_coordinates)){

                        foreach ($img_360_coordinates as $key => $value) {

                          ?>


                             <div class="row">
                                <div class="col-sm-4">
                                    X : <input type="number" value="<?php echo $value['xval'] ?>" name="xval[]" style="width:20%">
                                    Y : <input type="number" value="<?php echo $value['yval'] ?>" name="yval[]" style="width:20%">
                                    Z : <input type="number" value="<?php echo $value['zval'] ?>" name="zval[]" style="width:20%">
                                </div>

                                <div class="col-sm-4">
                                    <textarea style="width: 1200px;" name="coordinate_360_info[]" class="form-control" placeholder="Info text"><?php echo $value['info'] ?></textarea>
                                </div>
                                <input type='button' class='btn btn-danger' style="height: min-content;"  value='REMOVE' onclick='removeCoordinate("<?php echo encode($value['id']) ?>");'>
                            </div>


                          <?php

                        }

                      }
                  ?>



                   <div class="row">
                      <div class="col-sm-4">
                          X : <input type="number" name="xval[]" style="width:20%">
                          Y : <input type="number" name="yval[]" style="width:20%">
                          Z : <input type="number" name="zval[]" style="width:20%">
                      </div>

                      <div class="col-sm-4">
                          <textarea style="width: 1200px;" name="coordinate_360_info[]" class="form-control" placeholder="Info text"></textarea>
                      </div>
                      <br>
                    <input type="button" class="btn btn-success" style="height: min-content;" onclick="add_file3();" value="Add More Co-ordinates">
                  </div><br>

                </div>




                <br><br>
 -->



<!-- 

              <label for="inputName1" class="control-label"><?php echo $this->lang->line('product_3d_model_glb'); ?></label>
                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <input type="file" name="glb3dmodel" class="form-control" id="inputName1"/ >
                    </div>
                  </div>
                </div>

                
                <br><br>
                <label for="inputName1" class="control-label"><?php echo $this->lang->line('product_3d_model_usdz'); ?></label>
                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <input type="file" name="usdz3dmodel" class="form-control" id="inputName1"/ >
                    </div>
                  </div>
                </div>


                

                <br>
 -->




                    <label for="inputName1" class="control-label"><?php echo $this->lang->line('added_product_colours'); ?></label>
                <?php
                    if(!empty($colours)){

                      foreach ($colours as $key => $value) {
                        ?>

                         <div class="row">
                            <div style="width:200px; background-color: <?php echo $value["colour_code"] ?>;" class="text-center">
                              <div style="margin-top:60px;">Colour Code : <?php echo $value["colour_code"] ?></div>
                              <input type="hidden" class="alreadyExistColor" value="<?php echo $value["colour_code"] ?>">
                            </div>
                            <small>

                              &nbsp;&nbsp;

                              <?php 
                                    if($value["status"]=="Active"){
                                      ?>
                                          <input type="button" style="margin-top: 100px" class="btn btn-success changeStatusClass" id="deactive_<?php echo $value["id"] ?>" value="ACTIVE">
                                      <?php
                                    }else{
                                      ?>
                                          <input type="button" style="margin-top: 100px" class="btn btn-danger changeStatusClass" id="active_<?php echo $value["id"] ?>" value="DEACTIVE">
                                      <?php
                                    }  
                              ?>

                              <input type="button" style="margin-top: 100px" class="btn btn-danger deleteOptionClass" id="colour_<?php echo $value["id"] ?>" value="DELETE THIS COLOUR">



                            </small>
                          </div>





                        <?php 
                              if($value["status"]=="Active"){
                                ?>

                                <!-- ///////////// ONLY IF COLOUR IS ACTIVE //////////////// -->


                                <br>

                                <label for="inputName1" class="control-label"><?php echo $this->lang->line('added_product_images'); ?></label>
                            <?php
                                if(!empty($images)){

                                  foreach ($images as $key2 => $value2) {

                                    if($value2['product_colour_varity_id']==$value['id']){

                                      ?>

                                       <div class="row">
                                          <div>
                                            <img src="<?php echo base_url()."uploads/company_media/".$value2["media"]; ?>" width="300">
                                          </div>
                                          <small><input type="button" style="margin-top: 135px" class="btn btn-danger deleteOptionClass" id="media_<?php echo $value2["id"] ?>" value="DELETE THIS IMAGE"></small>
                                        </div>

                                      <?php
                                    }
                                  }

                                }
                            ?>

                            <br>
                              <input type="hidden" name="existsColor[]" value="<?php echo $value["colour_code"] ?>">
                              <?php //echo "string"; 
                               print_r($value['colour_code']); 
                               if(is_array($value['colour_code'])){ ?>
                                <div id="<?php echo explode('#',$value['colour_code'])[1]."_"; ?>">
                              <?php }
                              else{ ?>
                                <div id="<?php echo $value['colour_code']; ?>">
                              <?php }
                              ?>
                              <!-- <div id="<?php echo explode('#',$value['colour_code'])[1]."_"; ?>"> -->
                                <input type="button" class="btn btn-success" onclick="add_file('<?php echo $value['colour_code']; ?>');" value="ADD MORE PRODUCT IMAGES">
                              <br>
                              </div>
                              <br>







                            <br>

                                <label for="inputName1" class="control-label"><?php echo $this->lang->line('added_product_3d_model_usdz'); ?></label>
                            <?php
                                if(!empty($usdz)){

                                  foreach ($usdz as $key2 => $value2) {

                                    if($value2['product_colour_varity_id']==$value['id']){

                                      ?>

                                       <div class="row">
                                          <div>
                                            <a href="<?php echo base_url()."uploads/company_media/".$value2["media"]; ?>" target="_blank"><?php echo $value2["media"]; ?></a>
                                          </div>
                                          &nbsp; <small><input type="button" class="btn btn-danger deleteOptionClass" id="media_<?php echo $value2["id"] ?>" value="DELETE THIS IOS USDZ DOC"></small>
                                        </div>

                                      <?php
                                    }
                                  }

                                }else{
                                  ?>


                                      <br>
                                      <label for="inputName1" class="control-label"><?php echo $this->lang->line('product_3d_model_usdz'); ?></label>
                                      <div class="row">
                                         <div class="form-group">
                                          <div class="col-sm-12">
                                            <input type="file" name='<?php echo $value['colour_code']; ?>_usdz' class="form-control" id="inputName1"/ >
                                          </div>
                                        </div>
                                      </div>
                                      <br>



                                  <?php
                                }
                            ?>




                            <br>

                                <label for="inputName1" class="control-label"><?php echo $this->lang->line('added_product_3d_model_glb'); ?></label>
                            <?php
                                if(!empty($glb)){

                                  foreach ($glb as $key2 => $value2) {

                                    if($value2['product_colour_varity_id']==$value['id']){

                                      ?>

                                       <div class="row">
                                          <div>
                                            <a href="<?php echo base_url()."uploads/company_media/".$value2["media"]; ?>" target="_blank"><?php echo $value2["media"]; ?></a>
                                          </div>
                                          &nbsp; <small><input type="button" class="btn btn-danger deleteOptionClass" id="media_<?php echo $value2["id"] ?>" value="DELETE THIS ANDROID GLB DOC"></small>
                                        </div>

                                      <?php
                                    }
                                  }

                                }else{
                                  ?>


                                        <br>
                                        <label for="inputName1" class="control-label"><?php echo $this->lang->line('product_3d_model_glb'); ?></label>
                                        <div class="row">
                                           <div class="form-group">
                                            <div class="col-sm-12">
                                              <input type="file" name='<?php echo $value['colour_code']; ?>_glb' class="form-control" id="inputName1"/ >
                                            </div>
                                          </div>
                                        </div>
                                        <br>


                            
                                  <?php
                                }
                            ?>

                                <!-- ///////////// ONLY IF COLOUR IS ACTIVE //////////////// -->

                                <?php
                              }  
                        ?>


                            
                            <br><br><br><br>







                        <?php
                      }

                    }
                ?>
                <br>




                

                <br>
                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label for="inputName" class="control-label"><?php echo $this->lang->line('product_colours_pick'); ?>
                        </label>
                        <br>
                        <input id="color-picker" value='#276cb8' /> 
                        <a href="javascript:void(0)" id="pick_btn" class="btn btn-success" style="margin-bottom: 20px;"><?php echo $this->lang->line('btn_add_colour'); ?></a>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <div class="form-group" id="pushcolor">
                        
                      </div>
                    </div>
                  </div>
                </div>




                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-12">
                      <button type="submit" class="btn btn-info"><?php echo $this->lang->line('btn_update_products'); ?></button>
                    </div>
                  </div>

                 
             <?php echo form_close(); ?> 
        </div> 
      </div> 
      </div>
      <!-- /.row -->

    </section>
    </div>
    <script type="text/javascript">
      
      

      $( document ).ready(function() {


        $(".deleteOptionClass").click(function(){
          // let theId =  $(this).attr("id");
          // alert(theId)

            if(window.confirm("Are You Sure To Delete This?")){



              let theId =  $(this).attr("id").split("_")
                console.log(theId)
                // console.log("<?php echo base_url()."admin/products/removeData/"; ?>"+"/"theId[0]+"/"theId[1])

              $.get( "<?php echo base_url()."admin/products/removeData/"; ?>"+"/"+theId[0]+"/"+theId[1], function( data ) {
                // $( ".result" ).html( data );
                console.log(data)
                location.reload();
                // alert(data);
              });

            }

        })


        $(".changeStatusClass").click(function(){
          // let theId =  $(this).attr("id");
          // alert(theId)

            if(true){



              let theId =  $(this).attr("id").split("_")
                console.log(theId)

              $.get( "<?php echo base_url()."admin/products/colourStatus/"; ?>"+"/"+theId[0]+"/"+theId[1], function( data ) {
                // $( ".result" ).html( data );
                console.log(data)
                location.reload();
                // alert(data);
              });

            }

        })


        $('#color-picker').spectrum({
          type: "component"
        });



        let filterArr = [];


        let x = $(".alreadyExistColor")
        for(var i=0; i<x.length; i++){
          filterArr.push($(x[i]).val())
        }



        $("#pick_btn").click(function(){

          let color = $("#color-picker").val()

          if(!filterArr.includes(color)){

              filterArr.push(color)

                let ids = color.split("#")[1]
                let chk = `<div class="`+ids+`">
                            <input type="checkbox" name="color[]" class="checkbox_color" checked="" value="`+color+`">
                            <div style="width:50px; height: 50px; background-color: `+color+`;"></div><br>
                            <div>


                              <label for="inputName1" class="control-label"><?php echo $this->lang->line('product_images'); ?></label>
                              <div id="`+color.split('#')[1]+`_">
                               <div class="row">
                                  <div class="col-sm-6">
                                    <input type='file' name='`+color+`[]' class='form-control'  accept='image/x-png,image/gif,image/jpeg' required / >
                                </div><br>
                                <input type="button" class="btn btn-success" onclick="add_file('`+color+`');" value="ADD MORE PRODUCT IMAGES">
                              </div><br>
                              </div>









                              <br>
                              <label for="inputName1" class="control-label"><?php echo $this->lang->line('product_3d_model_glb'); ?></label>
                              <div class="row">
                                 <div class="form-group">
                                  <div class="col-sm-12">
                                    <input type="file" name='`+color+`_glb' class="form-control" id="inputName1"/ >
                                  </div>
                                </div>
                              </div>

                              
                              <br>
                              <label for="inputName1" class="control-label"><?php echo $this->lang->line('product_3d_model_usdz'); ?></label>
                              <div class="row">
                                 <div class="form-group">
                                  <div class="col-sm-12">
                                    <input type="file" name='`+color+`_usdz' class="form-control" id="inputName1"/ >
                                  </div>
                                </div>
                              </div>







                              

                            </div>
                          <div>`
                $("#pushcolor").append(chk)

                $( document ).ready(function() {

                    $(".checkbox_color").change(function(){

                      var id = $(this).attr("value").split("#")[1]
                      if(!$(this).is(":checked")){

                        var index = filterArr.indexOf($(this).attr("value"));
                        if (index > -1) {
                          filterArr.splice(index, 1);
                        }

                        $("."+id).remove()
                      }
                    })

                })


          }


        })


      });

      function add_file(color)
      {
       $(color+"_").append(`
                <div class="row">
                    <div class="col-sm-6">
                      <input type='file' name='`+color+`[]' class='form-control' id='inputName1'  accept='image/x-png,image/gif,image/jpeg' required / >
                  </div><input type='button' class='btn btn-danger'  value='REMOVE' onclick=remove_file(this);>
                </div>

                `);
         $( document ).ready(function() {
            console.log( "ready!" );
        });

      }


      function add_file2()
      {
       $("#file_div2").append(`
                <div class="row">
                    <div class="col-sm-4">
                      <input type='file' name='updateuserrenderpic[]' class='form-control' id='inputName1'  accept='image/x-png,image/gif,image/jpeg' / >
                    </div>
                    <div class="col-sm-4">
                        <textarea style="width: 1200px;" name="renderPicInfo[]" class="form-control" placeholder="<?php echo $this->lang->line('placeholder_3d_rendered_img_info'); ?>"></textarea>
                    </div>

                  <input type='button' class='btn btn-danger' style="height: min-content;"  value='REMOVE' onclick=remove_file(this);>
                </div>

                `);
         $( document ).ready(function() {
            console.log( "ready!" );
        });

      }




      function add_file3()
      {
       $("#file_div3").append(`
                <div class="row">
                      <div class="col-sm-4">
                          X : <input type="number" name="xval[]" style="width:20%">
                          Y : <input type="number" name="yval[]" style="width:20%">
                          Z : <input type="number" name="zval[]" style="width:20%">
                      </div>

                      <div class="col-sm-4">
                          <textarea style="width: 1200px;" name="coordinate_360_info[]" class="form-control" placeholder="Info text"></textarea>
                      </div>
                  <input type='button' class='btn btn-danger' style="height: min-content;"  value='REMOVE' onclick=remove_file(this);>
                  </div>

                `);
         $( document ).ready(function() {
            console.log( "ready!" );
        });

      }






      function removeCoordinate(id)
      {

              $.get( "<?php echo base_url()."admin/products/deleteCordinates/"; ?>"+"/"+id, function( data ) {
                // $( ".result" ).html( data );
                console.log(data)
                location.reload();
                // alert(data);
              });

      }









      function add_file_existing_color(color)
      {
       $(color+"_").append(`
                <div class="row">
                    <div class="col-sm-6">
                      <input type='file' name='`+color+`[]' class='form-control' id='inputName1'  accept='image/x-png,image/gif,image/jpeg' required / >
                  </div><input type='button' class='btn btn-danger'  value='REMOVE' onclick=remove_file(this);>
                </div>

                `);
         $( document ).ready(function() {
            console.log( "ready!" );
        });

      }



      
      function remove_file(ele)
      {
       $(ele).parent().remove();
      }

    </script>