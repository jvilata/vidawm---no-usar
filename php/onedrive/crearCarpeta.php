<?php
session_start();

require_once("onedrive.php");

if ($_REQUEST['tipo']=="ENTIDADES") $_REQUEST['empresa']="VIDAWM"; // siempre dirijo a VIDAWM para informacion de ENTIDADES
$estado=(isset($_REQUEST['estado'])?$_REQUEST['estado']:null);
$VIDAOnedrive= new VIDAOneDrive("crearCarpeta",$_REQUEST['tipo'],$_REQUEST['carpeta'],$_REQUEST['empresa'],$estado);

?>