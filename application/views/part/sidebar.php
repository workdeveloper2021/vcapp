<?php 
    $loggedUser=getLoggedUserData();
?>
<div class="sidebar-inner slimscrollleft">

    <!-- User -->
    <div class="user-box">
        <div class="user-img">
            <img src="<?php echo base_url('uploads/user/'.$loggedUser->user_pic); ?>" alt="user-img" title="Mat Helme" class="rounded-circle img-thumbnail img-responsive">
            <div class="user-status offline"><i class="mdi mdi-adjust"></i></div>
        </div>
        <h5><a href="#"><?php echo !empty($loggedUser->full_name) ? $loggedUser->full_name : ""; ?></a> </h5>
        <ul class="list-inline">
            <li class="list-inline-item">
                <a href="<?php echo base_url('admin/profile'); ?>" >
                    <i class="mdi mdi-settings"></i>
                </a>
            </li>

            <li class="list-inline-item">
                <a href="<?php echo base_url('admin/dashboard/logout'); ?>" class="text-custom">
                    <i class="mdi mdi-power"></i>
                </a>
            </li>
        </ul>
    </div>
    <!-- End User -->

    <!--- Sidemenu -->
    <div id="sidebar-menu">
        <ul>
        	<li>
                <a href="<?php echo base_url('admin/dashboard'); ?>" class="waves-effect"><i class="mdi mdi-view-dashboard"></i> <span> Dashboard </span> </a>
            </li>


            <li class="has_sub">
                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-invert-colors"></i> <span> Users Management </span> <span class="menu-arrow"></span></a>
                <ul class="list-unstyled">
                    <li><a href="<?php echo base_url('admin/users'); ?>">Enthusiast</a></li>
                    <li><a href="<?php echo base_url('admin/users/serviceProvider'); ?>">Service</a></li>
                </ul>
            </li>
           <!--   <li>
                <a href="<?php echo base_url('admin/users/businessList'); ?>" class="waves-effect"><i class="mdi mdi-google-pages"></i> <span> Business Management </span> </a>
            </li>  -->
             <li>
                <a href="<?php echo base_url('admin/article'); ?>" class="waves-effect"><i class="mdi mdi-google-pages"></i> <span> Article </span> </a>
            </li>
             <!--  <li>
                <a href="<?php echo base_url('admin/users/service'); ?>" class="waves-effect"><i class="mdi mdi-google-pages"></i> <span>Service </span> </a>
            </li>   -->  
             <li class="has_sub">
                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-invert-colors"></i> <span> Advertisement </span> <span class="menu-arrow"></span></a>
                <ul class="list-unstyled">
                    <li><a href="<?php echo base_url('admin/banner'); ?>">Advertisement Image </a></li>
                     <li><a href="<?php echo base_url('admin/banner/advertisementVideoList'); ?>">Advertisement video </a></li>
                    <!--<li><a href="<?php echo base_url('admin/banner/add'); ?>">Add</a></li>
              -->  </ul>
            </li>
             <li>
                <a href="<?php echo base_url('admin/users/ratingList'); ?>" class="waves-effect"><i class="mdi mdi-google-pages"></i> <span> Rating List </span> </a>
            </li> 
            
            <li>
                <a href="<?php echo base_url('admin/settings'); ?>" class="waves-effect"><i class="mdi mdi-google-pages"></i> <span> Manage Website Pages </span> </a>
            </li> 

            <li class="has_sub">
                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-email"></i> <span> Template Management </span> <span class="menu-arrow"></span></a>
                <ul class="list-unstyled">
                    <li><a href="<?php echo base_url('admin/templates'); ?>">Email</a></li>
                    <li><a href="<?php echo base_url('admin/templates/notification'); ?>">Notification</a></li>
                </ul>
            </li>

            <li class="has_sub">
                <a href="javascript:void(0);" class="waves-effect"><i class="fa fa-list-ul"></i> <span> General Listing </span> <span class="menu-arrow"></span></a>
                <ul class="list-unstyled">
                    <li><a href="<?php echo base_url('admin/listing'); ?>">Parent List</a></li>
                    <li><a href="<?php echo base_url('admin/listing/add'); ?>">Parent Add</a></li>
                    <li><a href="<?php echo base_url('admin/listing/child'); ?>">Child List</a></li>
                    <li><a href="<?php echo base_url('admin/listing/child_add'); ?>">Child Add</a></li>
                </ul>
            </li>






