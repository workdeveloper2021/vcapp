<?php if (!defined('BASEPATH'))exit('No direct script access allowed');
/*
====================================================================================================
	* 
	* @description: This is coomon model for admin and all user type
	* 
	* 
====================================================================================================*/

class Common_model extends CI_Model{
   
    function __construct(){
        parent::__construct();    
         //$this->load->library('image_lib');
    }
    
	/*
	 * @description: This function is used countResult
	 * 
	 */ 
	
	function countResult($table='',$field='',$value='', $limit=0,$groupBy = ''){
	
		if(is_array($field)){
				$this->db->where($field);
		}
		elseif($field!='' && $value!=''){
			$this->db->where($field, $value);
		}	
		$this->db->from($table);
		if(!empty($groupBy)){
			$this->db->group_by($groupBy);
		}
		
		if($limit >0){
			$this->db->limit($limit);
		}
		
		 $res= $this->db->count_all_results();
		// echo $this->db->last_query();
		 return $res;
		 
	}

	
	/*
	 * @description: This function is used getDataFromTabelWhereIn
	 * 
	 */ 
	
	function getDataFromTableWhereIn($table='', $field='*',  $whereField='', $whereValue='', $orderBy='', $order='ASC', $whereNotIn=0){
		
		$table=$table;
		 $this->db->select($field);
		 $this->db->from($table);
		 
		if($whereNotIn > 0){
			$this->db->where_not_in($whereField, $whereValue);
		}else{
			$this->db->where_in($whereField, $whereValue);
		}
		
		if(is_array($orderBy) && count($orderBy)){
			/* $orderBy treat as where condition if $orderBy is array  */
			$this->db->where($orderBy);
		}
		elseif(!empty($orderBy)){  
			$this->db->order_by($orderBy, $order);
		}
		
		$query = $this->db->get();
		
		$result = $query->result_array();
		if(!empty($result)){
			return 	$result;
		}
		else{
			return FALSE;
		}
	}
	
	
	/*
	 * @description: This function is used getDataFromTabel
	 * 
	 */
	
	function getObjectDataFromTable($table='', $field='*',  $whereField='',$whereInField='',$whereNotIn=''){
		
		$table=$table;
		$this->db->select($field);
		$this->db->from($table);
		$this->db->where($whereField);
		if($whereInField!=''){
			$this->db->where_not_in($whereInField, $whereNotIn);
		}
		$query = $this->db->get();
		//echo $this->db->last_query();
		$result = $query->row();
		
		return 	$result;
	}

	/*
	 * @description: This function is used getDataFromTabelWhereWhereIn
	 * 
	 */
	
	function getDataFromTableWhereWhereIn($table='', $field='*',  $where='',  $whereinField='', $whereinValue='', $orderBy='', $whereNotIn=0){
	
		$table=$table;
		 $this->db->select($field);
		 $this->db->from($table);
		 
		if(is_array($where)){
			$this->db->where($where);
		}
		
		if($whereNotIn > 0){
			$this->db->where_not_in($whereinField, $whereinValue);
		}else{
			$this->db->where_in($whereinField, $whereinValue);
		}
		
		if(!empty($orderBy)){  
			$this->db->order_by($orderBy);
		}
		
		$query = $this->db->get();
		//echo $this->db->last_query();
		$result = $query->result();
		if(!empty($result)){
			return 	$result;
		}
		else{
			return FALSE;
		}
	}
	
	
	/*
	 * @description: This function is used getDataFromTabel
	 * 
	 */
	
	function getDataFromTable($table='', $field='*',  $whereField='', $whereValue='', $orderBy='', $order='ASC', $limit=0, $offset=0, $resultInArray=false  ){
		
		$table=$table;
		 $this->db->select($field);
		 $this->db->from($table);
		
		if(is_array($whereField)){
			$this->db->where($whereField);
		}elseif(!empty($whereField) && $whereValue != ''){
			$this->db->where($whereField, $whereValue);
		}

		if(!empty($orderBy)){  
			$this->db->order_by($orderBy, $order);
		}
		if($limit > 0){
			$this->db->limit($limit,$offset);
		}
		$query = $this->db->get();
		
		//echo $this->db->last_query(); die;
		if($resultInArray){
			$result = $query->result_array();
		}else{
			$result = $query->result();
		}
		
		if(!empty($result)){
			return 	$result;
		}
		else{
			return FALSE;
		}
	}
	
