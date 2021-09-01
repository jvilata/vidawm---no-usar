<?php
try {
session_start();

require_once("onedrive.php");
$VIDAOnedrive= new VIDAOneDrive("moverElementosCarpeta",$_REQUEST['tipo'],$_REQUEST['carpeta'],$_REQUEST['empresa'],$_REQUEST['codEmpresa'],$_REQUEST['destino'],$_REQUEST['asunto'],$_REQUEST['texto'],$_REQUEST['destinoCopia']);

}
catch (Exception $e) {
    echo "<script>alert(' Error:".$e->getMessage()."');</script>";
    
}
?>