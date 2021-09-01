<?php

	require_once("pdf_invoice_class.php");

	// Initiiate Library
	$api = new pdf_invoice_class();

	$api->main_imprimirFactura($_REQUEST['id'],$_REQUEST['aDisco']); // no imprime a disco
  
?>