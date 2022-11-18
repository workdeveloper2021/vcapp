<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Dynamic_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}
	public function insertdata($tbnm = null,$var = array()){
		$this->db->insert($tbnm, $var);
		return $this->db->insert_id(); 
	}	

	public function deletedata($tbnm=null,$where= array()){
	   $this->db->where($where);	   
	   $this->db->delete($tbnm);
    }
	public function updatedata($tbnm = null,$var = array(), $postid){
		$this->db->where('id', $postid);
		$this->db->update($tbnm, $var);
		return $this->db->insert_id(); 
	}
	public function updateRowWhere($table, $where = array(), $data = array()) {
	    $this->db->where($where);
	    $this->db->update($table, $data);
	    $updated_status = $this->db->affected_rows();
 		return $updated_status;
	}
	public function add_user_meta($usid = 0,$key = null,$val = null){
		$arg = array(
		    'user_id' => $usid,
		    'meta_key' => $key,
		    'meta_value' => $val
		);
		$this->db->insert('user_meta', $arg);
	}

	/*public function add_post_meta($usid = 0,$key = null,$val = null){
		$arg = array(
		    'post_id' => $usid,
		    'meta_key' => $key,
		    'meta_value' => $val
		);
		$this->db->insert('post_meta', $arg);
	}*/

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
			}else if($asset_type == 'Model'){
				$type = '*';
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
            	// $picture = $this->upload->display_errors();
            	// print_r($picture); die;
                $picture = '';
            }
		} else {
			$picture = '';
		}
		return $picture;
	}  
	public function multiple_fileupload($filenm, $foldername, $asset_type = ""){	
		if($asset_type == ''){
			$type = 'mp4|3gp|mpeg|jpg|jpeg|png|gif';
		} else if($asset_type == 'Picture'){
			$type = MEDIA_PICTURE;
		} else if($asset_type == 'Audio'){
			$type = MEDIA_AUDIO;
		} else if($asset_type == 'Video'){
			$type = MEDIA_VIDEO;
		} else if($asset_type == 'Pdf'){
			$type = MEDIA_PDF;
		}
         // echo $type;die;
	   	$number_of_files = sizeof($_FILES["$filenm"]['tmp_name']);
	   	$files = $_FILES["$filenm"];
	   	$errors = array();
	   	$upload_result = array();

	   for($i=0;$i<$number_of_files;$i++) {
	     if($_FILES["$filenm"]['error'][$i] != 0) $errors[$i][] = 'Couldn\'t upload file '.$_FILES["$filenm"]['name'][$i];
	   }
	   if(sizeof($errors)== 0) {
	     $this->load->library('upload');

	     $config['upload_path'] = './'.$foldername.'/';
	     // $config['allowed_types'] = 'mp4|3gp|mpeg|jpg|jpeg|png|gif';
	     $config['allowed_types'] = $type;
	     for ($i = 0; $i < $number_of_files; $i++) {
	       $_FILES['uploadedimage']['name'] = time().str_replace(str_split(' ()\\/,:*?"<>|'), '', 

	   	  $_FILES[$filenm]['name'])[$i];
	       $_FILES['uploadedimage']['type'] = $files['type'][$i];
	       $_FILES['uploadedimage']['tmp_name'] = $files['tmp_name'][$i];
	       $_FILES['uploadedimage']['error'] = $files['error'][$i];
	       $_FILES['uploadedimage']['size'] = $files['size'][$i];
	       $this->upload->initialize($config);
	       if($this->upload->do_upload('uploadedimage')){
	         $data = $this->upload->data();
	         $picture = $data['file_name'];
             $upload_result[$i]['original_url'] = $picture;
		     $upload_result[$i]['thumb_url'] = "";
	       }else{
	         //print_r($this->upload->display_errors());die;
	          $upload_result['upload_errors'][$i] = $this->upload->display_errors();
	       }
	     }
	   } else {
	     $upload_result['error'] = $errors;
	   }
	   return $upload_result;
	}
	/* Upload video file and create video thumbnail  */

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





					// $movie = $picture;
					// $thumbnail = 'thumbnail.png';

					// $mov = new ffmpeg_movie($movie);
					// $frame = $mov->getFrame($frame);
					// if ($frame) {
					//     $gd_image = $frame->toGDImage();
					//     if ($gd_image) {
					//         imagepng($gd_image, $thumbnail);
					//         imagedestroy($gd_image);
					//         echo '<img src="'.$thumbnail.'">';
					//     }
					// }

					// die;


                
                if($picture != ''){
                    $size = '300X200';
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
            	// echo'<pre>';
            	// print_r($error = array('error' => $this->upload->display_errors()));
            	// die;
                $video_arr = array();
            }
        } else {
            $video_arr = array();
        }
       
        return $video_arr;
    }

	/* End video upload code*/
	
	public function checkEmail($key){
		$arg = array(
		    'email' => $key
		);
		$query = $this->db->get_where(TABLE_USERS, $arg);
		if($query->num_rows() != 0){
			return $query->result_array();
		} else {
			return false;
		}
	}

	public function checkMobile($key){
		$arg = array(
		    'mobile' => $key
		);
		$query = $this->db->get_where(TABLE_USERS, $arg);
		if($query->num_rows() != 0){
			return $query->result_array();
		} else {
			return false;
		}
	}

	public function checkSocialid($key){
		$arg = array(
		    'social_id' => $key
		);
		$query = $this->db->get_where(TABLE_USERS, $arg);
		if($query->num_rows() != 0){
			return $query->result_array();
		} else {
			return false;
		}
	}



	public function get_user_by_id($usid = 0){
		$key = array(
			'id' => $usid
		);
		$query = $this->db->get_where(TABLE_USERS, $key);
		return $query->row_array();
	}

	public function get_category_by_id($usid = 0){
		$key = array(
			'id' => $usid
		);
		$query = $this->db->get_where(TABLE_BUSINESS_CATEGORY, $key);
		return $query->row_array();
	}



	public function get_user($usid){
		 $data = $this->get_user_by_id($usid);
		 /*$data['first_name'] = $this->get_user_meta($data['id'], 'first_nm');
		 $data['user_address'] = $this->get_user_meta($data['id'], 'user_address');
		 $data['user_phone'] = $this->get_user_meta($data['id'], 'user_phone');
		 $data['user_pic'] = $this->get_user_meta($data['id'], 'user_pic');
		 $data['last_nm'] = $this->get_user_meta($data['id'], 'last_nm');*/
		 return $data;
	}

	

	/*public function get_user_meta($usid = 0,$key = null){
		$key = array(
			'user_id' => $usid,
			'meta_key' => $key
		);
		$this->db->select('meta_value');
		$query = $this->db->get_where('user_meta', $key);
		$metavalue = $query->row_array();
		return $metavalue['meta_value'];
	}*/


	/*************** Get Table Data *******************/	

	public function getdatafromtable($tbnm, $condition = array(), $data = "*", $limit = "", $offset= "", $orderby = "", $ordertype = "ASC"){
		 
			$this->db->select($data);
			$this->db->from($tbnm);
			$this->db->where($condition);
			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			if($orderby != ''){
				$this->db->order_by($orderby, $ordertype);
			}	
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				return $query->result_array();
			} else {				
				return false;
			}	 

	}

	/*************** Get Table Data *******************/	

	public function getWhereInData($tbnm,$Inconditioncolmn='',$Incondition=array(),$condition = array(), $data = "*", $limit = "", $offset= "", $orderby = "", $ordertype = "ASC",$like=''){
		 
			//print_r($Incondition);die;
			$this->db->select($data);
			$this->db->from($tbnm);
			if(!empty($Incondition)){
			$this->db->where_in($Inconditioncolmn,$Incondition);
		    }
            if(!empty($condition)){
			 $this->db->where($condition);
		    }
		    if(!empty($like)){
		    $this->db->where($like,NUll);
	     	}
			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			if($orderby != ''){
				$this->db->order_by($orderby, $ordertype);
			}	
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				 return $query->result_array();
				 //echo $this->db->last_query();die;
			} else {				
				return false;
			}	 

	}

	/*************** Count *******************/	

	public function countdata($tablename,$condition){ 
		$this->db->select('COUNT(*) as counting');
		$query=$this->db->from($tablename);
		$query=$this->db->where($condition);
		$query=$this->db->get();
		return $query->result_array();
	}



	/*************** Option Table data *******************/	

	public function getoptions($condition){ 
		$this->db->select('option_value');
		$query=$this->db->from(TABLE_OPTIONS);
		$query=$this->db->where($condition);
		$query=$this->db->get();
		return $query->result_array();
	}



	/* GET THREE TABLE DATA  */

	/****************************************/

	public function getTwoTableData($data,$table1,$table2,$on,$condition = '',$limit="", $offset= "", $orderby = "", $ordertype = "ASC", $join = "inner", $group_by_key=''){
        $this->db->select($data);
        $this->db->from($table1);
        $this->db->join($table2,$on, $join);
        if($limit != ''){
			$this->db->limit($limit, $offset);
		}
        if(!empty($condition)){
           $this->db->where($condition);
        }
        if($orderby != ''){
			$this->db->order_by($orderby, $ordertype);
		}
		if($group_by_key !=''){
			$this->db->group_by($group_by_key);
		}
        $query=$this->db->get();
        return $query->result_array();
	}



	public function getThreeTableData($data,$table1,$table2,$table3,$on,$on2,$condition){

	        $this->db->select($data);
	        $this->db->from($table1);
	        $this->db->join($table2,$on);
	        $this->db->join($table3,$on2);
	        $this->db->where($condition);
	        $query=$this->db->get();
	        return $query->result_array();
	}
	/* Get search Query */
	public function get_search($tbnm, $match) {
		$this->db->like('company_nm','jain');
		$this->db->or_like('author','test');
		/*
		$this->db->or_like('author',$match);
		$this->db->or_like('characters',$match);
		$this->db->or_like('synopsis',$match);
		*/
		$query = $this->db->get($tbnm);
		return $query->result();
	}

	public function get_country(){
		$this->db->select('*');
		$this->db->from(TABLE_COUNTRY);
		//$this->db->order_by('nicename',"ASC");
		$query = $this->db->get();
		return $query->result_array();
	}

	
	function get_query_result($query){
           $query = $this->db->query($query);
           if($query->num_rows() > 0){
               return $query->result();
           } else {
               return FALSE;
           }
       }
	public function check_user_role($email='',$role='',$flag='',$role2=''){
		$this->db->select('user.id,user.name,user.lastname,user.email,user.password,user_role.role_id');
		$this->db->from('user');
		$this->db->join('user_role','user_role.user_id = user.id');
		$this->db->where('user.email',$email);
		if($flag==1){
		$this->db->where('(user_role.role_id='.$role.' OR user_role.role_id='.$role2.' )');
		}else{
         $this->db->where('user_role.role_id',$role);
		}	
		$this->db->limit(1);	
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		if ($query->num_rows() > 0) {
			//print_r($query->result_array()); die;
			  return  $query->result_array();
			 
		} else {				
			return false;
		}
	}


	public function check_skip_register_user_role($device_token='',$role=''){
		$this->db->select('user.id,user.name,user.lastname,user.email,user.password,user_role.role_id');
		$this->db->from('user');
		$this->db->join('user_role','user_role.user_id = user.id');
		$this->db->where('user.device_token',$device_token);
        $this->db->where('user_role.role_id',$role);
		$this->db->limit(1);	
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		if ($query->num_rows() > 0) {
			//print_r($query->result_array()); die;
			  return  $query->result_array();
			 
		} else {				
			return false;
		}
	}

  public function business_time_slote($business_id=''){
		$this->db->select('business_time_slote.*,manage_week_days.week_name');
		$this->db->from('business_time_slote');
		$this->db->join('manage_week_days','manage_week_days.id = business_time_slote.day_id');
		$this->db->where('business_id',$business_id);
		$this->db->group_by('business_time_slote.day_id');
		$query = $this->db->get();
		
		if($query->num_rows() > 0) {
			return  $query->result_array(); 
		}else{				
			return false;
		}	
	} 

	public function getQueryResultArray($query) {
		return $this->db->query($query)->result_array();
	}

	public function getQueryRowArray($query) {
		return $this->db->query($query)->row_array();
	}

	public function getSingleROwColumnValue($table, $where, $select = '*') {
		$this->db->select($select);
		return $this->db->get_where($table, $where)->row_array();
	}

	public function getMultipleOwColumnValue($table, $where, $select = '*') {
		$this->db->select($select);
		return $this->db->get_where($table, $where)->result_array();
	}

	public function customQuery($query)
		{
			$query = $this->db->query($query);
			$result = $query->result();
			
			return $result;
		}

}