	/*
	 * @description: This function is used addDataIntoTabel
	 * 
	 */
	
	function addDataIntoTable($table='', $data=array()){
		$table=$table;
		if($table=='' || !count($data)){
			return false;
		}
		$inserted = $this->db->insert($table , $data);
		$this->db->last_query();
		$ID = $this->db->insert_id();
		return $ID;
	}
	
	/*
	 * @description: This function is used updateDataFromTabel
	 * 
	 */
	 
	function updateDataFromTable($table='', $data=array(), $field='', $ID=0){
		$table=$table;
		if(empty($table) || !count($data)){
			return false;
		}
		else{
			if(is_array($field)){
				
				$this->db->where($field);
			}else{
				$this->db->where($field , $ID);
			}
			return $this->db->update($table , $data);
		}
	}
	/*
	 * @description: This function is used updateDataFromTabelWhereIn
	 * 
	 */
	
	function updateDataFromTabelWhereIn($table='', $data=array(), $where=array(), $whereInField='', $whereIn=array(), $whereNotIn=false){
		$table=$table;
		if(empty($table) || !count($data)){
			return false;
		}
		else{
			if(is_array($where) && count($where) > 0){
				
				$this->db->where($where);
			}
			
			if(is_array($whereIn) && count($whereIn) > 0 && $whereInField != ''){
				if($whereNotIn){
					$this->db->where_not_in($whereInField,$whereIn);
				}else{
					$this->db->where_in($whereInField,$whereIn);
				}
			}
			return $this->db->update($table , $data);
		}
	}
	
	
	/*
	 * @description: This function is used deleteRowFromTabel
	 * 
	 */
	 
	function deleteRowFromTable($table='', $field='', $ID=0, $limit=0){
		$table=$table;
		$Flag=false;
		if($table!='' && $field!=''){
			if(is_array($ID) && count($ID)){
				$this->db->where_in($field ,$ID);
			}elseif(is_array($field) && count($field) > 0){
				$this->db->where($field);
			}else{
				$this->db->where($field , $ID);
			}
			if($limit >0){
				$this->db->limit($limit);
			}
			if($this->db->delete($table)){
				$Flag=true;
			}
		}
		//echo $this->db->last_query();
		return $Flag;
	}
	
	/*
	 * @description: This function is used deletelWhereWhereIn
	 * 
	 */
	 
	 
	function deletelWhereWhereIn($table='', $where='',  $whereinField='', $whereinValue='', $whereNotIn=0){
		$table=$table;
		if(is_array($where)){
			$this->db->where($where);
		}
		
		if($whereNotIn > 0){
			$this->db->where_not_in($whereinField, $whereNotIn);
		}else{
			$this->db->where_in($whereinField, $whereinValue);
		}
		
		if($this->db->delete($table)){
				return true;
		}else{
			return false;
		}
	}
	
	
	/*
	 * @description: This function is used deleteRow
	 * 
	 */
	// Delete single row 
	function deleteRow($table,$where)
	{
		$table=$table;
		$this->db->delete($table, $where);
		//echo $sql = $this->db->last_query(); die;
		return $this->db->affected_rows();
	}

	/* Delete Multiple row */

