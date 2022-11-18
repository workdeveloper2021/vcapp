<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class LangLoader{
	
	function initialize() {
		$ci =& get_instance();
        $ci->load->library('session');
        if($ci->session->userdata('logged_in')){
            $language = $ci->session->userdata['logged_in']['session_language'];
            $ci->lang->load("message", "$language");
        } else {
            $language = "english";
            $ci->lang->load("message", "$language");
        }

        $path = $ci->config->item('Document_Root_Path').'application/language/'.$language.'/';
        if (file_exists($path.'breadcrumb_lang.php')) { $ci->lang->load('breadcrumb', $language); }


    /* 
        if (file_exists($path.'title_lang.php')) { $ci->lang->load('title', $language); }
        if (file_exists($path.'leftsidebar_lang.php')) { $ci->lang->load('leftsidebar', $language);}
        if (file_exists($path.'other_lang.php')) { $ci->lang->load('other', $language);}
        if (file_exists($path.'tooltip_lang.php')) { $ci->lang->load('tooltip', $language);}
        if (file_exists($path.'language_lang.php')) { $ci->lang->load('language', $language);}
    */
		 
    }

}


?>
