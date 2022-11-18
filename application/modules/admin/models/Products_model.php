<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Products_model extends CI_model{
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

    public function productAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc',$cid,$catId){
        if(!empty($column_name) && $column_name=='category' ){
            $orderby_name = 'category_name';
        }else if(!empty($column_name) && $column_name=='product' ){
            $orderby_name = 'product_name';
        }else if(!empty($column_name) && $column_name=='product_unit' ){
            $orderby_name = 'product_unit';
        }else if(!empty($column_name) && $column_name=='detail1' ){
            $orderby_name = 'details1';
        }else if(!empty($column_name) && $column_name=='detail2' ){
            $orderby_name = 'details2';
        }else if(!empty($column_name) && $column_name=='detail3' ){
            $orderby_name = 'details3';
        }else if(!empty($column_name) && $column_name=='status' ){
            $orderby_name = 'status';
        }else{
               $orderby_name = 'id';
        }
        $search = $this->input->get('search');
        $this->db->select('product_list.*, manage_products.product_name, manage_product_categories.category_name');
        $this->db->from('product_list');
        $this->db->join('manage_products','manage_products.id = product_list.product_id'); 
        $this->db->join('manage_product_categories','manage_product_categories.id = product_list.category_id'); 
        // $this->db->group_by('user_role.user_id'); 
        $this->db->where('product_list.company_id',$cid);
        if(!empty($catId) && $catId!=""){
           $this->db->where('product_list.category_id', $catId);
        }
        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }
        //--------search text-box value start
        if(!empty($search['value'])){
            $search_info = trim($search['value']);
            $this->db->where('(`manage_products.product_name` LIKE "%'.$search_info.'%" OR `manage_product_categories.category_name` LIKE "%'.$search_info.'%" OR `product_list.details1` LIKE "%'.$search_info.'%" OR `product_list.details2` LIKE "%'.$search_info.'%" OR `product_list.details3` LIKE "%'.$search_info.'%")',NUll);
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