	function deleteMultipleRow($table,$where)
	{
		$table=$table;
		$this->db->where_in('media_id', $where);
		$this->db->delete($table);
		//echo $sql = $this->db->last_query(); die;
		return $this->db->affected_rows();
	}
	
	
     /**
     *  function for get Data From Table
     *  param $table, $field, $whereField, $whereValue, $orderBy, $order, $limit, $offset, $resultInArray
     *  return result row
     **/
	function getDataFromTabel($table, $field='*',  $whereField='', $whereValue='', $orderBy='', $order='ASC', $limit=0, $offset=0, $resultInArray=false, $join = '' , $extracondition = ''){
        
        $this->db->select($field);
        $this->db->from($table);

        if(is_array($whereField)){
            $this->db->where($whereField);
        }elseif(!empty($whereField) && $whereValue != ''){
            $this->db->where($whereField, $whereValue);
        }

        if(!empty($orderBy)){  
            $this->db->order_by($orderBy, $order);
        }
        if($limit > 0){
            $this->db->limit($limit,$offset);
        }
        $query = $this->db->get();
        if($resultInArray){
            $result = $query->result_array();
        }else{
            $result = $query->result();
        }
		if(!empty($result)){
            return $result;
        }
        else{
            return FALSE;
        }
	}
    
    
     /**
     *  function for update data of table
     *  param $table, $data, $field, $id
     *  return result
     **/
    function updateDataFromTabel($table='', $data=array(), $field='', $id=0){
        if(empty($table) || !count($data)){
            return false;
        }
        else{
            if(is_array($field)){                 
                $this->db->where($field);
            }else{
                $this->db->where($field, $id);
                
            }
            return $this->db->update($table , $data);
        }
    }

    /*
    *
    *
    *
    */
	public function insertDataFromTable($tableName, $insertData)
	{	
		$tableName=$tableName;
		if($tableName=='' || !count($insertData)){
			return false;
		}
		$query = $this->db->insert($tableName, $insertData); 
		$id = $this->db->insert_id();
		return $id;
	}
    /* Insert Multiple data */
    public function insertMultipleData($tableName, $insertData)
	{
		$tableName=$tableName;
		if($tableName=='' || !count($insertData)){
			return false;
		}
		$query = $this->db->insert_batch($tableName, $insertData); 
		$id = $this->db->insert_id();
		return $id;
	}
    
     /*
    *  @access: public
    *  @Description: This method is use to upload picture 
    *  @auther: Gokul Rathod
    *  @return: void
    */
    
