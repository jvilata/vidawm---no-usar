<?php 
$file=$_GET['file']; // nombre real de descarga
$filetmp=$_GET['filetmp'];//nombre del archivo temporal

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.$file.'"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($filetmp)); //Absolute URL
ob_clean();
flush();
readfile($filetmp); //Absolute URL
 unlink($filetmp); // borra archivo temporal 
 echo "<script>window.close();</script>";
exit();
?>