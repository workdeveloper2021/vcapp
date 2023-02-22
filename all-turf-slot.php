<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <!-- site title -->
    <title>JUSTTURF - The Complete Guide On TURF</title>
    <!-- Stylesheets css comes here --><!-- 
    <link rel="stylesheet" href="<?//= base_url('assets_web/');?>css/bootstrap.min.css"> -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
  </head>
    <link rel="stylesheet" href="<?= base_url('assets_web/');?>css/animate.min.css">
    <link rel="stylesheet" href="<?= base_url('assets_web/');?>css/font-awesome.min.css">
    <link rel="stylesheet" href="<?= base_url('assets_web/');?>css/nivo-lightbox.css">
    <link rel="stylesheet" href="<?= base_url('assets_web/');?>css/nivo_themes/default/default.css">
    <link rel="stylesheet" href="<?= base_url('assets_web/');?>css/templatemo-style.css">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Dosis:400,700' rel='stylesheet' type='text/css'>
    <!-- 
Alpha Template
https://templatemo.com/tm-465-alpha
-->

</head>
<style>
    @import "compass/css3";

/***/
input[type="checkbox"].graphic {
    display: none;
}
 
input[type="checkbox"].graphic + label,
input[type="checkbox"].graphic + label:after {
    padding: 6px 9px;
    display: inline-block;
}
input[type="checkbox"].graphic + label {
    position: relative;
    /*padding-left: 35px;*/ 
    padding: 10px 30px;
    /* style */
    background-color: #a4c639;
    color: #444;
    border: 1px solid rgba(255,255,255,0.2);
   /* border-radius: 4px;*/
       box-shadow: 3px 3px 2px 0px #ccc;
    margin: 1px;
    cursor:pointer;
}
input[type="checkbox"].graphic + label:after {
    position: absolute;
    /* style */
    top: -22%;
    left: -3px;
    font-size: 180%;
}

input[type="checkbox"].graphic:checked + label:after {
    content: '\2714';
}

/* style */
input[type="checkbox"].graphic + label:hover:after {
  /*text-shadow: 0 0 7px rgba(255,255,255,0.1);
  color: rgba(0,0,0,0);*/
}
input[type="checkbox"].graphic:checked + label:hover:after {
  text-shadow: none;
  color: inherit;
}
input[type="checkbox"].graphic + label:hover,
input[type="checkbox"].graphic:checked + label:hover {
    /*order: 1px solid rgba(255,255,255,0.7);*/
}

input[type="checkbox"].graphic + label:active,
input[type="checkbox"].graphic:checked + label:active {
    box-shadow: 0 1px 2px rgba(0,0,0,0.05), inset 0px 0 7px 1px rgba(0,0,0,0.5), inset 0px 0 3px 0 rgba(0,0,0,0.4);
  border: none;
  margin: 2px;
}
input[type="checkbox"].graphic:checked + label {
    color: rgb(249 249 249);
    /*border: 1px solid #888;*/
    outline:none;
    /*background: #00dd00;*/
}



input[type="checkbox"].graphicd {
    display: none;
}

input[type="checkbox"].graphicd + label {
    position: relative;
    padding-left: 35px; 
    /* style */
    background-color:  #e7e7e7;
    color: #444;
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 4px;
    margin: 1px;
}

input[type="checkbox"].graphicd + label,
input[type="checkbox"].graphicd + label:after {
    padding: 6px 9px;
    display: inline-block;
}

input[type="checkbox"].graphicd + label:after {
    position: absolute;
    /* style */
    top: -22%;
    left: -3px;
    font-size: 180%;
}
   @media only screen and (max-width: 600px) {
        .col-md-12.col-sm-12.about-bottom-des.wow.bounceIn.animated {
            text-align: center;
        }
}
    @media only screen and (max-width: 600px) {
.col-md-12.col-sm-12 {
    padding-top: 5px;
}
    }
