<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc.">
        <meta name="author" content="Coderthemes">

        <link rel="shortcut icon" href="assets/images/favicon.ico">

        <title><?php echo SITE_NAME; ?> - Comming Soon</title>

        <!--Morris Chart CSS -->
        <link rel="stylesheet" href="assets/plugins/morris/morris.css">

        <!-- App css -->
        <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/css/icons.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/css/style.css" rel="stylesheet" type="text/css" />
       <script src="<?php echo base_url(); ?>assets/js/modernizr.min.js"></script>

    </head>

    <body>
    	<div class="account-pages"></div>
		<div class="clearfix"></div>

        <!-- HOME -->
        <div class="home-wrapper">
            <div class="container-alt">

                <div class="row m-t-40">
                    <div class="col-sm-12">
                        <div class="text-center">
                            <h2 class="text-success m-t-0 text-uppercase font-600">Coming Soon</h2>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-10 offset-lg-1">
                        <div id="count-down" class="row center-block">
                            <div class="clock-presenter days_dash col-sm-3">
                                <div class="digit"></div>
                                <div class="digit"></div>
                                <div class="digit"></div>
                                <p class='note dash_title'>Days</p>
                            </div>
                            <div class="clock-presenter hours_dash col-sm-3">
                                <div class="digit"></div>
                                <div class="digit"></div>
                                <p class='note dash_title'>Hours</p>
                            </div>
                            <div class="clock-presenter minutes_dash col-sm-3">
                                <div class="digit"></div>
                                <div class="digit"></div>
                                <p class='note dash_title'>Minutes</p>
                            </div>
                            <div class="clock-presenter seconds_dash col-sm-3">
                                <div class="digit"></div>
                                <div class="digit"></div>
                                <p class='note dash_title'>Seconds</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END HOME -->



        <!-- jQuery  -->
        <script src="<?php echo base_url(); ?>assets/js/jquery.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/popper.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/detect.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/fastclick.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/jquery.blockUI.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/waves.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/jquery.nicescroll.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/jquery.slimscroll.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/jquery.scrollTo.min.js"></script>
        <!-- Count down js -->
        <script src="<?php echo base_url(); ?>assets/plugins/count-down/jquery.lwtCountdown-1.0.js"></script>
        <!-- App js -->
        <script src="<?php echo base_url(); ?>assets/js/jquery.core.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/jquery.app.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                "use strict";
                //Set your date
                $('#count-down').countDown({
                    targetDate: {
                        'day': 1,
                        'month': 8,
                        'year': 2019,
                        'hour': 0,
                        'min': 0,
                        'sec': 0
                    },
                    omitWeeks: true
                });
            });
        </script>    
    </body>
</html>