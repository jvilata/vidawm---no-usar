<?php
/**
 * This example shows sending a message using PHP's mail() function.
 */

require 'PHPMailer/PHPMailerAutoload.php';
class SendMail { 
		protected $SMTP_SERVER = "mail.vidawm.com";
		protected $FROM_USER="vjulia@vidawm.com";
		protected $mail=NULL;
		
        public function __construct(){
			//Create a new PHPMailer instance
			$this->mail = new PHPMailer;
	        $this->mail->CharSet ='UTF-8';

			$this->mail->IsSMTP();
			$this->mail->Host = $this->SMTP_SERVER;
		//	$mail->SMTPSecure = 'tls';
			$this->mail->SMTPAuth = true;
			$this->mail->SMTPKeepAlive = true; // SMTP connection will not close after each email sent, reduces SMTP overhead
			$this->mail->Port = 26; //587;
		//	$this->mail->SMTPDebug=2; // solo para depurar
			$this->mail->Username = 'vjulia@vidawm.com';
			$this->mail->Password = 'v220189';
			$this->FROM_USER=( (isset($_POST['from']) && $_POST['from']!="")?$_POST['from']:$this->FROM_USER);
			$this->mail->setFrom($this->FROM_USER, 'VIDA Wealth Management (app)');
        }
		
		public function procesa() {
			$to=$_POST['to'];
			$subject=$_POST['subject'];
			$body=$_POST['body'];
			$html=(isset($_POST['html'])?$_POST['html']:"");
			$attach=(isset($_POST['attach'])?$_POST['attach']:"");
			if ($_POST['action'] === 'send'||$_POST['action'] === 'sendattach') {
				//Set who the message is to be sent to
			    $addresses = explode(';', $to); // pueden venir varias direcciones separadas por ;
			    foreach ($addresses as $address) {
			        $this->mail->AddAddress(trim($address));
			    }
				//$this->mail->addAddress($to);
				
				//Set the subject line
				$this->mail->Subject = $subject;
				if ($html!="") {
					//Read an HTML message body from an external file, convert referenced images to embedded,
					//convert HTML into a basic plain-text alternative body
					$this->mail->msgHTML(file_get_contents($html), dirname(__FILE__));
				} else $this->mail->msgHTML($body);
				//Replace the plain text body with one created manually
				if ($_POST['action'] === 'sendattach') {
				    $this->mail->addAttachment($_FILES['attach']['tmp_name'], $_FILES['attach']['name']);
				} else {
				    if ($attach!="") {
					    $this->mail->addAttachment($attach);
				    }
				}
				//send the message, check for errors
				if (!$this->mail->send()) {
					$error=$this->mail->ErrorInfo;
					echo  '{"success": "ko. $error"}' ;
				} else {
					echo '{"success": "ok"}';
				} 

			}
		}
		
		public function envia($action,$to,$to2,$subject,$body,$html,$attach) {
		    
		    if ($action === 'send') {
		        //Set who the message is to be sent to
//		        $this->mail->addAddress($to);
		        $addresses = explode(';', $to); // pueden venir varias direcciones separadas por ;
		        foreach ($addresses as $address) {
		            $this->mail->AddAddress(trim($address));
		        }
		        
		        if ($to2!="") {
// 		            $this->mail->addAddress($to2);
		            $addresses = explode(';', $to2); // pueden venir varias direcciones separadas por ;
		            foreach ($addresses as $address) {
		                $this->mail->AddCC(trim($address));
		            }
		        }
		        //Set the subject line
	            $this->mail->Subject = $subject;
	            if ($html!="") {
	                //Read an HTML message body from an external file, convert referenced images to embedded,
	                //convert HTML into a basic plain-text alternative body
	                $this->mail->msgHTML(file_get_contents($html), dirname(__FILE__));
	            } else $this->mail->msgHTML($body);
		            //Replace the plain text body with one created manually
		        if ($attach!="") {
		                $this->mail->addAttachment($attach);
		        }
	            //send the message, check for errors
	            if (!$this->mail->send()) {
	                $error=$this->mail->ErrorInfo;
	                echo  "{'success':'ko. $error'}" ;
	            } else {
	                echo "{'success':'ok'}";
	            }
	            
		    }
		}
		
}

?>		
		
