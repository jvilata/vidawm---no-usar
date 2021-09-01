<?php
if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: ". $_SERVER['HTTP_ORIGIN']); // jv. 19.2.2020 para permitir conectarse desde otras URLs
	} else {
	    header("Access-Control-Allow-Origin: *");
	}
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
$actualURL = "/privado/php/edicom/redirect.php";
$newURL = "https://gestion.edicom.es".substr($_SERVER['REQUEST_URI'],strlen($actualURL));

//$data="email=jvilata@edicom.es&password=JiZWYWxlbmNpYTE5Njc=";

//An array to hold the data that we'll end up sending. 
//Empty by default.
$postData = "";
 
//Attemp to find the POST variables that we want to send.
foreach($_REQUEST as $name => $valor){
    if (strlen($postData)>0) $postData =  $postData . "&";
    $postData = $postData .  $name . "=" .  $valor;
}

// var_dump($postData);

$curl_handle=curl_init();
curl_setopt($curl_handle, CURLOPT_URL,$newURL);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 1);  
curl_setopt($curl_handle, CURLOPT_POST, 1);  
curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $postData);

$query = curl_exec($curl_handle);
// $query = curl_error($curl_handle) . '<br/>';
curl_close($curl_handle);
echo $query 
?>