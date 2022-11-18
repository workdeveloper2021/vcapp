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
            <h4 class="header-title m-t-0 m-b-30"><?php echo $this->lang->line('product_colours'); ?></h4>
            <?php if ($this->session->flashdata('updateerror') != '') { 
              echo '<h6 class="'.$this->session->flashdata('updateclass').'">'.$this->session->flashdata('updateerror').'</h6>';
            } ?>

              <table border="" width="100%">
                <thead>
                  <tr>
                    <td>S.No.</td>
                    <td>Colour Code</td>
                    <td>Colour Preview</td>
                  </tr>
                </thead>
                <tbody>
            
                    <?php 
                    if(!empty($product_colour_varities) && count($product_colour_varities)>0){


                      foreach ($product_colour_varities as $key => $value) {
                        ?>

                        <tr>
                          <td><?php echo $key+1; ?></td>
                          <td><?php echo $value["colour_code"]; ?></td>
                          <td><div class="text-center"><div style="width: 100%; height: 50px; background-color: <?php echo $value["colour_code"]; ?>;"></div></div></td>
                        </tr>

                        <?php
                      } 


                    }else{

                        ?>

                         <tr>
                            <td colspan="3"><?php echo $this->lang->line('no_product_colours'); ?></td>
                          </tr>
                        <?php

                    }

                    ?>


                </tbody>
              </table>


                 
            
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