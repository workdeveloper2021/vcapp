<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Trainers_model extends CI_model{
    public function __construct(){
        parent::__construct();
    }
    
    public function trainersAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc') {
         if(!empty($column_name) && $column_name=='name' ){
            $orderby_name = 'name';
        }else if(!empty($column_name) && $column_name=='email' ){
            $orderby_name = 'email';
        }else if(!empty($column_name) && $column_name=='mobile' ){
            $orderby_name = 'mobile';
        }else if(!empty($column_name) && $column_name=='country' ){
            $orderby_name = 'country';
        }else if(!empty($column_name) && $column_name=='state' ){
            $orderby_name = 'state';
        }else if(!empty($column_name) && $column_name=='city' ){
            $orderby_name = 'city';
        }else{
               $orderby_name = 'id';
        }
        $search = $this->input->get('search');
        // $this->db->select('*');
        // $this->db->from(TABLE_USERS);
        // $this->db->where('role_id ','4');
        $this->db->select('user.*,user_role.role_id, (SELECT GROUP_CONCAT(name) as skills from manage_skills WHERE manage_skills.id IN (instructor_details.skill)) as skills');
        $this->db->from('user');
        $this->db->join('user_role','user.id = user_role.user_id'); 
        $this->db->join('instructor_details', 'user.id = instructor_details.user_id');
        $this->db->group_by('user_role.user_id'); 
        $this->db->where('user_role.role_id ','4');
        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }

        //--------search text-box value start
        if(!empty($search['value'])){
            $search_info = trim($search['value']);
           $this->db->where('(`name` LIKE "%'.$search_info.'%" OR `lastname` LIKE "%'.$search_info.'%" OR `email` LIKE "%'.$search_info.'%" OR `mobile` LIKE "%'.$search_info.'%" OR `country` LIKE "%'.$search_info.'%" OR `state` LIKE "%'.$search_info.'%" OR `city` LIKE "%'.$search_info.'%" OR `address` LIKE "%'.$search_info.'%" OR `gender` LIKE "%'.$search_info.'%" )',NUll);
            // $search_info = trim($search['value']);
            // $this->db->like("name", $search_info);
            // $this->db->like("lastname", $search_info);
            // $this->db->or_like("email", $search_info);
            // $this->db->or_like("mobile", $search_info);
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
            // $this->session->set_userdata('search_data',$returnData);
            $this->session->set_userdata('search_data',$query->result_array());
        }
        // echo $this->db->last_query();die;
        return $returnData;
    }


    
    public function businessTrainersAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc') {
         if(!empty($column_name) && $column_name=='name' ){
            $orderby_name = 'name';
        }else if(!empty($column_name) && $column_name=='email' ){
            $orderby_name = 'email';
        }else if(!empty($column_name) && $column_name=='mobile' ){
            $orderby_name = 'mobile';
        }else if(!empty($column_name) && $column_name=='country' ){
            $orderby_name = 'country';
        }else if(!empty($column_name) && $column_name=='state' ){
            $orderby_name = 'state';
        }else if(!empty($column_name) && $column_name=='city' ){
            $orderby_name = 'city';
        }else{
               $orderby_name = 'id';
        }
        $search = $this->input->get('search');
        // $this->db->select('*');
        // $this->db->from(TABLE_USERS);
        // $this->db->where('role_id ','4');
        // $this->db->select('user.*,user_role.role_id');
        // $this->db->from('user');
        // $this->db->join('user_role','user.id = user_role.user_id'); 
        // $this->db->where('user_role.role_id ','4');

        // $bid = decode($this->session->userdata('bid'));
        // echo $bid; die();

        $bid = $this->session->userdata('bid');

        $this->db->select('a.*, a.role_id'); 
        $this->db->from(TABLE_USERS.' as a');
        $this->db->join(TABLE_BUSINESS_TRAINER_RELATIONSHIP.' as b','a.id = b.user_id');
        $this->db->join('user_role','a.id = user_role.user_id'); 
        $this->db->where('b.business_id',$bid);
        $this->db->where('user_role.role_id ','4');



        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }

        //--------search text-box value start
        if(!empty($search['value'])){
            $search_info = trim($search['value']);
           $this->db->where('(`name` LIKE "%'.$search_info.'%" OR `lastname` LIKE "%'.$search_info.'%" OR `email` LIKE "%'.$search_info.'%" OR `mobile` LIKE "%'.$search_info.'%" OR `country` LIKE "%'.$search_info.'%" OR `state` LIKE "%'.$search_info.'%" OR `city` LIKE "%'.$search_info.'%" OR `address` LIKE "%'.$search_info.'%" OR `gender` LIKE "%'.$search_info.'%" )',NUll);
            // $search_info = trim($search['value']);
            // $this->db->like("name", $search_info);
            // $this->db->like("lastname", $search_info);
            // $this->db->or_like("email", $search_info);
            // $this->db->or_like("mobile", $search_info);
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
            // $this->session->set_userdata('search_data',$returnData);
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
