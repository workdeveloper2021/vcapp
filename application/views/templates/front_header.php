<?php
 defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- <link rel="icon" href=<?php echo base_url(); ?>"front_assets/images/fav_icon.png" type="image/x-icon" /> -->
  <title> Virjual </title>
  <link href="<?php echo base_url(); ?>front_assets/css/style.css" rel="stylesheet">
</head>
<body>
   


<!-- Header Area -->
  <header class="main_header_area <?php echo isset($menu) && $menu == 'front_page' ? '' : 'new-header'; ?>">   
    <div class="searchForm"> 
      <form action="#" class="row">
        <div class="input-group">
          <span class="input-group-addon"><i class="flaticon-search"></i></span>
          <input type="search" name="search" class="form-control" placeholder="Type & Hit Enter">
          <span class="input-group-addon form_hide"><i class="flaticon-close"></i></span>
        </div>
      </form>
    </div>
    <nav class="navbar navbar-expand-lg"> 
      <a class="navbar-brand" href="<?php echo base_url(); ?>"><img src="<?php echo base_url(); ?>front_assets/images/Logo.png" ></a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target=".navbar_supported"  aria-label="Toggle navigation"> 
        <i class="fa fa-bars" aria-hidden="true"></i>
      </button>
      <div class="collapse navbar-collapse navbar_supported">
        <ul class="navbar-nav"> 
          <li><a href="<?php echo site_url('web'); ?>">Home</a></li>
          <li><a href="<?php echo site_url('web/gallery'); ?>">Gallery</a></li>
          <li><a href="<?php echo site_url('web/about'); ?>">About Us</a></li>
          <li><a href="<?php echo site_url('web/contact'); ?>">Contact</a></li>  
        </ul>   
      </div>
    </nav>   
  </header>
<!-- Header Area -->  
    
