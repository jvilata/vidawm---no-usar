<?php
session_start();

require_once("onedrive.php");
$VIDAOnedrive= new VIDAOneDrive("recorrerCarpeta",$_REQUEST['tipo'],$_REQUEST['carpeta'],$_REQUEST['empresa'],$_REQUEST['codEmpresa']);

?>