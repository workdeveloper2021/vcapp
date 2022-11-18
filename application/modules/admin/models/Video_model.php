<?php

defined('BASEPATH') OR exit('No direct script access allowed'); 

class Video_model extends CI_Model {
    public function __construct(){
        ob_clean();
        parent::__construct();
    }

    public function categoryAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc') {
        if(!empty($column_name) && $column_name=='category_name' ){
            $orderby_name = 'category_name';
        }else if(!empty($column_name) && $column_name=='amount' ){
            $orderby_name = 'amount';
        }else if(!empty($column_name) && $column_name=='max_users' ){
            $orderby_name = 'max_users';
        }else if(!empty($column_name) && $column_name=='create_dt' ){
            $orderby_name = 'create_dt';
        }else{
               $orderby_name = 'id';
        }
        $search = $this->input->get('search');
        $this->db->select('*');
        $this->db->from(TABLE_BUSINESS_CATEGORY);
        //$this->db->join(TABLE_BUSINESS_CATEGORY.' as pc','c.id = pc.category_parent','LEFT');
        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }
        //--------search text-box value start
        if(!empty($search['value'])){
          $search_info = trim($search['value']);
           $this->db->where('(`category_name` LIKE "%'.$search_info.'%" )',NUll);
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
        //echo $this->db->last_query();die;
        return $returnData;
    }

    function updateDataFromTabel($table = '', $data = array(), $field = '', $id = 0) {
      if (empty($table) || !count($data)) {
        return false;
      } else {
        if (is_array($field)) {
          $this->db->where($field);
        } else {
          $this->db->where($field, $id);

        }
        return $this->db->update($table, $data);
      }
    }

     public function skillsAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc') {
        if(!empty($column_name) && $column_name=='name' ){
            $orderby_name = 'name';
        }else if(!empty($column_name) && $column_name=='amount' ){
            $orderby_name = 'amount';
        }else if(!empty($column_name) && $column_name=='max_users' ){
            $orderby_name = 'max_users';
        }else if(!empty($column_name) && $column_name=='create_dt' ){
            $orderby_name = 'create_dt';
        }else{
               $orderby_name = 'id';
        }
        $search = $this->input->get('search');
        $this->db->select('*');
        $this->db->from(TABLE_MANAGE_SKILLS);
        //$this->db->join(TABLE_BUSINESS_CATEGORY.' as pc','c.id = pc.category_parent','LEFT');
        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }
        //--------search text-box value start
        if(!empty($search['value'])){
          $search_info = trim($search['value']);
           $this->db->where('(`name` LIKE "%'.$search_info.'%" )',NUll);
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
        //echo $this->db->last_query();die;
        return $returnData;
    }

    public function videosAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc',$uid) {
        if(!empty($column_name) && $column_name=='category' ){
            $orderby_name = 'mc1.category_name';
        }else if(!empty($column_name) && $column_name=='subcategory' ){
            $orderby_name = 'mc2.category_name';
        }else if(!empty($column_name) && $column_name=='name' ){
            $orderby_name = 'name';
        }else if(!empty($column_name) && $column_name=='description' ){
            $orderby_name = 'description';
        }else if(!empty($column_name) && $column_name=='duration' ){
            $orderby_name = 'duration';
        }else if(!empty($column_name) && $column_name=='is_vimeo' ){
            $orderby_name = 'is_vimeo';
        }else if(!empty($column_name) && $column_name=='status' ){
            $orderby_name = 'status';
        }else{
               $orderby_name = 'id';
        }
        $search = $this->input->get('search');
        $this->db->select('manage_videos.*, mc1.category_name as cat, mc2.category_name as subcat');
        $this->db->from(TABLE_MANAGE_VIDEOS);
        $this->db->join('manage_category as mc1','mc1.id = manage_videos.category_id','LEFT'); 
        $this->db->join('manage_category as mc2','mc2.id = manage_videos.sub_category_id','LEFT'); 
        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }
        //--------search text-box value start

        $this->db->where("(`is_delete` = '0' and `created_by`='".$uid."' )",NUll);
        
        
        if(!empty($search['value'])){
          $search_info = trim($search['value']);
          $this->db->where('(`name` LIKE "%'.$search_info.'%" )',NUll);
          $this->db->or_where('(`description` LIKE "%'.$search_info.'%" )',NUll);
          $this->db->or_where('(`duration` LIKE "%'.$search_info.'%" )',NUll);
          $this->db->or_where('(`is_vimeo` LIKE "%'.$search_info.'%" )',NUll);
        //   $this->db->where('(`status` LIKE "%'.$search_info.'%" )',NUll);
          $this->db->or_where('(`mc1.category_name` LIKE "%'.$search_info.'%" )',NUll);
          $this->db->or_where('(`mc2.category_name` LIKE "%'.$search_info.'%" )',NUll);
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
        //echo $this->db->last_query();die;
        return $returnData;
    }


    public function newsfeedAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc') {
        if(!empty($column_name) && $column_name=='title' ){
            $orderby_name = 'title';
        }else if(!empty($column_name) && $column_name=='url' ){
            $orderby_name = 'url';
        }else if(!empty($column_name) && $column_name=='description' ){
            $orderby_name = 'description';
        }else if(!empty($column_name) && $column_name=='create_dt' ){
            $orderby_name = 'create_dt';
        }else{
               $orderby_name = 'id';
        }
        $search = $this->input->get('search');
        $this->db->select('*');
        $this->db->from(TABLE_MANAGE_NEWSFEED);
        //$this->db->join(TABLE_BUSINESS_CATEGORY.' as pc','c.id = pc.category_parent','LEFT');
        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }
        //--------search text-box value start
        if(!empty($search['value'])){
          $search_info = trim($search['value']);
           $this->db->where('(`title` LIKE "%'.$search_info.'%" )',NUll);
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
        //echo $this->db->last_query();die;
        return $returnData;
    }
}