<!--
            <li class="has_sub">
                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-texture"></i><span class="badge badge-warning pull-right">7</span><span> Forms </span> </a>
                <ul class="list-unstyled">
                    <li><a href="form-elements.html">General Elements</a></li>
                    <li><a href="form-advanced.html">Advanced Form</a></li>
                    <li><a href="form-validation.html">Form Validation</a></li>
                    <li><a href="form-wizard.html">Form Wizard</a></li>
                    <li><a href="form-fileupload.html">Form Uploads</a></li>
                    <li><a href="form-wysiwig.html">Wysiwig Editors</a></li>
                    <li><a href="form-xeditable.html">X-editable</a></li>
                </ul>
            </li>

            <li class="has_sub">
                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-view-list"></i> <span> Tables </span> <span class="menu-arrow"></span></a>
                <ul class="list-unstyled">
                	<li><a href="tables-basic.html">Basic Tables</a></li>
                    <li><a href="tables-datatable.html">Data Table</a></li>
                    <li><a href="tables-responsive.html">Responsive Table</a></li>
                    <li><a href="tables-editable.html">Editable Table</a></li>
                    <li><a href="tables-tablesaw.html">Tablesaw Table</a></li>
                </ul>
            </li>

            <li class="has_sub">
                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-chart-donut-variant"></i><span> Charts </span> <span class="menu-arrow"></span></a>
                <ul class="list-unstyled">
                    <li><a href="chart-flot.html">Flot Chart</a></li>
                    <li><a href="chart-morris.html">Morris Chart</a></li>
                    <li><a href="chart-chartist.html">Chartist Charts</a></li>
                    <li><a href="chart-chartjs.html">Chartjs Chart</a></li>
                    <li><a href="chart-other.html">Other Chart</a></li>
                </ul>
            </li>

            <li>
                <a href="calendar.html" class="waves-effect"><i class="mdi mdi-calendar"></i><span> Calendar </span></a>
            </li>

            <li>
                <a href="inbox.html" class="waves-effect"><i class="mdi mdi-email"></i><span class="badge badge-purple pull-right">New</span><span> Mail </span></a>
            </li>

            <li class="has_sub">
                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-google-pages"></i><span> Pages </span> <span class="menu-arrow"></span></a>
                <ul class="list-unstyled">
                    <li><a href="page-starter.html">Starter Page</a></li>
                    <li><a href="page-login.html">Login</a></li>
                    <li><a href="page-register.html">Register</a></li>
                    <li><a href="page-recoverpw.html">Recover Password</a></li>
                    <li><a href="page-lock-screen.html">Lock Screen</a></li>
                    <li><a href="page-confirm-mail.html">Confirm Mail</a></li>
                    <li><a href="page-404.html">Error 404</a></li>
                    <li><a href="page-500.html">Error 500</a></li>
                </ul>
            </li>

            <li class="has_sub">
                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-layers"></i><span>Extra Pages </span> <span class="menu-arrow"></span></a>
                <ul class="list-unstyled">
                    <li><a href="extras-projects.html">Projects</a></li>
                    <li><a href="extras-tour.html">Tour</a></li>
                    <li><a href="extras-taskboard.html">Taskboard</a></li>
                    <li><a href="extras-taskdetail.html">Task Detail</a></li>
                    <li><a href="extras-profile.html">Profile</a></li>
                    <li><a href="extras-maps.html">Maps</a></li>
                    <li><a href="extras-contact.html">Contact list</a></li>
                    <li><a href="extras-pricing.html">Pricing</a></li>
                    <li><a href="extras-timeline.html">Timeline</a></li>
                    <li><a href="extras-invoice.html">Invoice</a></li>
                    <li><a href="extras-faq.html">FAQ</a></li>
                    <li><a href="extras-gallery.html">Gallery</a></li>
                    <li><a href="extras-email-template.html">Email template</a></li>
                    <li><a href="extras-maintenance.html">Maintenance</a></li>
                    <li><a href="extras-comingsoon.html">Coming soon</a></li>
                </ul>
            </li>-->

        </ul>
        <div class="clearfix"></div>
    </div>
    <!-- Sidebar -->
    <div class="clearfix"></div>

</div>
