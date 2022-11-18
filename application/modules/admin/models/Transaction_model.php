<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Transaction_model extends CI_model{
    public function __construct(){
        parent::__construct();
    }
    
    public function trxAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc'){
        if(!empty($column_name) && $column_name=='name' ){
            $orderby_name = 'name';
        }else if(!empty($column_name) && $column_name=='business_name' ){
            $orderby_name = 'business_name';
        }else if(!empty($column_name) && $column_name=='trx_id' ){
            $orderby_name = 'trx_id';
        }else if(!empty($column_name) && $column_name=='amount' ){
            $orderby_name = 'amount';
        }else{
               $orderby_name = 'id';
        }
        $search = $this->input->get('search');
        $this->db->select('t.*,u.id as user_ids,u.name,u.lastname,b.business_id,bu.business_name, bc.class_name, b.service_type, b.tax_amount, b.status');
        $this->db->from(TABLE_TRANSACTIONS.' as t');
        $this->db->join(TABLE_USERS.' as u','u.id = t.user_id');
        $this->db->join(TABLE_USER_BOOKING.' as b','b.transaction_id = t.id');
        $this->db->join(TABLE_BUSINESS.' as bu','bu.id = b.business_id');
        $this->db->join(TABLE_BUSINESS_CLASS.' as bc','bc.id = b.class_id');
        $this->db->where('t.transaction_type',2);
        $this->db->group_by('t.id');
        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        } 
        //--------search text-box value start
        if(!empty($search['value'])){
          $search_info = trim($search['value']);
           $this->db->where('(`u.name` LIKE "%'.$search_info.'%" OR `u.lastname` LIKE "%'.$search_info.'%" OR `bu.business_name` LIKE "%'.$search_info.'%" OR `bc.class_name` LIKE "%'.$search_info.'%" )',NUll);
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
    

}
?>
