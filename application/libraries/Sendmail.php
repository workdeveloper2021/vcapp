<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
    
    /** Fetch the email template as per the post purpose and language
     * Return the email template data from zon_email_template table.
     * **/
     
    class Sendmail {
        
        var $Fromval     = 'no-reply@consagous.in';
        var $FromNameval = 'Warrior';
        var $Hostnameval = 'smtp.hostinger.com';
        var $Portval     = '587';
        var $Usernameval = 'no-reply@consagous.in';
        var $Passwordval = '@Consagous@123@';
        var $isBcc       = false;
        var $bccEmailId  = '';
        
        function sendmailto($emailId,$messageSubject,$messageBody,$attachment='')
        {
            require_once("class.phpmailer.php");
            $mail = new PHPMailer();
           
            //------set value for smtp-----------
            $mail->From     = $this->Fromval;
            $mail->FromName = $this->FromNameval;
            $mail->Hostname = $this->Hostnameval;
            $mail->Host     = $this->Hostnameval;
            $mail->Port     = $this->Portval;
            $mail->Username = $this->Usernameval;
            $mail->Password = $this->Passwordval;
            
            
            $mail->IsSMTP();                 	// set mailer to use SMTP
            $mail->SMTPAuth = true;     		// turn on SMTP authentication
            $mail->CharSet="windows-1251";
            $mail->CharSet="utf-8";
            $mail->WordWrap = 50;      			// set word wrap to 50 characters
            $mail->IsHTML(true);  
            
            //create and send email
            $mail->AddAddress($emailId);	//sender user email id
            if($this->isBcc){
                $mail->AddBCC($this->bccEmailId);	
            }
            
            $mail->Subject = $messageSubject;
            $mail->Body    = $messageBody;
            if($attachment != "")
            {
                $mail->addAttachment($attachment);
            }
            if($mail->send()){
                return true;
            }else{
                return false;
            }
        }
}	
?>

