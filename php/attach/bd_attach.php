<?php
//JV. API para hacer consultas especificas que no se puedan hacer con el API generica lib/bd_api.php
// la URL debe tener el formato:  path/bd_suscritos.php/{operacion}/  , ejem: php/lib/bd_suscritos.php/findSuscritosFilter/

	require_once("bd_attach_class.php");
 
	
	
	// Initiiate Library
	
	$api = new bd_attachs();
	$api->analiza_method();

?>
