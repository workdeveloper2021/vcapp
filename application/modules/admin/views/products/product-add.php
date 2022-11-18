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
              echo form_open_multipart('admin/products/productAddSubmit/'.$cid, $attributes, $hidden);
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

                          <option value="<?php echo $value["id"] ?>"><?php echo $value["category_name"] ?></option>

                          <?php
                        } ?>

                      </select>
                    </div>
                  </div>
                  <div class="col-md-4 col-sm-4">
                    <div class="form-group">
                      <label for="inputName" class="control-label"><?php echo $this->lang->line('product_name'); ?>
                      </label>
                        <input type="text" name="product_name" class="form-control" id="inputName" value="" required>
                    </div>
                  </div>                  
                  <div class="col-md-4 col-sm-4">
                    <div class="form-group">
                      <label for="inputName" class="control-label">Product Unit Name
                      </label>
                        <input type="text" name="product_unit" class="form-control" id="inputName" value="" required>
                    </div>
                  </div>                  
                </div>





                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label for="inputName" class="control-label"><?php echo $this->lang->line('product_details1'); ?>
                        </label>
                          <textarea style="width: 1200px;" name="product_details1" class="form-control"></textarea>
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
                          <textarea style="width: 1200px;" name="product_details2" class="form-control"></textarea>
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
                          <textarea style="width: 1200px;" name="product_details3" class="form-control"></textarea>
                      </div>
                    </div>
                  </div>
                </div>


                <input type="hidden" name="company_id" value="<?php echo $cid;?>" />



                
                <br><br>
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
                <label for="inputName1" class="control-label"><?php echo $this->lang->line('product_video'); ?></label>
                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <input type="file" name="updatevideo" class="form-control" id="inputName1" accept="video/mp4,video/x-m4v,video/*" / >
                    </div>
                  </div>
                </div>


                <!-- 
                <br><br>
                <label for="inputName1" class="control-label"><?php echo $this->lang->line('product_360_image'); ?></label>
                <div class="row">
                   <div class="form-group">
                    <div class="col-sm-12">
                      <input type="file" name="update360img" class="form-control" id="inputName1" accept="image/x-png,image/gif,image/jpeg" / >
                    </div>
                  </div>
                </div>
                <label for="inputName1" class="control-label">360 Image Co-ordinates & Details</label>
                <div id="file_div3">
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
 -->
                
                <br><!-- <br>
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

 -->
                

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
                      <button type="submit" class="btn btn-info"><?php echo $this->lang->line('btn_add_products'); ?></button>
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

        $('#color-picker').spectrum({
          type: "component"
        });

        let filterArr = [];

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


      function remove_file(ele)
      {
       $(ele).parent().remove();
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



    </script>