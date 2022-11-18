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
      <div class="card-box text-center">
            <h4 class="header-title m-t-0 m-b-30"><?php echo $this->lang->line('product_360_image'); ?></h4>
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

            
              <?php 
              if(!empty($product_360_image) && count($product_360_image)>0){


                foreach ($product_360_image as $key => $value) {
                  ?>

                  <div class="row" style="margin-top: 50px">
                    <div class="col-md-12 col-sm-12">
                      <div class="form-group">

                        <img src="<?php echo base_url()."uploads/company_media/".$value["media"] ?>" width="80%">   
                      </div>
                    </div>                  
                  </div>

                  <?php
                  if($key+1<count($product_360_image)){
                    ?>
                    <hr style="border: solid;">
                    <?php
                  }
                } 


              }else{

                  ?>

                  <div class="row">
                    <div class="col-md-12 col-sm-12">
                      <div class="form-group">
                        <h4><?php echo $this->lang->line('no_product_360'); ?></h4>
                      </div>
                    </div>                  
                  </div>

                  <?php

              }

              ?>




                 
            
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
                let chk = '<div class="'+ids+'"><input type="checkbox" name="color[]" class="checkbox_color" checked="" value="'+color+'"><div style="width:50px; height: 50px; background-color: '+color+';"></div><div>'
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

      function add_file()
      {
       $("#file_div").append(`
                <div class="row">
                    <div class="col-sm-6">
                      <input type='file' name='updateuserpic[]' class='form-control' id='inputName1'  accept='image/x-png,image/gif,image/jpeg' / >
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