<!DOCTYPE html>
<html lang="en">
  <?php 
    $base_path = base_url();
    $base_path = str_replace($base_path, '/superadmin', '/');
  ?>
  <head>
      <meta charset="utf-8">
      <title>Signal Health Group Inc</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta name="description" content="">
      <meta name="author" content="">
      <link rel="shortcut icon" type="image/x-icon" href="<?=$base_path;?>assets/img/favicon.png">
      <style>
          *{
              margin: 0;
              padding: 0;
          }
          .messagesection{
              min-height: 100vh;
              width: 100%;
              background: #e4e7ec;
              display: -ms-flexbox;
              display: flex;
              justify-content: center;
              align-items: center;
          }
          .midlewrp h4{
              color: green;
              font-weight: 600;
              font-size: 24px;
              text-align: center;
              margin-bottom: 15px;
          }
          .btntype_acchor{
              margin: auto;
              height: 40px;
              border-radius: 10px;
              color: #fff;
              background: linear-gradient(90deg, #32C3F1 0%, #2AACE3 50%, #1B96D3 99%);
              font-size: 16px;
              text-transform: uppercase;
              padding: 10px;
              margin-top: 20px;
              display: flex;
              justify-content: center;
              align-items: center;
              text-decoration: none;
              max-width: 150px;
          }
      </style>

  </head>

  <body>

      <div class="messagesection">
          <div class="midlewrp">
              <!-- <img src="<?=$base_path;?>assets/img/logo_colored.png" style="display:block;margin:auto;max-width:100%;margin-bottom:50px;width:60%;margin-left: 40%;"> -->
              <h4>
                <?php if($email_status=='1') { ?>
                  <?php echo isset($email_verified) ?  $email_verified : 'Not Verify Please Try again Later'; ?>
                <?php } else { ?>
                  <?php echo isset($email_verified) ?  $email_verified : 'Not Verify Please Try again Later'; ?>
                <?php  } ?>  
              </h4>
              <!-- <a href="<?php echo $base_path;?>" class="btntype_acchor">Go To Home</a> -->
          </div>
      </div>

  </body>

</html>