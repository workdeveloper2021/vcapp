<?php 
//error_reporting(0); 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');	
	class Home_template {		
		var $template_data = array();		
	
    		function load($template = '', $view = '' , $view_data = array(), $return = FALSE){           
			$CI = get_instance();
			//define constant
			define("BASEURL",base_url());
			define("IMAGE",BASEURL.$CI->config->item('template_path')."img/");
			$data 				= 	$view_data;
			//side title
			$config['site_title'] 		= $CI->config->item('site_title');			
			//add meta
			$CI->head->add_meta('viewport', 'width=device-width, initial-scale=1.0, maximum-scale=1');
			//add css
			$CI->head->add_css($CI->config->item('googleapis_3467_css'));
			//add js
			$CI->head->add_js($CI->config->item('jquery_min_js'));
            
			//this is common file for all form
			$CI->head->add_js($CI->config->item('common_js'));
			//add inline js code
			$CI->head->add_inline_js("var baseUrl= '".base_url()."' ; ");
            //~ $data['head'] = $CI->head->render_head($config);			
			$data['content'] = $CI->load->view($view, $data,true );
			$data['module']=$CI->router->fetch_class();
			$data['moduleMethod']=$CI->router->fetch_method();
 			$CI->load->view($template, $data);				
		}
	}

?>
