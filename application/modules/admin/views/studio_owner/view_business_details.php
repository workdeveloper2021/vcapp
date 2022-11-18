<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
  <section class="content">
  <div class="row">
    <div class="col-md-12">  <a href="<?php echo site_url();?>admin/StudioOwner/business_list" class="btn btn-back"><?php echo $this->lang->line('back_to_list_btn'); ?></a><br><br>
    <div class="card-box">
        <div class="col-md-12">
          <div class="row text-center no-margin">
            <div class="user-img-wrap">
                  <?php  $img_path= IMAGE_URL.'/user/';
                //$imgurl= image_check(@$userinfo[0]['user_pic'],$img_path); ?>
                <div class="circle">
                  <img class="profile-pic img-responsive" src="<?php //echo $imgurl;?>" id="blah">
                </div>
            </div>
          </div>
          <div class="row">
             
            <div class="col-md-6 col-sm-6">
              <div class="col-md-12"> 
                <div class="detail-page-block">
                  <h4 class="detail-page-headng"> Full Name:</h4> 
                  <div class="detail-page-content"><?php echo isset($business_data) ? ucwords($business_data[0]['name'].' '.$business_data[0]['lastname']) : '';?></div>
                </div>
              </div>
              <div class="col-md-12"> 
                <div class="detail-page-block">
                  <h4 class="detail-page-headng"> Business Name:</h4> 
                  <div class="detail-page-content"><?php echo isset($business_data) ? ucwords($business_data[0]['business_name']) : '';?> </div>
                </div>
              </div>
              <div class="col-md-12"> 
                <div class="detail-page-block">
                  <h4 class="detail-page-headng"> Mobile:</h4> 
                  <div class="detail-page-content"><?php echo isset($business_data) ? $business_data[0]['business_phone'] : '';?> </div>
                </div>
              </div>
              <div class="col-md-12"> 
                <div class="detail-page-block">
                  <h4 class="detail-page-headng"> State:</h4> 
                  <div class="detail-page-content"> <?php echo isset($business_data) ? $business_data[0]['state'] : '';?> </div>
                </div>
              </div>
              
               <!-- <div class="col-md-12"> 
                <div class="detail-page-block">
                  <h4 class="detail-page-headng"> Categories:</h4> 
                  <div class="detail-page-content"><?php //echo isset($business_data) ? //get_business_category($business_data[0]['category']) : '';?>   </div>
                </div>
              </div> -->
              <div class="col-md-12"> 
                <div class="detail-page-block">
                  <h4 class="detail-page-headng"> Sevice Type:</h4> 
                  <div class="detail-page-content"><?php echo isset($business_data) ? get_services_type_name($business_data[0]['service_type']) : '';?>   </div>
                </div>
              </div>
              <div class="col-md-12"> 
                <div class="detail-page-block">
                  <h4 class="detail-page-headng"> Business Type:</h4> 
                  <div class="detail-page-content"><?php echo isset($business_data) ? get_business_type_name($business_data[0]['business_type']) : '';?>   </div>
                </div>
              </div>
               <div class="col-md-12"> 
                <div class="detail-page-block">
                  <h4 class="detail-page-headng">Zipcode:</h4> 
                  <div class="detail-page-content"><?php echo isset($business_data) ? $business_data[0]['zipcode'] : '';?>   </div>
                </div>
              </div>
              <div class="col-md-12"> 
                <div class="detail-page-block">
                  <h4 class="detail-page-headng"> Area (Sq.):</h4> 
                  <div class="detail-page-content"><?php echo isset($business_data) ? $business_data[0]['area'] : '';?>   </div>
                </div>
              </div>
            


            </div>

            <div class="col-md-6 col-sm-6">                  

              <div class="col-md-12"> 
                  <div class="detail-page-block">
                    <h4 class="detail-page-headng"> Email:</h4> 
                    <div class="detail-page-content">
                     <?php echo isset($business_data[0]['primary_email']) ? $business_data[0]['primary_email'] : '';?> 
                    </div>
                  </div>
              </div>

              <div class="col-md-12"> 
                <div class="detail-page-block">
                  <h4 class="detail-page-headng"> Country:</h4> 
                  <div class="detail-page-content"><?php echo isset($business_data) ? $business_data[0]['country'] : '';?> </div>
                </div>
              </div>

               <div class="col-md-12"> 
                  <div class="detail-page-block">
                    <h4 class="detail-page-headng"> City:</h4> 
                    <div class="detail-page-content"><?php echo isset($business_data) ? $business_data[0]['city'] : '';?>  </div>
                  </div>
                </div>
                <div class="col-md-12"> 
                <div class="detail-page-block">
                  <h4 class="detail-page-headng"> Number Of Floor:</h4> 
                  <div class="detail-page-content"><?php echo isset($business_data) ? $business_data[0]['number_of_floor'] : '';?>   </div>
                </div>
              </div>
              <div class="col-md-12"> 
                <div class="detail-page-block">
                  <h4 class="detail-page-headng"> Number Of Employee:</h4> 
                  <div class="detail-page-content"><?php echo isset($business_data) ? $business_data[0]['number_of_employee'] : '';?>   </div>
                </div>
              </div>
               <div class="col-md-12"> 
                <div class="detail-page-block">
                  <h4 class="detail-page-headng">Seasonal:</h4> 
                  <div class="detail-page-content"><?php echo isset($business_data) ? $business_data[0]['is_seasonal'] : '';?>   </div>
                </div>
              </div>
              <div class="col-md-12"> 
                <div class="detail-page-block">
                  <h4 class="detail-page-headng">Address Details:</h4> 
                  <div class="detail-page-content"><?php echo isset($business_data) ? $business_data[0]['address'] : '';?>   </div>
                </div>
              </div>
               <div class="col-md-12"> 
                <div class="detail-page-block">
                  <h4 class="detail-page-headng"> Created Date:</h4> 
                  <div class="detail-page-content"><?php echo isset($business_data) ? get_formated_date($business_data[0]['create_dt'], 2) : '';?> </div>
                </div>
              </div> 

            </div>
          </div>
        </div>
     
      <!-- /.col -->
    </div>
  </div>
   </div> 
  <!-- /.row -->
    
  </section>
</div>