</style>
<body>

    <!-- preloader section -->
    <div class="preloader">
        <div class="sk-spinner sk-spinner-rotating-plane"></div>
    </div>

    <!-- navigation section -->
    <nav class="navbar navbar-default navbar-fixed-top sticky-navigation" role="navigation" style="opacity: 1 !important; top: 0px !important;">
        <div class="container">
            <div class="navbar-header">
                <button class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="icon icon-bar"></span>

                    <span class="icon icon-bar"></span>
                    <span class="icon icon-bar"></span>
                </button>
                <!-- <a href="#"><img src="<?= base_url('assets_web/');?>images/logo.png" class="img-responsive site-logo" alt="logo"></a> -->
                <a href="<?= base_url();?>">
                    <!-- <span style="font-size: 43px; font-weight: bold; color: #a4c639;"> -->
                        <img src="<?= base_url('assets_web/');?>logo/justurflogo.png" style="width: 20%; padding-bottom: 25px;">
                    <!-- </span> -->
                </a>
            </div>
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav navbar-right main-navigation" style="margin-top: -7%;">
                    <li><a href="#home" class="smoothScroll">HOME</a></li>
                    <li><a href="#about" class="smoothScroll">ABOUT US</a></li>
                    <!-- <li><a href="#service" class="smoothScroll">SERVICE</a></li> -->
                    <!-- <li><a href="#team" class="smoothScroll">TURFS</a></li> -->
                    <li><a href="#service" class="smoothScroll">FUNCtions</a></li>
                    <li><a href="#filterZ_" class="smoothScroll">TURFS</a></li>
                    <!-- <li><a href="#price" class="smoothScroll">PRICE</a></li> -->
                    <li><a href="#contact" class="smoothScroll">CONTACT</a></li>
                    <?php $sesdata = $this->session->userdata('user_setdata'); ?>
                    <?php if(empty($sesdata)){ ?>
                    <li><a href="<?= base_url('User_login');?>" class="smoothScroll">USER LOGIN</a></li>
                    <li><a href="<?= base_url('Welcome/turf_login');?>" class="smoothScroll">TURF LOGIN</a></li>
                    <?php }else{ ?>
                       <li><a href="<?= base_url('Welcome/all_turf');?>" class="smoothScroll">My Account</a></li>
                        <li><a href="<?= base_url('Welcome/');?>logout" class="smoothScroll">Logout</a></li> 
                    <?php } ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- end  -->
    <section id="about" class="tm-about">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-sm-12 about-bottom-des wow bounceIn">
                    <div class="col-md-6 col-sm-12">
                        <h2 class="tm-about-header"><?= $turf_details[0]['turf_name']?></h2>
                        <p><?= $turf_details[0]['description']?></p>
                    </div>
                    <div class="col-md-6 col-sm-12 about-skills">
                        <img width="" src="<?= base_url();?>/<?=$turf_details[0]['image'];?>" alt="">
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="item form-group">
                                   <h3 class="tm-about-header">Booking Date</h3>
                                        <input type="date" id="b_date" value="<?= date('Y-m-d'); ?>" class="graphic" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                 <span style="font-size:20px">Rs. <span id="stprice"><?= round($turf_details[0]['price']);?></span></span>  
                            </div>

                             <div class="col-md-2">
                                 <h3 class="tm-about-header">From</h3>
                                 <select id="f_time" name="f_time" value="" class="from-time form-control" >
                                     <option value="">HH:MM</option>
                                   

                                   
                                 </select>
                                 
                            </div>
                             <div class="col-md-2">
                                 <h3 class="tm-about-header">To</h3>
                                 <select id="t_time" name="t_time" value="" class="from-time form-control" >
                                     <option value="">HH:MM</option>
                                 </select> 
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="item form-group" id="slot_show">
                                  
                                </div>
                            </div>     
                        </div> 
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <?php if (!empty($this->session->userdata('user_setdata'))) { ?>
                        <button class="btn btn book_button" type="button" id="booked_slot" style="background: #a4c639; color: white; padding: 4px 21px; font-size: 19px;">Book</button>
                        <?php }else{
                         
                            ?>
                        <a class="btn btn book_button" href="<?= base_url('Google_login/login'); ?>" style="background: #a4c639; color: white; padding: 4px 21px; font-size: 19px;">Book</a>
                        <?php } ?>
                    </div>

                    </form>    
                </div>
            </div>
        </div>
    </section>

       <div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Enter Your Details</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  <!-- <span aria-hidden="true">&times;</span> -->
                </button>
              </div>
              <div class="modal-body">
                <form action="<?= base_url('Welcome/booking_slot') ?>"  method="post">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Your Name</label>
                    <input type="text" class="form-control" name="name" aria-describedby="emailHelp" placeholder="Enter Your Name" value="<?php if(!empty($_SESSION['user_setdata']['name'])){ echo $_SESSION['user_setdata']['name'];} ?>" required>
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">Email</label>
                    <input type="email" class="form-control" name="email" value="<?php if($_SESSION['user_setdata']['email']){ echo $_SESSION['user_setdata']['email']; }?>" placeholder="Email" required>
                    <input type="hidden" name="user_id" id="user_id" value="<?php echo $_SESSION['user_setdata']['id']; ?>">
                    <input type="hidden" name="booking_date" id="booking_date">
                    <input type="hidden" name="booking_slots" id="booking_slots">
                    <input type="hidden" name="amount" id="booking_amount">
                    <input type="hidden" name="turf_id" id="turf_id" value="<?= $turf_details[0]['id']?>">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">Mobile No.</label>
                    <input type="text" class="form-control" name="contact"  placeholder="Enter Mobile No." value="<?php if(!empty($_SESSION['user_setdata']['name'])){ if (isset($_SESSION['user_setdata']['mobile'])) { echo $_SESSION['user_setdata']['mobile']; } } ?>" required>
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword">Your Address</label>
                    <textarea type="text" name="address" class="form-control"></textarea>
                    <br>
                  </div>
              
                  <button type="submit" class="btn btn-primary">Submit</button>
                </form>
              </div>
            </div>
          </div>
        </div>




    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <!-- <img src="<?= base_url('assets_web/');?>images/logo.png" class="img-responsive" alt="footer logo"> -->
                    <span style="font-size: 43px; font-weight: bold; color: #a4c639;">JUSTTURF</span>
                    <p>Copyright &copy; 2018 Company Name

                        | Design: <a rel="nofollow" href="{{ url('/') }}" title="free templates">Template Mo</a></p>
                    <hr>
                    <ul class="social-icon">
                        <li class="wow bounceIn" data-wow-delay="0.3s"><a href="#" class="fa fa-facebook"></a></li>
                        <li class="wow bounceIn" data-wow-delay="0.6s"><a href="#" class="fa fa-twitter"></a></li>
                        <li class="wow bounceIn" data-wow-delay="0.9s"><a href="#" class="fa fa-instagram"></a></li>
                        <li class="wow bounceIn" data-wow-delay="1s"><a href="#" class="fa fa-dribbble"></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- javascript js comes here -->
  <<script src="<?= base_url('assets_web/');?>js/jquery.js"></script>

    <!-- <script src="<?//= base_url('assets_web/');?>js/bootstrap.min.js"></script>
     <script src="<?//= base_url('assets_web/');?>js/modal.js"></script> -->
     <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.5/dist/umd/popper.min.js" integrity="sha384-Xe+8cL9oJa6tN/veChSP7q+mnSPaj5Bcu9mPX5F5xIGE0DVittaqT5lorf0EI7Vk" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.min.js" integrity="sha384-kjU+l4N0Yf4ZOJErLsIcvOU2qSb74wXpOhqTvwVx3OElZRweTnQ6d31fXEoRD1Jy" crossorigin="anonymous"></script>
    <script src="<?= base_url('assets_web/');?>js/smoothscroll.js"></script>
    <script src="<?= base_url('assets_web/');?>js/jquery.nav.js"></script>
    <script src="<?= base_url('assets_web/');?>js/isotope.js"></script>
    <script src="<?= base_url('assets_web/');?>js/imagesloaded.min.js"></script>
    <script src="<?= base_url('assets_web/');?>js/nivo-lightbox.min.js"></script>
    <script src="<?= base_url('assets_web/');?>js/wow.min.js"></script>
    <script src="<?= base_url('assets_web/');?>js/custom.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>





    <script type="text/javascript">
        $(document).on('click','.graphic',function(){
            var numberOfChecked = $("input[name='slot']:checked").length;
            var amt = '<?php echo  $turf_details[0]['price'];?>';
            if (numberOfChecked >0) {

            $('#stprice').html(parseInt(numberOfChecked)*parseInt(amt));
            }else{
            $('#stprice').html(parseInt(amt));
                
            }
        })
     
        $(document).on('click','#booked_slot',function(){
            var date = $('#b_date').val();
            var amt = '<?php echo  $turf_details[0]['price'];?>';
            var numberOfChecked = $("input[name='slot']:checked").length;
            var amount = parseInt(numberOfChecked)*parseInt(amt);
            var programming = $("input[name='slot']:checked").map(function() {
                return this.value;
            }).get().join(',');

           if (date != '') {
             if (programming != '') {                
                   $('#booking_date').val(date);
                   $('#booking_slots').val(programming);
                   $('#booking_amount').val(amount);
                   $('#bookingModal').modal('show');
             }else{
               swal("warning!", "Please Select Slot!", "warning");

             }
           }else{
                swal("warning!", "Please Select Booking Date!", "warning");
           }
             
        })

        
    </script>
    <script type="text/javascript">
        $(window).on('load',function(){
            var date  = $('#b_date').val();
            var turf_id = $('#turf_id').val();
             $.ajax({
                url:'<?= base_url('Welcome/fetch_start_slots') ?>',
                type:'post',
                data: {date:date,turf_id:turf_id},
                success:function(data){
                   $('#f_time').html(data);
                   $('#t_time').html('');
                   $('#slot_show').html('');
                }     
            });
        })
        $(document).on('change','#f_time',function(){

                   $('#slot_show').html('');
                var date  = $('#b_date').val();
                var turf_id = $('#turf_id').val();
                var f_time = $('#f_time').val();
                 $.ajax({
                    url:'<?= base_url('Welcome/fetch_end_slots') ?>',
                    type:'post',
                    data: {date:date,turf_id:turf_id,f_time:f_time},
                    success:function(data){
                       $('#t_time').html(data);
                    }     
                });
        })

          $(document).on('change','#t_time',function(){
                var date  = $('#b_date').val();
                var turf_id = $('#turf_id').val();
                var f_time = $('#f_time').val();
                var t_time = $('#t_time').val();
                 $.ajax({
                    url:'<?= base_url('Welcome/fetch_slots_s') ?>',
                    type:'post',
                    data: {date:date,turf_id:turf_id,f_time:f_time,t_time:t_time},
                    success:function(data){
                        if (data != '<h3 class="tm-about-header">Available Slots</h3>') {

                           $('#slot_show').html(data);
                             var numberOfChecked = $("input[name='slot']:checked").length;
                            var amt = '<?php echo  $turf_details[0]['price'];?>';
                            if (numberOfChecked >0) {

                            $('#stprice').html(parseInt(numberOfChecked)*parseInt(amt));
                            }else{
                            $('#stprice').html(parseInt(amt));
                                
                            }
                        }else{
                            $('#slot_show').html('<h3 style="color:red">Not Available Slots!</h3>');
                        }
                    }     
                });
        });
        $('#t_time').on("change",function(){
            $('.book_button').css("background","green");
        });
    </script>
</body>

</html>