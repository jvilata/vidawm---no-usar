<?php
session_start();
require_once("onedrive.php");

$VIDAOnedrive= new VIDAOneDrive("uploadFactura","FACTURAS",$_REQUEST['empresa'],$_REQUEST['nombrePDF'],$_REQUEST['nombrePDF_tmp']);


?>