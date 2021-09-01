<?php

	require_once("sepa.php");

	// Initiiate Library
	$api = new sepa();
	if ($_POST['enviarMail']==0) 
	    $api->procesarPago($_POST['codEmpresa'],$_POST['nompdf']); //nompdf es en realidad tipoOPeracion=NOMINA/PAGO
	else 
	    $api->enviarMailsPagosCobros($_POST['codEmpresa'],$_POST['nompdf']);
	    
?>