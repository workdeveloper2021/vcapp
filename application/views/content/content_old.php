<!DOCTYPE html>
<html lang="en" >

<head>
  <meta charset="UTF-8">
  <title> <?php echo isset($static_data[0]['title']) ?  $static_data[0]['title'] : ''; ?></title>
      <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel='stylesheet prefetch' href='http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css'>
  <!-- <link rel="stylesheet" href="<?php echo base_url().'/assets/css/style.css'?>"> -->
  
</head>
<body style="margin: 0px !important; ">
<div class="content">

 <?php echo isset($static_data[0]['discription']) ?  $static_data[0]['discription'] : ''; ?>
	
</div>
</body>

</html>