    public function uploadProfileImage($name,$dirPath,$fileName) {
		$data = array();
		$response=array();
        if (isset($_FILES[$fileName]["name"]) && $_FILES[$fileName]["name"]!="") {
		   	$cdate= date("dmyHis");
			$imageName = str_replace(" ","-",$name).'-'.$cdate;
            //echo "<pre>"; print_r($imageName);die;
			$this->load->library('upload');
			$this->upload->initialize(set_upload_options($dirPath,'jpg|JPEG|PNG|png|jpeg|mp3|mp4|wma',$imageName));
			$this->upload->do_upload($fileName);
			if($this->upload->display_errors()){
				$this->messages->setMessageFront($this->upload->display_errors(),'error');	
				return array("status" => 'error');
			}else{
				$data=$this->upload->data();
    //         	if(($data['image_width']<'100') || ($data['image_height']<'40')) {
				//   	$this->messages->setMessageFront('The image you are attempting to upload should be greater than 100*40','error');	
				//   	return array("status" => 'error');
				// } else {
					$file_path=$dirPath.$data['file_name'];
                    
					//$file_path=$restMenuFolderName.$data['file_name'];
					$this->load->library('image_lib');
					// clear config array
					$config = array();
					$config['image_library'] 	= 'gd2';
					$config['source_image'] 	= $file_path;
					$config['maintain_ratio'] 	= TRUE;
					$config['create_thumb'] 	= FALSE;
					$response = array(
						"status" => 'success',
						"imageName" => $data['file_name'],
						"width" => $data['image_width'],
						"height" => $data['image_height']
				    );
				// }
			}
		}
		return $response;
	}
		
    
	public function multiple_fileupload($filenm, $foldername)
	{
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
	      $config['allowed_types'] = 'mp4|3gp|mpeg|jpg|jpeg|png|gif';
	      for ($i = 0; $i < $number_of_files; $i++) {
	        $_FILES['uploadedimage']['name'] = time().str_replace(str_split(' ()\\/,:*?"<>|'), '', 

    		$_FILES[$filenm]['name'])[$i];
	        $_FILES['uploadedimage']['type'] = $files['type'][$i];
	        $_FILES['uploadedimage']['tmp_name'] = $files['tmp_name'][$i];
	        $_FILES['uploadedimage']['error'] = $files['error'][$i];
	        $_FILES['uploadedimage']['size'] = $files['size'][$i];
	        $this->upload->initialize($config);
	        if ($this->upload->do_upload('uploadedimage'))  {
	          $data = $this->upload->data();
	          $upload_result[$i] = $data['file_name'];
	        } else {
	          $data['upload_errors'][$i] = $this->upload->display_errors();
	        }
	      }
	    } else {
	      $upload_result['error'] = $errors;
	    }
	    return $upload_result;
	}

	/* Custom multiple file upload */
	public function MultipleFileupload($filenm, $foldername)
	{
    	$number_of_files = sizeof($_FILES["$filenm"]['tmp_name']);
    	$files = $_FILES["$filenm"];

    	$errors = array();
    	$response = array();
    	$upload_result = array();
 
	    for($i=0;$i<$number_of_files;$i++) {
	      if($_FILES["$filenm"]['error'][$i] != 0) $errors[$i][] = 'Couldn\'t upload file '.$_FILES["$filenm"]['name'][$i];
	    }
	    if(sizeof($errors)== 0) {
	      $this->load->library('upload');

	      $config['upload_path'] = './'.$foldername.'/';
	      $config['allowed_types'] = 'jpg|jpeg|png|gif|JPG|JPEG|PNG|GIF';
	      for ($i = 0; $i < $number_of_files; $i++) {
	      $add_name = rand(10,99999);
	        $_FILES['uploadedimage']['name'] = time().'_'.str_replace(str_split(' ()\\/,:*?"<>|'), '', 

    		$_FILES[$filenm]['name'])[$i];
	        $_FILES['uploadedimage']['type'] = $files['type'][$i];
	        $_FILES['uploadedimage']['tmp_name'] = $files['tmp_name'][$i];
	        $_FILES['uploadedimage']['error'] = $files['error'][$i];
	        $_FILES['uploadedimage']['size'] = $files['size'][$i];

	        $this->upload->initialize($config);
	        if ($this->upload->do_upload('uploadedimage'))  {
	          $data = $this->upload->data();
	          $upload_result[$i] = $data['file_name'];
	         $media_name = $data['file_name'];
		$link = 'uploads/business/'.$media_name;
		   $upload_result[$i] = $data['file_name'];
	       $config['image_library'] = 'gd2'; 
        $config['source_image'] = $link;
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = TRUE;
        $config['width']         = 300;
        $config['height']       = 300;
        $st  = json_encode($this->load->library('image_lib', $config));

         $this->image_lib->clear();
    	$this->image_lib->initialize($config);
    	$this->image_lib->resize();
    	
	        } else {
	          
	          $response = array("status" => 'error', 'error'=>$this->upload->display_errors());
	        }
	      }
	    } else {
	    
	      $response = array("status" => 'error', 'error'=>$errors);
	    }

	    $response = array("status" => 'success', "imageName" => $upload_result);
	    return $response;
	}
	/* Single video upload*/
	public function UploadVideo($name,$dirPath,$fileName) {
		$data = array();
		$response=array();
        if (isset($_FILES[$fileName]["name"]) && $_FILES[$fileName]["name"]!="") {
		   	$cdate= date("dmyHis");
			$imageName = str_replace(" ","-",$name).'-'.$cdate;
            //echo "<pre>"; print_r($imageName);die;
			$this->load->library('upload');
			$this->upload->initialize(set_upload_options($dirPath,'mp3|mp4',$imageName));
			$this->upload->do_upload($fileName);
			if($this->upload->display_errors()){
				$this->messages->setMessageFront($this->upload->display_errors(),'error');	
				return array("status" => 'error');
			}else{
				$data=$this->upload->data();
					$file_path=$dirPath.$data['file_name'];
					$this->load->library('image_lib');
					// clear config array
					$config = array();
					$config['image_library'] 	= 'gd2';
					$config['source_image'] 	= $file_path;
					$config['maintain_ratio'] 	= TRUE;
					$config['create_thumb'] 	= FALSE;
					$response = array(
						"status" => 'success',
						"imageName" => $data['file_name'],
						"width" => $data['image_width'],
						"height" => $data['image_height']
				    );
			}
		}
		return $response;
	}

	public function getAvaliableUserList($useridarray){
       	$this->db->select('*');
        $this->db->from('users');
        $this->db->where('role_id!=',1);
    	$this->db->where_not_in('id',$useridarray);
        $this->db->where('status','Active');
        $query=$this->db->get(); 
        return $query->result();
    }

    public function GetTestimonialDetails(){
	       $this->db->select('users.user_id as uid, users.full_name, users.user_pic, rating.*')
	         ->from('rating')
	         ->join('users', 'users.user_id = rating.user_id');
			$result = $this->db->get();
	        return $result->result();
	}

	// public function getAvaliableDriverList($driverArr){
 //       	$this->db->select('*');
 //        $this->db->from('users');
 //        $this->db->where('role_id',3);
 //    	$this->db->where_not_in('id',$driverArr);
 //        $this->db->where('status','Active');
 //        $query=$this->db->get(); 
 //        return $query->result();
 //    }


    public function uniqueIdExist($uniqueId){
        $this->db->select('*');
		$this->db->where('unique_id',$uniqueId);
		$query =$this->db->get('users');
        if($query->num_rows() > 0){
            return true;
		}else {
            return false;
		}
	}
  

  	function customquery($sql){
			$data = $this->db->query($sql);
			
			if($data->num_rows() > 0){
				return $data->result();
			}
			return false;
		}
  	/* Get table record with where condion*/
	function GetTabDataWhere($tbnm, $condition = array(), $data="*", $limit="", $offset=""){

			$this->db->select($data);
			$this->db->from($tbnm);
			$this->db->where($condition);

			If($limit != ''){
				$this->db->limit($limit, $offset);		
			}

			$query = $this->db->get();

			if ($query->num_rows() > 0) {

				return $query->result_array();

			} else {

				return false;
			}
	}

	// Get Single Row
	function GetSingleRow($tbnm, $field, $condition){
		$res = $this->db->select($field)->get_where($tbnm, $condition)->row();
		return $res;
	}

	public function getUserDetail($id=''){ 
        if(!empty($id)){
            $userInfo =  $this->getDataFromTabel('users', '*', array('user_id'=> $id));
           // $userInfo=!empty($userInfo) ? $userInfo[0] : "";
            if(!empty($userInfo)){
            	return $userInfo;
            }else{
            	return false;
            }
        }else{
            return false;
        }
    }

    public function GetTabDataWhereAll($table='', $condition = array(), $field='*', $orderBy='', $order='ASC', $limit="", $offset=""){
		
		$table=$table;
		$this->db->select($field);
		$this->db->from($table);
		 
		if(is_array($condition) && count($condition)){
			/* $orderBy treat as where condition if $orderBy is array  */
			$this->db->where($condition);
		}
		
		if(!empty($orderBy)){  
			$this->db->order_by($orderBy, $order);
		}

		if($limit != ''){
			$this->db->limit($limit, $offset);		
		}
		
		$query = $this->db->get();
		
		$result = $query->result_array();
		if(!empty($result)){
			return 	$result;
		}
		else{
			return FALSE;
		}
	}

	public function CustomCommonQuery($que){
		if(!empty($que)){
			$query 	= $this->db->query($que);
			$res 	= $query->result_array();
			return $res;
		}else{
			return false;
		}

	}

	public function DeleteAllRow($que){
		if(!empty($que)){
			$query 	= $this->db->query($que);
			return $query;
		}else{
			return false;
		}
		
	}
	public function join2table($bid, $limit){
		$this->db->select('u.full_name, u.user_pic, b.busi_user');
		$this->db->from('business as b');
		$this->db->join('users as u', 'b.busi_user = u.user_id','inner');
		$this->db->where('b.busi_id',$bid);
		if($limit >0){
			$this->db->limit($limit);
		}
		$query = $this->db->get();
		 return $query->result_array();
	}

	/* User section function */
	public function purchaseAjaxlist($isCount=false,$uid=0,$start=0,$stop=0, $column_name='',$order='') {
        if(!empty($column_name) && $column_name=='full_name' ){
            $orderby_name = 'full_name';
        }else if(!empty($column_name) && $column_name=='overview' ){
            $orderby_name = 'overview';
        }else if(!empty($column_name) && $column_name=='purchase_donation_amount' ){
            $orderby_name = 'purchase_donation_amount';
        }else if(!empty($column_name) && $column_name=='pay_amount' ){
            $orderby_name = 'pay_amount';
        }else {
            $order='desc';
            $orderby_name = 'purchase_id';
        }
        
        $search = $this->input->get('search');

        $this->db->select('payment.pay_id, payment.pay_trans_id,payment.pay_amount,payment.pay_date,service.overview,users.full_name, package_purchase.purchase_package, package_purchase.purchase_donation,package_purchase.purchase_donation_amount,package_purchase.purchase_service_user');
        $this->db->from('package_purchase');
        $this->db->join('users','package_purchase.purchase_user = users.user_id','inner');
        $this->db->join('service','package_purchase.purchase_service = service.service_id','inner');
        $this->db->join('payment','package_purchase.purchase_pay_id = payment.pay_id','inner');
        $this->db->where('purchase_user',$uid);
      	$this->db->group_by('package_purchase.purchase_pay_id');
		
        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }


    //--------search text-box value start--------------------------//
        if(!empty($search['value'])){
           $search_info = trim($search['value']);
           $this->db->where('(`full_name` LIKE "%'.$search_info.'%" OR `pay_date` LIKE "%'.$search_info.'%" OR `pay_trans_id` LIKE "%'.$search_info.'%" OR `pay_amount` LIKE "%'.$search_info.'%" OR `purchase_donation` LIKE "%'.$search_info.'%" OR `purchase_donation_amount` LIKE "%'.$search_info.'%" OR `purchase_package` LIKE "%'.$search_info.'%" OR `purchase_service` LIKE "%'.$search_info.'%" )',NUll);
        }
        //--------search text-box value end
        if($stop!=0) { 
           $this->db->limit($stop,$start);
        }
        $query=$this->db->get(); 
        if($isCount){
             $returnData = $query->num_rows();
        }else{
            $returnData = $query->result();
        }
        return $returnData;
    }


    /* Upload video file and create video thumbnail  */

	public function allupload($mediaid){
		
		$this->db->select('*');
		$this->db->from('media');
		$this->db->where(array("media_id"=>$mediaid));
		$query = $this->db->get();
		//echo $this->db->last_query();
		$result = $query->row();
		$media_extension = $result->media_extension;
		
		$media_name = $result->media_name;
		$media_snap = $result->media_snap;
		$ext = explode('.', $media_name);
		$link = 'uploads/business/'.$media_name;
		$link2 = 'uploads/business/';
		if(file_exists($link)){
		if($media_extension == 1) { 

        $config['image_library'] = 'gd2'; 
        $config['source_image'] = $link;
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = TRUE;
        $config['width']         = 400;
        $config['height']       = 300;
        $st  = json_encode($this->load->library('image_lib', $config));

        $this->image_lib->clear();
    	$this->image_lib->initialize($config);
    	$this->image_lib->resize();
       
    	}
               
        if($media_extension == 2){
            $size = '300X200';
            $path = $link2;
            $video = $path . escapeshellcmd($media_name);
            $cmd = "ffmpeg -i $video 2>&1";
            $second = 1;
            if (preg_match('/Duration: ((\d+):(\d+):(\d+))/s', `$cmd`, $time)) {
                $total = ($time[2] * 3600) + ($time[3] * 60) + $time[4];
                $second = rand(1, ($total - 1));
            }
            $image  = $path.strstr($media_name ,'.',true).'_thumb.jpg';
           // $image  = 'uploads/test_video/random_name.jpg';
            $cmd = "ffmpeg -i $video -deinterlace -an -ss $second -s $size -t 00:00:01 -r 1 -y -vcodec mjpeg -f mjpeg $image 2>&1";
            $do = `$cmd`;

            $video_arr['original_url'] = $media_name;
            $thumb_url = strstr($media_name ,'.',true).'_thumb.jpg';

                $this->db->where(array("media_id"=>$mediaid));
                $this->db->update('media' , array("media_snap"=>$thumb_url));
        }    
        }   else {

        	$this->db->delete('media', array('media_id'=>$mediaid));
			$this->db->affected_rows();
        }         
                /*************************************/
           
        
        return true;
    }


    public function alluploadadmin($mediaid,$type,$video=''){
		

    	if($type != 3){
    	if($type == 1){
		$table = 'article';
		$folder = 'uploads/articleimage/';
		} elseif($type == 2){
		$table = 'banner';
		$folder = 'uploads/banner/';	
		}

		$this->db->select('*');
		$this->db->from($table);
		$this->db->where(array("id"=>$mediaid));

		$query = $this->db->get();
		$result = $query->row();
		
		if($type == 1){
		$media_name = $result->article_image;
		} elseif($type == 2){
		$media_name = $result->banner;
		}
		
		$ext = explode('.', $media_name);
		$link = $folder.$media_name;
		$link2 = $folder;
		if(file_exists($link)){
        $config['image_library'] = 'gd2'; 
        $config['source_image'] = $link;
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = TRUE;
        $config['width']         = 400;
        $config['height']       = 300;
        $st  = json_encode($this->load->library('image_lib', $config));

        $this->image_lib->clear();
    	$this->image_lib->initialize($config);
    	$this->image_lib->resize();
       	}    else {
    	$this->db->delete($table, array('id'=>$mediaid));
		$this->db->affected_rows();
        } 
    }
               
        if($video == 1){
        	$this->db->select('*');
			$this->db->from('banner');
			$this->db->where(array("id"=>$mediaid));
			$query = $this->db->get();
			$result = $query->row();
			$media_name = $result->banner;

            $size = '300X200';
            $path = 'uploads/banner/';
            $video = $path . escapeshellcmd($media_name);
            $cmd = "ffmpeg -i $video 2>&1";
            $second = 1;
            if (preg_match('/Duration: ((\d+):(\d+):(\d+))/s', `$cmd`, $time)) {
                $total = ($time[2] * 3600) + ($time[3] * 60) + $time[4];
                $second = rand(1, ($total - 1));
            }
            $image  = $path.strstr($media_name ,'.',true).'_thumb.jpg';
           // $image  = 'uploads/test_video/random_name.jpg';
            $cmd = "ffmpeg -i $video -deinterlace -an -ss $second -s $size -t 00:00:01 -r 1 -y -vcodec mjpeg -f mjpeg $image 2>&1";
            $do = `$cmd`;

            $video_arr['original_url'] = $media_name;
            $thumb_url = strstr($media_name ,'.',true).'_thumb.jpg';

                $this->db->where(array("id"=>$mediaid));
                $this->db->update('banner' , array("thumb"=>$thumb_url));
        } 
                
                /*************************************/
         
        
        return true;
    }

    function join_two_groupy($tbl1,$tbl2 ,$field1,$field2,$where,$select,$groupby){   
      $this->db->select($select);
      $this->db->from($tbl1);
      $this->db->join($tbl2, $tbl1.'.'.$field1.'='.$tbl2.'.'.$field2);
      $this->db->group_by($groupby); 
      $this->db->where($where);   
      $getdata  = $this->db->get();
      $num = $getdata->num_rows();
      if($num> 0){ 
        $arr=$getdata->result();
        foreach ($arr as $rows){
          $data[] = $rows;
        }
        $getdata->free_result();
        return $data;
        } else{ 
        return false;
      }
    }


    function base64upload($imagecode,$servicetype,$id){
    	 $dirPath = 'uploads/business/';
    	$create_date = strtotime(date("Y-m-d h:i:s"));
		$image_parts = explode(";base64,", $imagecode);
	    $image_type_aux = explode("image/", $image_parts[0]);
	    $image_type = $image_type_aux[1];
	    $image_base64 = base64_decode($image_parts[1]);
	    $img = explode(',', $imagecode);
	    $ini =substr($img[0], 11);
	    $type = explode(';', $ini);
	    $filename = uniqid() . '.'.$type[0];
	    $file = $dirPath . $filename;
	    $reslt = file_put_contents($file, $image_base64);
	    $insert_arr = array('media_name' =>$filename ,'media_business' => $id ,'media_extension' => '1', 'media_service' => $servicetype ,'media_category' => '1', 'media_status' => '1' ,'media_date' => $create_date);
	    $newuserid = $this->insertDataFromTable('media', $insert_arr);
	    return $resultthumb = $this->allupload($newuserid);

    }

}


