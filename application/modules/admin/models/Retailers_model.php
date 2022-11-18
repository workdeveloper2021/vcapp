<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Retailers_model extends CI_model{
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

    public function retailersAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc',$cid){
        if(!empty($column_name) && $column_name=='name' ){
            $orderby_name = 'name';
        }else if(!empty($column_name) && $column_name=='email' ){
            $orderby_name = 'email';
        }else if(!empty($column_name) && $column_name=='country' ){
            $orderby_name = 'manage_comapny_location.location';
        }else if(!empty($column_name) && $column_name=='city' ){
            $orderby_name = 'city';
        }else if(!empty($column_name) && $column_name=='status' ){
            $orderby_name = 'status';
        }else{
               $orderby_name = 'id';
        }
        $search = $this->input->get('search');
        $this->db->select('company_retailers.*, manage_comapny_location.location as country_name');
        $this->db->from('company_retailers');
        $this->db->join('manage_comapny_location','manage_comapny_location.id = company_retailers.country'); 
        // $this->db->group_by('user_role.user_id'); 
        $this->db->where('company_retailers.company_id ',$cid);
        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }
        //--------search text-box value start
         if(!empty($search['value'])){
            $search_info = trim($search['value']);
            $this->db->where('(`name` LIKE "%'.$search_info.'%" OR `email` LIKE "%'.$search_info.'%"  OR `manage_comapny_location.location` LIKE "%'.$search_info.'%"  OR `city` LIKE "%'.$search_info.'%" )',NUll);
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
