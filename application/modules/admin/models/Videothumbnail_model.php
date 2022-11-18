<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Videothumbnail_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}
    
        public function fileupload($filenm, $foldername, $asset_type = ""){
        if(!empty($_FILES[$filenm]['name'])){

            if($asset_type == ''){
                $type = 'mp4|3gp|mpeg|jpg|jpeg|png|gif';
            } else if($asset_type == 'Picture'){
                $type = MEDIA_PICTURE;
            } else if($asset_type == 'Audio'){
                $type = MEDIA_AUDIO;
            } else if($asset_type == 'Video'){
                $type = MEDIA_VIDEO;
            }else if($asset_type == 'Pdf'){
                $type = MEDIA_PDF;
            }
            
            $new_image_name = time().str_replace(str_split(' ()\\/,:*?"<>|'), '', $_FILES[$filenm]['name']);
            $config['upload_path'] = './'.$foldername.'/';
            $config['allowed_types'] = $type;
            $config['file_name'] = $new_image_name;
            $config['overwrite'] = TRUE;
            $config['max_width']  = '0';
            $config['max_height']  = '0';
            $this->load->library('upload',$config);
            $this->upload->initialize($config);
            if($this->upload->do_upload($filenm)){
                $uploadData = $this->upload->data();
                $config['image_library'] = 'gd2'; 
                $config['source_image'] = $uploadData['full_path'];
                $config['create_thumb'] = TRUE;
                $config['maintain_ratio'] = TRUE;
                $config['width']         = 300;
                $config['height']       = 300;
                $this->load->library('image_lib', $config);
                if (!$this->image_lib->resize()) {
                }
                $picture = $uploadData['file_name'];
            }else{
                $picture = '';
            }
        } else {
        }
        
        return $picture;
    }

    public function videoupload($filenm, $foldername){
        if(!empty($_FILES[$filenm]['name'])){
            $type = MEDIA_VIDEO;
            $video_arr = array();
            $new_image_name = time().str_replace(str_split(' ()\\/,:*?"<>|'), '', $_FILES[$filenm]['name']);
            $config['upload_path'] = './'.$foldername.'/';
            $config['allowed_types'] = $type;
            $config['file_name'] = $new_image_name;
            $config['overwrite'] = TRUE;
            $config['max_width']  = '0';
            $config['max_height']  = '0';
            $this->load->library('upload',$config);
            $this->upload->initialize($config);
            if($this->upload->do_upload($filenm)){
                $uploadData = $this->upload->data();
                $config['image_library'] = 'gd2'; 
                $config['source_image'] = $uploadData['full_path'];
                $config['create_thumb'] = TRUE;
                $config['maintain_ratio'] = TRUE;
                $config['width']         = 300;
                $config['height']       = 300;
                $this->load->library('image_lib', $config);
                if (!$this->image_lib->resize()) {
                }
                $picture = $uploadData['file_name'];
                /*************************************/
                if($picture != ''){
                    $size = '300X300';
                    $path = $foldername.'/';
                    $video = $path . escapeshellcmd($picture);
                    $cmd = "ffmpeg -i $video 2>&1";
                    $second = 1;
                    if (preg_match('/Duration: ((\d+):(\d+):(\d+))/s', `$cmd`, $time)) {
                        $total = ($time[2] * 3600) + ($time[3] * 60) + $time[4];
                        $second = rand(1, ($total - 1));
                    }
                    $image  = $path.strstr($picture ,'.',true).'_thumb.jpg';
                   // $image  = 'uploads/test_video/random_name.jpg';
                    $cmd = "ffmpeg -i $video -deinterlace -an -ss $second -s $size -t 00:00:01 -r 1 -y -vcodec mjpeg -f mjpeg $image 2>&1";
                    $do = `$cmd`;

                    $video_arr['original_url'] = $picture;
                    $video_arr['thumb_url'] = strstr($picture ,'.',true).'_thumb.jpg';
                } else {
                    $video_arr = array();
                }                
                /*************************************/
            }else{
                $video_arr = array();
            }
        } else {
            $video_arr = array();
        }
       
        return $video_arr;
    }
    

}