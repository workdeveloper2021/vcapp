<style type="text/css">

   .reset_form {
     background-color: #eee;
    padding: 30px 30px 25px 30px;
    display: inline-block;
    width: 40%;
    margin-left: 30%;
    box-shadow: 0 8px 6px -6px gray;
    }

    .reset_form h1 {
        position: relative;
    }

    .reset_form h1:before  {
        content: "";
        position: absolute;
        height: 40px;
        width: 4px;
        background: #19aee9;
        left: -30px;
        top: 0px;
     }

    .reset_form h1  {
        margin-top:0px;
    }

    .reset_form p {
        color: red;
        margin:8px 0px;
        font-size: 14px; 
    }

    .reset_form label {
      font-size: 16px;
   }

   .reset_form label,  .reset_form .form-control {
        display: block;
        width: 100%;
   }

   .reset_form .waves-effect  {

    height: 42px;
    background: #19aee9;
    color: #fff;
    text-transform: uppercase;
    width: 40%;
    display: block;
    margin: 0 auto;
    margin-top: 20px;
    height: 44px;
    font-size: 16px;
    line-height: 32px;
    letter-spacing: 1px;
    padding: 6px 12px;
    font-weight: 400;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    -ms-touch-action: manipulation;
    touch-action: manipulation;
    cursor: pointer;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-image: none;
    border: 1px solid transparent;
    border-radius: 4px;
   }

   .reset_form .form-control {
    margin-top: 10px;
    display: block;
    width: 100%;
    padding: 6px 12px;
    font-size: 14px;
    line-height: 1.42857143;
    color: #555;
    background-color: #fff;
    background-image: none;
    border: 1px solid #ccc;
    border-radius: 4px;
    -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
    -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
    -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
    transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
    height: 42px;


   }



</style>
<!-- <div class="backlayer-watermark">
    <img src="<?php echo base_url(); ?>/assets/images/watermark_bg.png" class="img-fluid">
</div>  -->
        <div id="content" class="site-content" role="main">
            <div class="container" style="margin:120px auto;">


                <div class="reset_form">
                <h1> Reset Password </h1>
<?php if($this->session->flashdata("msg")!=""){
echo "<p>".$this->session->flashdata("msg")."</p>";
} ?>
                <div class="user-form"> 

                    <div class="row">

                        <form action="<?php echo base_url(); ?>dashboard/login/passwordreset" method="post" class="col s12">

                            <div class="row">	
<?php if(!empty($reset_key)){ ?>
<input id="email" name="reset_key" type="hidden" value="<?=$reset_key?>">
<?php } ?>
                                <div class="input-field col s12">
                                    <label for="email">Enter your New Password</label>
                                    <input id="email" placeholder="Enter your New Password" required="" class="form-control" name="new_password" type="password" required="">

                                    

                                </div>

                                <div class="input-field col s12">

                                    <button class="btn waves-effect waves-light light-blue darken-4" type="submit">Submit

                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>