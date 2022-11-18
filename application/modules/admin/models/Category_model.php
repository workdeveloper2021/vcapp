<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Category_model extends CI_model{
    public function __construct(){
        parent::__construct();
    }
    
    public function usersCsvList(){
      
        $this->db->select('user.*,user_role.role_id');
        $this->db->from('user');
        $this->db->join('user_role','user.id = user_role.user_id'); 
        $this->db->where('user_role.role_id ','3');

        $query=$this->db->get(); 
        $returnData = $query->result_array();
        return $returnData;
    }
    
    
    
    
    
    public function categoryAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc'){
        if(!empty($column_name) && $column_name=='category_name' ){
            $orderby_name = 'category_name';
        }else if(!empty($column_name) && $column_name=='category_type' ){
            $orderby_name = 'category_type';
        }else if(!empty($column_name) && $column_name=='price' ){
            $orderby_name = 'price';
        }else if(!empty($column_name) && $column_name=='no_of_days' ){
            $orderby_name = 'no_of_days';
        }else if(!empty($column_name) && $column_name=='status' ){
            $orderby_name = 'status';
        }else{
               $orderby_name = 'id';
        }
        $search = $this->input->get('search');
        $this->db->select("manage_category.*,
                            CASE WHEN category_type = '1' THEN category_type END AS skills,
                            CASE WHEN category_type = '2' THEN category_type END AS 'business category',
                            CASE WHEN category_type = '3' THEN category_type END AS products",FALSE);
        $this->db->from('manage_category');
        $this->db->where('category_parent','0');
        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }
        //--------search text-box value start
         if(!empty($search['value'])){
            $search_info = trim($search['value']);
            $this->db->where('(`category_name` LIKE "%'.$search_info.'%" OR `category_type` LIKE "%'.$search_info.'%" OR `price` LIKE "%'.$search_info.'%" OR `no_of_days` LIKE "%'.$search_info.'%" OR `status` LIKE "%'.$search_info.'%" )',NUll);
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
            //$this->session->set_userdata('search_data',$returnData);
            $this->session->set_userdata('search_data',$query->result_array());
        }
         // echo $this->db->last_query();die;
        return $returnData;
    }

    
    
    public function subcategoryAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc',$catid){
        if(!empty($column_name) && $column_name=='category_name' ){
            $orderby_name = 'category_name';
        }else if(!empty($column_name) && $column_name=='category_type' ){
            $orderby_name = 'category_type';
        }else if(!empty($column_name) && $column_name=='price' ){
            $orderby_name = 'price';
        }else if(!empty($column_name) && $column_name=='no_of_days' ){
            $orderby_name = 'no_of_days';
        }else if(!empty($column_name) && $column_name=='status' ){
            $orderby_name = 'status';
        }else{
               $orderby_name = 'id';
        }
        $search = $this->input->get('search');
        $this->db->select("manage_category.*,
                            CASE WHEN category_type = '1' THEN category_type END AS skills,
                            CASE WHEN category_type = '2' THEN category_type END AS 'business category',
                            CASE WHEN category_type = '3' THEN category_type END AS products",FALSE);
        $this->db->from('manage_category');
        $this->db->where('category_parent',$catid);
        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }
        //--------search text-box value start
         if(!empty($search['value'])){
            $search_info = trim($search['value']);
            $this->db->where('(`category_name` LIKE "%'.$search_info.'%" OR `category_type` LIKE "%'.$search_info.'%" OR `price` LIKE "%'.$search_info.'%" OR `no_of_days` LIKE "%'.$search_info.'%" OR `status` LIKE "%'.$search_info.'%" )',NUll);
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
            //$this->session->set_userdata('search_data',$returnData);
            $this->session->set_userdata('search_data',$query->result_array());
        }
         // echo $this->db->last_query();die;
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
    

}
?>
