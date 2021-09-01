<?php
try {
session_start();

require_once("onedrive.php");

//$dirbase=$_SERVER['DOCUMENT_ROOT'].'/privado/uploads/';// carpeta tmp para facturas generadas
$VIDAOnedrive= new VIDAOneDrive("downloadFactura","FACTURAS",$_REQUEST['empresa'],$_REQUEST['nombrePDF'],$_REQUEST['carpeta'],$_REQUEST['destino'],$_REQUEST['asunto'],$_REQUEST['texto'],$_REQUEST['destinoCopia']);
}
catch (Exception $e) {
    echo "<script>alert(' Error:".$e->getMessage()."');</script>";
    
}

?>