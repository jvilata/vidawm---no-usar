<?php
/**
 * This example shows sending a message using PHP's mail() function.
 */

require_once ('sendmail_class.php');

if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: ". $_SERVER['HTTP_ORIGIN']); // jv. 19.2.2020 para permitir conectarse desde otras URLs
	} else {
	    header("Access-Control-Allow-Origin: *");
	}
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
	
	if (isset($_POST['action'])) {
		$idserver=new SendMail();
		$idserver->procesa();
	} else if (isset($_REQUEST['destino'])) {
	    $idserver=new SendMail();
	   
	    $idserver->envia("send",$_REQUEST['destino'],$_REQUEST['destinoCopia'],$_REQUEST['asunto'],$_REQUEST['texto'],"","");
	    echo "<script>alert('Enviado mail a ".$_REQUEST['destino']."');window.close();</script>";
	}
?>		
		
