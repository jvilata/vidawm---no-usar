<?php
session_start();

require_once("onedrive.php");

$VIDAOnedrive= new VIDAOneDrive($_SESSION['function'],$_SESSION['tipo'],$_SESSION['codigo'],$_SESSION['filename'],$_SESSION['filename_tmp'],$_SESSION['fileid'],$_SESSION['idadjunto']);

?>