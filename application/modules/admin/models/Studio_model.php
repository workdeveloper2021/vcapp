<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Studio_model extends CI_model{
    public function __construct(){
        parent::__construct();
    }
    public function ownerAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc') {

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
        $this->db->select('user.*,user_role.role_id');
        $this->db->from('user');
        $this->db->join('user_role','user.id = user_role.user_id'); 
        $this->db->where('user_role.role_id ','2');
        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }
        //--------search text-box value start
        if(!empty($search['value'])){
          $search_info = trim($search['value']);
           $this->db->where('(`name` LIKE "%'.$search_info.'%" OR `lastname` LIKE "%'.$search_info.'%" OR `email` LIKE "%'.$search_info.'%" OR `mobile` LIKE "%'.$search_info.'%" OR `country` LIKE "%'.$search_info.'%" OR `state` LIKE "%'.$search_info.'%" OR `city` LIKE "%'.$search_info.'%" OR `address` LIKE "%'.$search_info.'%" )',NUll);
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
    public function businessAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc'){
        if(!empty($column_name) && $column_name=='name' ){
            $orderby_name = 'name';
        }
        else if(!empty($column_name) && $column_name=='business_name' ){
            $orderby_name = 'business_name';
        }else if(!empty($column_name) && $column_name=='primary_email' ){
            $orderby_name = 'primary_email';
        }else if(!empty($column_name) && $column_name=='business_phone' ){
            $orderby_name = 'business_phone';
        }
        else if(!empty($column_name) && $column_name=='country' ){
            $orderby_name = 'country';
        }else if(!empty($column_name) && $column_name=='state' ){
            $orderby_name = 'state';
        }else if(!empty($column_name) && $column_name=='city' ){
            $orderby_name = 'city';
        }
        else{
               $orderby_name = 'id';
        }

        $search = $this->input->get('search');
        $this->db->select('u.name,u.lastname,b.*'); 
        $this->db->from(TABLE_BUSINESS.' as b');
        $this->db->join(TABLE_USERS.' as u','u.id = b.user_id');
        //--------search text-box value start
        if(!empty($search['value'])){
          $search_info = trim($search['value']);
           $this->db->where('(`u.name` LIKE "%'.$search_info.'%" OR `u.lastname` LIKE "%'.$search_info.'%" OR `b.primary_email` LIKE "%'.$search_info.'%" OR `b.business_name` LIKE "%'.$search_info.'%" OR `b.business_phone` LIKE "%'.$search_info.'%" OR `b.country` LIKE "%'.$search_info.'%" OR `b.state` LIKE "%'.$search_info.'%" OR `b.city` LIKE "%'.$search_info.'%" OR `b.address` LIKE "%'.$search_info.'%" )',NUll);
        }
        //--------search text-box value end
        if($stop!=0) { 
           $this->db->limit($stop,$start);
        }
        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }
        $query=$this->db->get(); 
         if($isCount){
             $returnData = $query->num_rows();
        }else{
            $returnData = $query->result(); 
            $this->session->set_userdata('search_data',$query->result_array());
        }
        return $returnData;
    }
    public function get_business_details($business_id=''){
        $this->db->select('u.name,u.lastname,b.*'); 
        $this->db->from(TABLE_BUSINESS.' as b');
        $this->db->join(TABLE_USERS.' as u','u.id = b.user_id');
        $this->db->where('b.id ',decode($business_id));
        $query=$this->db->get(); 
        $returnData = $query->result_array(); 
        return $returnData;
    }


    public function get_business_trainers_ids($business_id=''){

        $this->db->select('a.id'); 
        $this->db->from(TABLE_USERS.' as a');
        $this->db->join(TABLE_BUSINESS_TRAINER_RELATIONSHIP.' as b','a.id = b.user_id');
        $this->db->where('b.business_id',decode($business_id));
        $query=$this->db->get(); 
        $returnData = $query->result_array(); 
        return $returnData;
    }



    
    public function businessClassesAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc') {
         if(!empty($column_name) && $column_name=='class_name' ){
            $orderby_name = 'class_name';
        }else if(!empty($column_name) && $column_name=='start_date' ){
            $orderby_name = 'start_date';
        }else if(!empty($column_name) && $column_name=='end_date' ){
            $orderby_name = 'end_date';
        }else if(!empty($column_name) && $column_name=='from_time' ){
            $orderby_name = 'from_time';
        }else if(!empty($column_name) && $column_name=='to_time' ){
            $orderby_name = 'to_time';
        }else if(!empty($column_name) && $column_name=='class_category' ){
            $orderby_name = 'class_category';
        }else if(!empty($column_name) && $column_name=='duration' ){
            $orderby_name = 'duration';
        }else if(!empty($column_name) && $column_name=='capacity' ){
            $orderby_name = 'capacity';
        }else if(!empty($column_name) && $column_name=='description' ){
            $orderby_name = 'description';
        }else if(!empty($column_name) && $column_name=='location' ){
            $orderby_name = 'location';
        }else if(!empty($column_name) && $column_name=='status' ){
            $orderby_name = 'status';
        }else{
               $orderby_name = 'id';
        }
        $search = $this->input->get('search');



        $bid = $this->session->userdata('bid');

        $this->db->select('a.*, b.category_name as class_category'); 
        $this->db->from(TABLE_BUSINESS_CLASS.' as a');
        $this->db->join(TABLE_BUSINESS_CATEGORY.' as b','b.id = a.class_type');
        $this->db->where('a.business_id',$bid);


        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }

        //--------search text-box value start 




        if(!empty($search['value'])){
            $search_info = trim($search['value']);
           $this->db->where('(`class_name` LIKE "%'.$search_info.'%" OR `category_name` LIKE "%'.$search_info.'%" OR `duration` LIKE "%'.$search_info.'%" OR `capacity` LIKE "%'.$search_info.'%" OR `description` LIKE "%'.$search_info.'%" OR `location` LIKE "%'.$search_info.'%" OR a.status LIKE "%'.$search_info.'%" )',NUll);
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


        public function businessClassesDetailAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc') {
         if(!empty($column_name) && $column_name=='class_name' ){
            $orderby_name = 'class_name';
        }else{
               $orderby_name = 'id';
        }
        $search = $this->input->get('search');



        $bid = $this->session->userdata('cid');

        $this->db->select('cst.*, bc.class_name , bl.location_name,  u.name, u.lastname, mwd.week_name ' ); 
        $this->db->from(TABLE_CLASS_SCHEDULING_TIME.' as cst');
        $this->db->join(TABLE_BUSINESS_CLASS.' as bc','bc.id = cst.class_id', 'LEFT');
        $this->db->join(TABLE_BUSINESS_LOCATION.' as bl','bl.business_id = cst.business_id', 'LEFT');
        $this->db->join(TABLE_MANAGE_WEEK_DAYS.' as mwd','mwd.id = cst.day_id', 'LEFT');
        $this->db->join(TABLE_USERS.' as u','u.id = cst.instructor_id', 'LEFT');
        $this->db->where('cst.class_id',$bid);


        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }

        //--------search text-box value start 




        if(!empty($search['value'])){
            $search_info = trim($search['value']);
           $this->db->where('(`bc.class_name` LIKE "%'.$search_info.'%" OR `bl.location_name` LIKE "%'.$search_info.'%" OR `u.name` LIKE "%'.$search_info.'%" OR `u.lastname` LIKE "%'.$search_info.'%" OR `mwd.week_name` LIKE "%'.$search_info.'%"  )',NUll);
        }

        //--------search text-box value end
        if($stop!=0) { 
           $this->db->limit($stop,$start);
        }
        $this->db->group_by('cst.id'); 
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

    public function businessClassesAttendeeAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc') {
         if(!empty($column_name) && $column_name=='class_name' ){
            $orderby_name = 'class_name';
        }else{
               $orderby_name = 'id';
        }
        $search = $this->input->get('search');



        $bid = $this->session->userdata('sid');

        $this->db->select('ua.*, bc.class_name ,  u.name, u.lastname ' ); 
        $this->db->from(TABLE_USER_ATTENDANCE.' as ua');
        $this->db->join(TABLE_BUSINESS_CLASS.' as bc','bc.id = ua.service_id', 'LEFT');
        $this->db->join(TABLE_USERS.' as u','u.id = ua.user_id', 'LEFT');
        $this->db->where('ua.schedule_id',$bid);


        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }

        //--------search text-box value start 




        if(!empty($search['value'])){
            $search_info = trim($search['value']);
           $this->db->where('(`bc.class_name` LIKE "%'.$search_info.'%" OR `u.name` LIKE "%'.$search_info.'%" OR `u.lastname` LIKE "%'.$search_info.'%"  )',NUll);
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
         //echo $this->db->last_query();die;
        return $returnData;
    }



    
    public function businessWorkshopsAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc') {
         if(!empty($column_name) && $column_name=='workshop_name' ){
            $orderby_name = 'workshop_name';
        }else if(!empty($column_name) && $column_name=='workshop_id' ){
            $orderby_name = 'workshop_id';
        }else if(!empty($column_name) && $column_name=='workshop_type' ){
            $orderby_name = 'workshop_type';
        }else if(!empty($column_name) && $column_name=='from_time' ){
            $orderby_name = 'from_time';
        }else if(!empty($column_name) && $column_name=='to_time' ){
            $orderby_name = 'to_time';
        }else if(!empty($column_name) && $column_name=='start_date' ){
            $orderby_name = 'start_date';
        }else if(!empty($column_name) && $column_name=='end_date' ){
            $orderby_name = 'end_date';
        }else if(!empty($column_name) && $column_name=='no_of_days' ){
            $orderby_name = 'no_of_days';
        }else if(!empty($column_name) && $column_name=='duration' ){
            $orderby_name = 'duration';
        }else if(!empty($column_name) && $column_name=='capacity' ){
            $orderby_name = 'capacity';
        }else if(!empty($column_name) && $column_name=='description' ){
            $orderby_name = 'description';
        }else if(!empty($column_name) && $column_name=='location' ){
            $orderby_name = 'location';
        }else if(!empty($column_name) && $column_name=='status' ){
            $orderby_name = 'status';
        }else{
               $orderby_name = 'id';
        }
        $search = $this->input->get('search');



        $bid = $this->session->userdata('bid');

        $this->db->select('a.*, b.category_name as workshop_category'); 
        $this->db->from(TABLE_BUSINESS_WORKSHOP.' as a');
        $this->db->join(TABLE_BUSINESS_CATEGORY.' as b','b.id = a.workshop_type');
        $this->db->where('a.business_id',$bid);


        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }

        //--------search text-box value start 




        if(!empty($search['value'])){
            $search_info = trim($search['value']);
           $this->db->where('(`workshop_name` LIKE "%'.$search_info.'%" OR `workshop_id` LIKE "%'.$search_info.'%" OR b.category_name LIKE "%'.$search_info.'%" OR `no_of_days` LIKE "%'.$search_info.'%" OR `duration` LIKE "%'.$search_info.'%" OR `capacity` LIKE "%'.$search_info.'%" OR `description` LIKE "%'.$search_info.'%" OR `location` LIKE "%'.$search_info.'%" OR a.status LIKE "%'.$search_info.'%" )',NUll);
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




    
    public function classAttendenceUsersAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc') {
         
        if(!empty($column_name) && $column_name=='name' ){
            $orderby_name = 'name';
        }else if(!empty($column_name) && $column_name=='class_name' ){
            $orderby_name = 'class_name';
        }else if(!empty($column_name) && $column_name=='from_time' ){
            $orderby_name = 'from_time';
        }else if(!empty($column_name) && $column_name=='to_time' ){
            $orderby_name = 'to_time';
        }else if(!empty($column_name) && $column_name=='status' ){
            $orderby_name = 'user_attendance.status';
        }else{
            $orderby_name = 'id';
        }
        $search = $this->input->get('search');

        $business_id = $this->session->userdata('bid');
        $class_id = $this->session->userdata('cid');

        $url=base_url().'uploads/user/';
        $this->db->select("user.id,user.name,user.lastname,CONCAT('" . $url . "', user.profile_img) as profile_img,user.gender,user.date_of_birth,business_class.class_name,business_class.from_time,business_class.to_time,user_attendance.status as attendance_status,user_attendance.user_id,user_attendance.service_id");
        $this->db->from('business_class');
        $this->db->join('user_attendance','user_attendance.service_id = business_class.id');
        $this->db->join('user','user.id = user_attendance.user_id');
        $where="business_class.business_id='".$business_id."' AND business_class.id='".$class_id."' AND user_attendance.service_type='1' AND user_attendance.signup_status='1' AND  business_class.status='Active'";
        $this->db->where($where);

        // if($checkedin_type=='1'){
        //     $wh="user_attendance.status='checkin' AND DATE(FROM_UNIXTIME(user_attendance.create_dt))='".$date."'";
        //     $this->db->where($wh);
        // }elseif($checkedin_type=='2'){
        //     $wh1="user_attendance.status !='checkout' AND user_attendance.status !='checkin' ";
        //     $this->db->where($wh1);
        // }


        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }


        if(!empty($search['value'])){
            $search_info = trim($search['value']);
           $this->db->where('(`name` LIKE "%'.$search_info.'%" OR `class_name` LIKE "%'.$search_info.'%" OR user_attendance.status LIKE "%'.$search_info.'%" )',NUll);
        }


        if($stop!=0) { 
           $this->db->limit($stop,$start);
        }

        $this->db->group_by('user_attendance.user_id');

        // $this->db->order_by("create_dt","DESC");
        $query = $this->db->get();
        //echo $this->db->last_query();die;

        if($isCount){
             $returnData = $query->num_rows();
        }else{
            $returnData = $query->result();
            $this->session->set_userdata('search_data',$query->result_array());
        }
        return  $returnData; 

    }





    
    public function workshopAttendenceUsersAjaxlist($isCount=false,$start=0,$stop=0, $column_name='',$order='desc') {
         
        if(!empty($column_name) && $column_name=='name' ){
            $orderby_name = 'name';
        }else if(!empty($column_name) && $column_name=='workshop_name' ){
            $orderby_name = 'workshop_name';
        }else if(!empty($column_name) && $column_name=='from_time' ){
            $orderby_name = 'from_time';
        }else if(!empty($column_name) && $column_name=='to_time' ){
            $orderby_name = 'to_time';
        }else if(!empty($column_name) && $column_name=='status' ){
            $orderby_name = 'user_attendance.status';
        }else{
            $orderby_name = 'id';
        }
        $search = $this->input->get('search');

        $business_id = $this->session->userdata('bid');
        $cat_id = $this->session->userdata('cid');

        // echo $business_id."__".$cat_id; die;

        $url=base_url().'uploads/user/';
        $this->db->select("user.id,user.name,user.lastname,CONCAT('" . $url . "', user.profile_img) as profile_img,user.gender,user.date_of_birth,business_workshop.workshop_name,business_workshop.from_time,business_workshop.to_time,user_attendance.status as attendance_status,user_attendance.user_id,user_attendance.service_id");
        $this->db->from('business_workshop');
        $this->db->join('user_attendance','user_attendance.service_id = business_workshop.id');
        $this->db->join('user','user.id = user_attendance.user_id');
        $where="business_workshop.business_id='".$business_id."' AND business_workshop.id='".$cat_id."' AND user_attendance.service_type='2' AND user_attendance.signup_status='1' AND  business_workshop.status='Active'";
        $this->db->where($where);

        // if($checkedin_type=='1'){
        //     $wh="user_attendance.status='checkin' AND DATE(FROM_UNIXTIME(user_attendance.create_dt))='".$date."'";
        //     $this->db->where($wh);
        // }elseif($checkedin_type=='2'){
        //     $wh1="user_attendance.status !='checkout' AND user_attendance.status !='checkin' ";
        //     $this->db->where($wh1);
        // }


        if(!empty($orderby_name)){
           $this->db->order_by($orderby_name, $order);
        }


        if(!empty($search['value'])){
            $search_info = trim($search['value']);
           $this->db->where('(`name` LIKE "%'.$search_info.'%" OR `workshop_name` LIKE "%'.$search_info.'%" OR user_attendance.status LIKE "%'.$search_info.'%" )',NUll);
        }


        if($stop!=0) { 
           $this->db->limit($stop,$start);
        }

        $this->db->group_by('user_attendance.user_id');

        // $this->db->order_by("create_dt","DESC");
        $query = $this->db->get();
        // echo $this->db->last_query();die;

        if($isCount){
             $returnData = $query->num_rows();
        }else{
            $returnData = $query->result();
            $this->session->set_userdata('search_data',$query->result_array());
        }
        return  $returnData; 

    }



    public function get_class_attendence_users($business_id='',$class_id='',$checkedin_type='',$date=''){
        
        $url=base_url().'uploads/user/';
        $this->db->select("user.name,user.lastname,CONCAT('" . $url . "', user.profile_img) as profile_img,user.gender,user.date_of_birth,business_class.class_name,business_class.from_time,business_class.to_time,user_attendance.status as attendance_status,user_attendance.user_id,user_attendance.service_id");
        $this->db->from('business_class');
        $this->db->join('user_attendance','user_attendance.service_id = business_class.id');
        $this->db->join('user','user.id = user_attendance.user_id');
        $where="business_class.business_id='".$business_id."' AND business_class.id='".$class_id."' AND user_attendance.service_type='1' AND user_attendance.signup_status='1' AND  business_class.status='Active'";
        $this->db->where($where);

        if($checkedin_type=='1'){
            $wh="user_attendance.status='checkin' AND DATE(FROM_UNIXTIME(user_attendance.create_dt))='".$date."'";
            $this->db->where($wh);
        }elseif($checkedin_type=='2'){
            $wh1="user_attendance.status !='checkout' AND user_attendance.status !='checkin' ";
            $this->db->where($wh1);
        }

        $this->db->group_by('user_attendance.user_id');

        $this->db->order_by("create_dt","DESC");
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if($query->num_rows() > 0) {
            return  $query->result(); 
        }else{  
            return false;
        } 
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
