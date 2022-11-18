<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Subscription_model extends CI_model{
    public function __construct(){
        parent::__construct();
    }
    
     public function planAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc') {
        if(!empty($column_name) && $column_name=='plan_name' ){
            $orderby_name = 'plan_name';
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
        $this->db->from(TABLE_SUBSCRIBE_PLAN);
        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }
        //--------search text-box value start
        if(!empty($search['value'])){
          $search_info = trim($search['value']);
           $this->db->where('(`plan_name` LIKE "%'.$search_info.'%" OR `amount` LIKE "%'.$search_info.'%" OR `max_users` LIKE "%'.$search_info.'%" )',NUll);
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
        // echo $this->db->last_query();die;
        return $returnData;
    }

       public function usersAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc') {
        if(!empty($column_name) && $column_name=='plan_name' ){
            $orderby_name = 'plan_name';
        }else if(!empty($column_name) && $column_name=='name' ){
            $orderby_name = 'name';
        }else if(!empty($column_name) && $column_name=='sub_start' ){
            $orderby_name = 'sub_start';
        }else if(!empty($column_name) && $column_name=='sub_end' ){
            $orderby_name = 'sub_end';
        }else if(!empty($column_name) && $column_name=='trx_id' ){
            $orderby_name = 'trx_id';
        }else if(!empty($column_name) && $column_name=='payment_status' ){
            $orderby_name = 'payment_status';
        }else{
               $orderby_name = 'sub_id';
        }
        $search = $this->input->get('search');
        $this->db->select('a.sub_start, a.sub_end, a.sub_id, b.plan_name, c.name, c.lastname, d.trx_id, d.payment_status');
        $this->db->from(TABLE_SUBSCRIPTION.' as a');
        $this->db->join(TABLE_SUBSCRIBE_PLAN.' as b','b.id = a.sub_plan_id');
        $this->db->join(TABLE_USERS.' as c','c.id = a.sub_user_id');
        $this->db->join(TABLE_TRANSACTIONS.' as d','d.id = a.transaction_id');
        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }
        //--------search text-box value start
        if(!empty($search['value'])){
          $search_info = trim($search['value']);
           $this->db->where('(`plan_name` LIKE "%'.$search_info.'%" OR `trx_id` LIKE "%'.$search_info.'%" OR `payment_status` LIKE "%'.$search_info.'%" OR `name` LIKE "%'.$search_info.'%" OR `lastname` LIKE "%'.$search_info.'%" )',NUll);
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

     public function planAjaxlistMonth($isCount=false,$start=0,$stop=0, $column_name='',$order='desc') {
        if(!empty($column_name) && $column_name=='plan_name' ){
            $orderby_name = 'plan_name';
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
        $this->db->from(TABLE_SUBSCRIBE_PLAN);
        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }
        //--------search text-box value start
        if(!empty($search['value'])){
          $search_info = trim($search['value']);
           $this->db->where('(`plan_name` LIKE "%'.$search_info.'%" OR `amount` LIKE "%'.$search_info.'%" OR `max_users` LIKE "%'.$search_info.'%" )',NUll);
        }
        //--------search text-box value end
        $this->db->where('type !=','1');
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
?>
