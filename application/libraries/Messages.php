<?php
class Messages {

/*******************Back End Template ****************************/
		
	function setMessage($message, $type='success'){
		
		$this->CI =& get_instance();
		$data = array('message' =>$message,
					  'messageType'=>$type);
		$this->CI->session->set_userdata($data);
    return true;
	}
	
	function getMessage(){
			$this->CI =& get_instance();
			
			$message=$this->CI->session->userdata('message');
			$messageType=$this->CI->session->userdata('messageType');
			
			$this->CI->session->unset_userdata('message');
			$this->CI->session->unset_userdata('messageType');
			$this->CI->session->set_userdata(array('message' =>'','messageType'=>''));
			
			if(isset($message) && $message!=''){
				return 	'<div class="page-notification '.$messageType.' png_bg messagePosition">
						<a class="close" href="javascript:closeNotification();"></a>
						<div class="message-content" >'.$message.'</div>
						<div class="clear"></div>
						</div>';
			}
			else{
				return ''; 		
			}			
	}
	
/*******************Front End Template ****************************/
	
    function setMessageFront($message, $type='success'){
		
		$this->CI =& get_instance();
		$data = array('message' =>$message,
					  'messageType'=>$type);
		$this->CI->session->set_userdata($data);
    return true;
	}
	
	function getMessageFront(){
			$this->CI =& get_instance();
			$message=$this->CI->session->userdata('message');
			$messageType=$this->CI->session->userdata('messageType');
			if($messageType =='error'){
				$messageType='danger';
			}else if($messageType =='warning'){
				$messageType='warning';
			}
			
			$this->CI->session->unset_userdata('message');
			$this->CI->session->unset_userdata('messageType');
			$this->CI->session->set_userdata(array('message' =>'','messageType'=>''));
			
			if(isset($message) && $message!=''){
				if($messageType=='warning'){
					return "<div class='alert alert-$messageType' id='messages_warning'><strong>$message</strong></div>";
				}else{
					return "<div class='alert alert-$messageType' id='messages'><strong>$message</strong></div>";
				}
				//return 	"<p class='alert alert-$messageType' style='font-size:13px;margin-bottom: inherit;' id='messages'>$message</p>";
					
			}
			else{
				return ''; 		
			}			
	}
}
?>
