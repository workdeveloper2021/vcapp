<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Videothumbnail extends My_Controller {
	public function __construct(){
        parent::__construct();
        
        $this->load->model('videothumbnail_model');
        if($this->session->userdata('logged_in')){
            $currentuser = getuserdetails();
            $this->login_user_id = $currentuser['id'];
        }
    }

    public function index(){
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('dashboard_breadcrumb') , 'admin'); 
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('appcontent_breadcrumb') , 'admin/category'); 
        $header['title'] = $this->lang->line('category');
        $this->admintemplates('videothumbnail_add', '', $header);
    } 



    // Insert New category Function
    public function assets_submit(){
        extract($this->input->post());
         
        // var_dump(decode($uploadtype));
        // echo decode($uploadtype);
         
        
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            if (!empty($_FILES['assets_image']['name'])) {
                $img_arr = $this->videothumbnail_model->videoupload('assets_image','uploads/test_video');
                echo'<pre>';
                print_r($img_arr);
                /*$size = '300X300';
                $path = 'uploads/category/';
                $video = $path . escapeshellcmd($img_name);
                $cmd = "ffmpeg -i $video 2>&1";
                $second = 1;
                if (preg_match('/Duration: ((\d+):(\d+):(\d+))/s', `$cmd`, $time)) {
                    $total = ($time[2] * 3600) + ($time[3] * 60) + $time[4];
                    $second = rand(1, ($total - 1));
                }
                $image  = $path.strstr($img_name ,'.',true).'_thumb.jpg';
               // $image  = 'uploads/test_video/random_name.jpg';
                $cmd = "ffmpeg -i $video -deinterlace -an -ss $second -s $size -t 00:00:01 -r 1 -y -vcodec mjpeg -f mjpeg $image 2>&1";
                $do = `$cmd`;*/

            } else {
                $img_name = '';
            }
        }else {
            $this->session->set_flashdata('assetclass', 'danger');
            $this->session->set_flashdata('asseterror', 'Wrong Method. Please Fill this Form');
            redirect(site_url().'admin/videothumbnail');
        }
    }

    function create_movie_thumb($src_file,$mediapath,$mediaid)
            {
                global $CONFIG, $ERROR;

                $CONFIG['ffmpeg_path'] = '/usr/local/bin/'; // Change the path according to your server.
                $dir_img='uploads/';
                $CONFIG['fullpath'] = $dir_img."thumbs/";

                $src_file = $src_file;
                $name_file=explode(".",$mediapath);
                $imgname="thumb_".$name_file[0].".jpg";
                $dest_file = $CONFIG['fullpath'].$imgname;

                if (preg_match("#[A-Z]:|\\\\#Ai", __FILE__)) {
                    // get the basedir, remove '/include'
                    $cur_dir = substr(dirname(__FILE__), 0, -8);
                    $src_file = '"' . $cur_dir . '\\' . strtr($src_file, '/', '\\') . '"';
                    $ff_dest_file = '"' . $cur_dir . '\\' . strtr($dest_file, '/', '\\') . '"';
                } else {
                    $src_file = escapeshellarg($src_file);
                    $ff_dest_file = escapeshellarg($dest_file);
                }

                $output = array();

                if (eregi("win",$_ENV['OS'])) {
                    // Command to create video thumb
                    $cmd = "\"".str_replace("\\","/", $CONFIG['ffmpeg_path'])."ffmpeg\" -i ".str_replace("\\","/" ,$src_file )." -an -ss 00:00:05 -r 1 -vframes 1 -y ".str_replace("\\","/" ,$ff_dest_file);
                    exec ("\"$cmd\"", $output, $retval);

                } else {
                    // Command to create video thumb
                    $cmd = "{$CONFIG['ffmpeg_path']}ffmpeg -i $src_file -an -ss 00:00:05 -r 1 -vframes 1 -y $ff_dest_file";
                    exec ($cmd, $output, $retval);

                }


                if ($retval) {
                    $ERROR = "Error executing FFmpeg - Return value: $retval";
                    if ($CONFIG['debug_mode']) {
                        // Re-execute the command with the backtick operator in order to get all outputs
                        // will not work if safe mode is enabled
                        $output = `$cmd 2>&1`;
                        $ERROR .= "<br /><br /><div align=\"left\">Cmd line : <br /><span style=\"font-size:120%\">" . nl2br(htmlspecialchars($cmd)) . "</span></div>";
                        $ERROR .= "<br /><br /><div align=\"left\">The ffmpeg program said:<br /><span style=\"font-size:120%\">";
                        $ERROR .= nl2br(htmlspecialchars($output));
                        $ERROR .= "</span></div>";
                    }
                    @unlink($dest_file);
                    return false;
                }

                $return = $dest_file;
                //@chmod($return, octdec($CONFIG['default_file_mode'])); //silence the output in case chmod is disabled
                return $return;
            }

    /***********************************************************/
}
