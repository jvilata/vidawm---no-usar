<?php


require_once ("/home2/vidawmco/public_html/vidawm.com/privado/php/lib/sendmail_class.php");

class VIDAcron  {
    private $api_sendmail=null;
    
    public function __construct() {
       $this->api_sendmail = new SendMail();
	}
	
	public function prueba($destino,$cc,$asunto,$cuerpo) {
       $this->api_sendmail->envia("send", $destino,$cc,$asunto,$cuerpo, "", "");
	}
}

// main
  $api=new VIDAcron();
  $api->prueba("jvilata@edicom.es","","Esto es una prueba","la fecha es ".date('Y-m-d H:i:s'));
  
?>