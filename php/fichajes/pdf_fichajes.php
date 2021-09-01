<?php

	require_once("pdf_fichajes_class.php");

	// Initiiate Library
	$api = new pdf_fichajes_class();

	$api->main_imprimirFichajes(1,$_REQUEST['email'],"");
  
?>