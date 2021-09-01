<?php


require __DIR__.'/../lib/onedrive/autoload.php'; // onedrive api
require_once("./../notas/bd_notas_class.php");
require_once("./../personal/bd_personal_class.php");
require_once("./../facturas/bd_facturas_class.php");
require_once("./../dashboard/bd_dashboard_class.php");
require_once ("./../lib/sendmail_class.php");
  
use GuzzleHttp\Client as GuzzleHttpClient;
use Krizalys\Onedrive\Client;
use Krizalys\Onedrive\Proxy\DriveItemProxy;

use Microsoft\Graph\Graph;

use Monolog\Logger;

class VIDAOneDrive  {
    private $client=null;
    
    public function __construct($function=null,$tipo=null,$codigo=null,$filename=null,$filename_tmp=null,$fileid=null,$idadjunto=null,$texto=null,$cc=null){
        // If we don't have a code in the query string (meaning that the user did not
        // log in successfully or did not grant privileges requested), we cannot proceed
        // in obtaining an access token.
        //            if (!array_key_exists('code', $_GET)) {
        //                throw new \Exception('code undefined in $_GET');
        //            }
        //session_start();
        
        try {
        ($config = include __DIR__.'/config.php') or die('Configuration file not found');
        
        // Attempt to load the OneDrive client' state persisted from the previous
        // request.
        if (!array_key_exists('code', $_GET)) { // recuperamos de $_SESSION
           if (array_key_exists('onedrive.code', $_SESSION))
              $_GET['code']=$_SESSION['onedrive.code'];
         } else  {
             $_SESSION['onedrive.code']=$_GET['code']; 
         }
        if (!array_key_exists('onedrive.client.state', $_SESSION)||(!array_key_exists('code', $_GET))) { // es primera sesion, hay que pedir autenticacion
            $this->client = new Client(
                $config['ONEDRIVE_CLIENT_ID'],
                new Graph(),
                new GuzzleHttpClient(
                    ['base_uri' => 'https://graph.microsoft.com/v1.0/']
                    ),
                new Logger('Krizalys\Onedrive\Client')
                );
            // Gets a log in URL with sufficient privileges from the OneDrive API.
            $url = $this->client->getLogInUrl([
                'files.read',
                'files.read.all',
                'files.readwrite',
                'files.readwrite.all',
                'offline_access',
            ], $config['ONEDRIVE_REDIRECT_URI']);
            
            // Persist the OneDrive client' state for next API requests.
            $_SESSION['onedrive.client.state']=$this->client->getState();
            $_SESSION['function']=$function;
            $_SESSION['tipo']=$tipo;
            $_SESSION['codigo']=$codigo;
            $_SESSION['filename']=$filename;
            $_SESSION['filename_tmp']=$filename_tmp;
            $_SESSION['fileid']=$fileid;
            $_SESSION['idadjunto']=$idadjunto;
            $_SESSION['texto']=$texto;
            $_SESSION['cc']=$cc;
            //header('Location: '.$url); // llama de nuevo a esta pagina con $_SESSION inicializado
            if ($function!=null && ($function=="downloadFile"||$function=="deleteFile"||$function=="abrirCarpeta"
                ||$function=="uploadFactura"||$function=="downloadFactura"||$function=="downloadNomina"||$function=="recorrerCarpeta"||$function=="moverElementosCarpeta"
                ||$function=="recorrerCarpetasDrive"))
               header('Location: '.$url);
            else 
               echo "{'success': '".$url."'}";
            exit;
        }
 
        // si continua aqui es porque sí existe $_SESSION
        $this->client = new Client(
            $config['ONEDRIVE_CLIENT_ID'],
            new Graph(),
            new GuzzleHttpClient(
                ['base_uri' => 'https://graph.microsoft.com/v1.0/']
                ),
            new Logger('Krizalys\Onedrive\Client'),
            [
                // Restore the previous state while instantiating this client to proceed
                // in obtaining an access token.
                'state' => $_SESSION['onedrive.client.state']
            ]
            );
        if ($this->client->getAccessTokenStatus()==1 || $this->client->getAccessTokenStatus()==-1  ){// es valido
            $this->client->renewAccessToken($config['ONEDRIVE_CLIENT_SECRET']);
        } else if ($this->client->getAccessTokenStatus()==-2 ) {
           $url = $this->client->getLogInUrl([
                'files.read',
                'files.read.all',
                'files.readwrite',
                'files.readwrite.all',
                'offline_access',
            ], $config['ONEDRIVE_REDIRECT_URI']);
            unset($_SESSION['onedrive.client.state']);
            unset($_SESSION['onedrive.code']);
           //header('Location: '.$url); // llama de nuevo a esta pagina con $_SESSION inicializado
            if ($function!=null && ($function=="downloadFile"||$function=="deleteFile"||$function=="abrirCarpeta"
                ||$function=="uploadFactura"||$function=="downloadFactura"||$function=="downloadNomina"||$function=="recorrerCarpeta"||$function=="moverElementosCarpeta"
                ||$function=="recorrerCarpetasDrive"))
               header('Location: '.$url);
            else 
               echo "{'success': '".$url."'}";
            exit;
        } else { // ha caducado o no se ha iniciado todavia
            // Obtain the token using the code received by the OneDrive API.
            $uri=$this->client->getState()->redirect_uri; // se pierde con la llamadaa obtainAccessToken
            $this->client->obtainAccessToken($config['ONEDRIVE_CLIENT_SECRET'], $_GET['code']);
            $this->client->getState()->redirect_uri=$uri;
        }
        // Persist the OneDrive client' state for next API requests.
        $_SESSION['onedrive.client.state'] = $this->client->getState();
        if ($function!=null) {
            $this->$function($tipo,$codigo,$filename,$filename_tmp,$fileid,$idadjunto,$texto,$cc);
        }
        }
        catch (Exception $e) {
            echo "<script>alert(' Error:".$e->getMessage()."');</script>";
            
        }
    }
    
    public function __destruct(){
    }
    
  
    
    public function getFirstChildByName(DriveItemProxy $item, $name)
    {
        $items=[];
        $items = array_filter($item->children, function (DriveItemProxy $item) use ($name) {
            return $item->name == $name;
        });
            
            return count($items) == 1 ? array_values($items)[0] : null;
    }
    
    public function crearCarpeta($tipo,$carpeta,$empresa,$estado,$fileid,$idadjunto,$texto,$cc) {
        // comprobar si existe el directorio y posicionarse creandolo sin es necesario
        $options=[];
        $root= $this->client->getDriveByUser("jvilata@vidawm.com")->getRoot(); 
       
        $c_empresa=$this->getFirstChildByName($root,$empresa);//$this->client->getRoot()
       if ($c_empresa==null) $c_empresa=$root->createFolder($empresa,$options);//$this->client->getRoot()

//        $c_empresa=$this->getFirstChildByName($this->client->getRoot(),$empresa);
//        if ($c_empresa==null) $c_empresa=$this->client->getRoot()->createFolder($empresa,$options);
        
        $ctipo=$this->getFirstChildByName($c_empresa,$tipo);
        if ($ctipo==null) $ctipo=$c_empresa->createFolder($tipo,$options);

        if ($tipo=="ACTIVOS") {
                $cestado=$this->getFirstChildByName($ctipo,$estado);
                if ($cestado==null) $cestado=$ctipo->createFolder($estado,$options);
                $ctipo=$cestado; // por compatibilidad entre activos y entidades
        }
        
        $c_carpeta=$this->getFirstChildByName($ctipo,$carpeta);
        if ($c_carpeta==null)  $c_carpeta=$ctipo->createFolder($carpeta,$options);
        $url="https://vidawealth-my.sharepoint.com/personal/jvilata_vidawm_com/_layouts/15/onedrive.aspx?id=%2Fpersonal%2Fjvilata_vidawm_com%2FDocuments%2F";
        $separador="%2F";
        
         $url=$url.$empresa.$separador.$tipo.$separador;
        if ($tipo=="ACTIVOS") $url=$url.$estado.$separador.$carpeta;
        else $url=$url.$carpeta;
        //$url=$url.$empresa.$separador.$tipo.$separador.$carpeta;
 
        header('Location: '.$url);
    }
    
    public function abrirCarpeta($tipo,$carpeta,$empresa,$estado,$fileid,$idadjunto,$texto,$cc) {
        // en tipo tenemos el TIPO de carpeta y en codigo el nombre de la carpeta
        // carpeta ONEDRIVE ROOT JVILATA ID->b!yLHw1PqbRUqVYB9Sgq8F4fwrcSk6SpRFrDa7WXou4goW-O1l0WxqSK0pBNQdk_yp
        //var_dump($this->client->getDriveByUser("jvilata@vidawm.com"))       ;//fd8bca7c-acda-4abd-8540-296384a27002
 
        // comprobar si existe el directorio y posicionarse creandolo sin es necesario
        $options=[];
        $root= $this->client->getDriveByUser("jvilata@vidawm.com")->getRoot(); 
        $c_empresa=$this->getFirstChildByName($root,$empresa);//$this->client->getRoot()
      if ($c_empresa==null) $c_empresa=$root->createFolder($empresa,$options);//$this->client->getRoot()
        
        $ctipo=$this->getFirstChildByName($c_empresa,$tipo);
        if ($ctipo==null) $ctipo=$c_empresa->createFolder($tipo,$options);
        
        if ($tipo=="ACTIVOS") {
                $cestado=$this->getFirstChildByName($ctipo,$estado);
                if ($cestado==null) $cestado=$ctipo->createFolder($estado,$options);
                $ctipo=$cestado; // por compatibilidad entre activos y entidades
        }
        $c_carpeta=$this->getFirstChildByName($ctipo,$carpeta);
// podria buscar en las carpetas EN ESTUDIO y DESCARTADO para ver si encuentra y proponer mover a nuevo estado

        $url="https://vidawealth-my.sharepoint.com/personal/jvilata_vidawm_com/_layouts/15/onedrive.aspx?id=%2Fpersonal%2Fjvilata_vidawm_com%2FDocuments%2F";
        $separador="%2F";
         $url=$url.$empresa.$separador.$tipo.$separador;
        if ($tipo=="ACTIVOS") $url=$url.$estado.$separador.$carpeta;
        else $url=$url.$carpeta;
        //$url=$url.$empresa.$separador.$tipo.$separador.$carpeta;
        
//        header('Location: '.$url.$empresa.$separador.$tipo.$separador.$carpeta);
//        if ($c_carpeta==null)  $c_carpeta=$adjuntos->createFolder($carpeta,$options);
        if ($c_carpeta==null)
          echo "<script>if (confirm('La carpeta: <".$carpeta."> no existe. Pulse OK para crearla o Cancel para salir')==true) {".
                     "    window.open('crearCarpeta.php?tipo=".$tipo."&carpeta=".$carpeta."&empresa=".$empresa."&estado=".$estado."','_self');} ".
               "else window.close();</script>";   
        else 
           header('Location: '.$url);
    }
    
    
    public function recorrerCarpeta($tipo,$carpeta,$empresa,$codEmpresa,$fileid,$idadjunto,$texto,$cc) {
        // en tipo tenemos el TIPO de carpeta y en codigo el nombre de la carpeta
       
        // comprobar si existe el directorio y posicionarse creandolo sin es necesario
        $options=[];
        $root= $this->client->getDriveByUser("jvilata@vidawm.com")->getRoot();
        $c_empresa=$this->getFirstChildByName($root,$empresa);//$this->client->getRoot()
        if ($c_empresa==null) $c_empresa=$root->createFolder($empresa,$options);//$this->client->getRoot()
        
        $ctipo=$this->getFirstChildByName($c_empresa,$tipo); // facturas - notas -personal
        if ($ctipo==null) $ctipo=$c_empresa->createFolder($tipo,$options);
        
         $items=$ctipo->children;
         
         if ($tipo=="FACTURAS")  $api = new bd_facturas(true);
         else if  ($tipo=="NOTAS")  $api = new bd_notas(true);
         else $api = new bd_personal(true);
       
         for ($i=0;$i<count($items);$i++) {
             if ($tipo=="FACTURAS"||$tipo=="NOTAS"||$tipo=="PERSONAL") {
                 if (count($items[$i]->children)==0 && (
                     stripos($items[$i]->name,".pdf")!==false ||
                     stripos($items[$i]->name,".jpg")!==false ||
                     stripos($items[$i]->name,".jpeg")!==false ||
                     stripos($items[$i]->name,".png")!==false ||
                     stripos($items[$i]->name,".doc")!==false   )) { // solo cargo los archivos
                     //cargar en cabfacturas estado CARGADA
                     // seguramento esto hay que meterlo en bd_facturas.php
                         if ($tipo=="FACTURAS") $api->crearCabFacturasFromDrive($codEmpresa,$items[$i]->name); // facturas
                         else  if ($tipo=="NOTAS") $api->crearCabNotasFromDrive($codEmpresa,$items[$i]->name); // notas
                         else {
                             try{
                             // buscar directorio persona
                             $nombref=substr($items[$i]->name,0,stripos($items[$i]->name,"."));
                             $extension=substr($items[$i]->name,stripos($items[$i]->name,".")+1,10);
                             $scarpeta= $api->getAtributoPersona($codEmpresa,$nombref,'nombre');
                             $carpeta=$this->getFirstChildByName($ctipo,$scarpeta); // nombre persona
                             //mover fichero  directorio
                             $items[$i]->move($carpeta);
                             $nuevoNombre=$nombref."_".date("m_Y").".".$extension;
                             $items[$i]->rename($nuevoNombre);
                             //actualizar datos
                             $api->crearNominasFromDrive($codEmpresa,$nombref,$nuevoNombre); // personal
                         }
                        catch (Exception $e) {
                            echo $e->getMessage(). "<script>alert(' Error:".$scarpeta."');</script>";
            
                        }             
                         }
                 }
             }
         }
         echo "<script>window.close();</script>";
   }

   // para el conteo del Dashboard
   public function recorrerCarpetasDrive($tipo,$carpeta,$empresa,$codEmpresa,$fileid,$idadjunto,$texto,$cc) {
       // en tipo tenemos el TIPO de carpeta y en codigo el nombre de la carpeta
       
       // comprobar si existe el directorio y posicionarse creandolo sin es necesario
       $options=[];
       $root= $this->client->getDriveByUser("jvilata@vidawm.com")->getRoot();
       // buscar hijos
       $items=$root->children;
//       echo "[";
       $api = new bd_dashboard(true);
       for ($i=0;$i<count($items);$i++) {
           if ($i>0) echo ",";
           $facturas=$this->getFirstChildByName($items[$i],"FACTURAS");
           $itemsFac=$facturas->children;
           $cfac=0;
           for ($j=0;$j<count($itemsFac);$j++) {
               if (count($itemsFac[$j]->children)==0 &&  (
                   stripos($itemsFac[$j]->name,".pdf")!==false ||
                   stripos($itemsFac[$j]->name,".jpg")!==false ||
                   stripos($itemsFac[$j]->name,".jpeg")!==false ||
                   stripos($itemsFac[$j]->name,".png")!==false ||
                   stripos($itemsFac[$j]->name,".doc")!==false   ) ) $cfac++;
           }
           $notas=$this->getFirstChildByName($items[$i],"NOTAS");
           $itemsNot=$notas->children;
           $cnot=0;
           for ($j=0;$j<count($itemsNot);$j++) {
               if (count($itemsNot[$j]->children)==0 &&  (
                   stripos($itemsNot[$j]->name,".pdf")!==false ||
                   stripos($itemsNot[$j]->name,".jpg")!==false ||
                   stripos($itemsNot[$j]->name,".jpeg")!==false ||
                   stripos($itemsNot[$j]->name,".png")!==false ||
                   stripos($itemsNot[$j]->name,".doc")!==false   ) ) $cnot++;
           }
           $notas=$this->getFirstChildByName($items[$i],"PERSONAL");
           $itemsNot=$notas->children;
           $cnominas=0;
           for ($j=0;$j<count($itemsNot);$j++) {
               if (count($itemsNot[$j]->children)==0 &&  (
                   stripos($itemsNot[$j]->name,".pdf")!==false ||
                   stripos($itemsNot[$j]->name,".jpg")!==false ||
                   stripos($itemsNot[$j]->name,".jpeg")!==false ||
                   stripos($itemsNot[$j]->name,".png")!==false ||
                   stripos($itemsNot[$j]->name,".doc")!==false   ) ) $cnominas++;
           }
           //          echo "{'empresa':'".$items[$i]->name."','facturas':'".$cfac."','notas':'".$cnot."'}";
           if ($cfac>0 || $cnot>0 ) $api->insertarRegistro($items[$i]->name,$cfac,$cnot,$cnominas);
       }
//       echo "]";
       echo "<script>alert('Debe refrescar el grid del dashboard');window.close();</script>";
   }
   
   // mover elemento
   public function moverElementosCarpeta($tipo,$carpeta,$empresa,$codEmpresa,$destino,$asunto,$texto,$cc) {
       // en tipo tenemos el TIPO de carpeta y en codigo el nombre de la carpeta
     try {
       // comprobar si existe el directorio y posicionarse creandolo sin es necesario
       $options=[];
       $root= $this->client->getDriveByUser("jvilata@vidawm.com")->getRoot();
       $c_empresa=$this->getFirstChildByName($root,$empresa);//$this->client->getRoot()
       if ($c_empresa==null) $c_empresa=$root->createFolder($empresa,$options);//$this->client->getRoot()
       
       $ctipo=$this->getFirstChildByName($c_empresa,$tipo); // facturas O NOTAS
       if ($ctipo==null) $ctipo=$c_empresa->createFolder($tipo,$options);

       $cejercicio=$this->getFirstChildByName($ctipo,date('Y')); // ejercicio
       if ($cejercicio==null) $cejercicio=$ctipo->createFolder(date('Y'),$options);
       
       $cenviadas=$this->getFirstChildByName($cejercicio,'enviadas'.date('Ymd')); // facturas
       if ($cenviadas==null) $cenviadas=$cejercicio->createFolder('enviadas'.date('Ymd'),$options);
       
       $items=$ctipo->children;
       if ($tipo=="FACTURAS")  $api = new bd_facturas(true);
       else $api = new bd_notas(true);
    
       for ($i=0;$i<count($items);$i++) {
           if (($tipo=="FACTURAS"||$tipo=="NOTAS") && $cenviadas->id!=$items[$i]->id) {
               if (count($items[$i]->children)==0 &&  (
                     stripos($items[$i]->name,".pdf")!==false ||
                     stripos($items[$i]->name,".jpg")!==false ||
                     stripos($items[$i]->name,".jpeg")!==false ||
                     stripos($items[$i]->name,".png")!==false ||
                     stripos($items[$i]->name,".doc")!==false   ) ) {
                $resp=$items[$i]->move($cenviadas);
                if ($tipo=="FACTURAS") $api->actualizarCabFacturasFromDrive($codEmpresa,$items[$i]->name,'ENVIADA',date('Y').'/enviadas'.date('Ymd')); 
                else $api->actualizarCabNotasFromDrive($codEmpresa,$items[$i]->name,'ENVIADA',date('Y').'/enviadas'.date('Ymd'));
               }
               // crear en enlace y enviar por mail
           }
       } 
       $permissionProxy=$cenviadas->createLink("view","anonymous");
       $strlink="<a href='".$permissionProxy->getLink()->getWebUrl()."'>".$permissionProxy->getLink()->getWebUrl()."</a>";
       $str=str_replace("%enlace%",$strlink,$texto);
       $api_sendmail = new SendMail();
       $api_sendmail->envia("send", $destino,$cc,$asunto,$str, "", "");
       
       echo "<script>alert('Enviado mail');window.close();</script>";
     }  
     catch ( Exception $Exception)  {
         echo "<script>alert('No se han enviado facturas. Error:".$Exception->getMessage()."');window.close();</script>";
     }
   }
   
   public function uploadFactura($tipo,$empresa,$filename,$filename_tmp,$fileid,$idadjunto,$texto,$cc) {
       $options=[];
       
       // comprobar si existe el directorio y posicionarse creandolo sin es necesario
       $root= $this->client->getDriveByUser("jvilata@vidawm.com")->getRoot();
       $c_empresa=$this->getFirstChildByName($root,$empresa);//$this->client->getRoot()
       if ($c_empresa==null) $c_empresa=$root->createFolder($empresa,$options);//$this->client->getRoot()
       
       $ctipo=$this->getFirstChildByName($c_empresa,$tipo); // facturas
       if ($ctipo==null) $ctipo=$c_empresa->createFolder($tipo,$options);
         
       $content=file_get_contents($filename_tmp);
       
       try {
           $file=$ctipo->upload($filename,$content,$options);
       }catch  (Exception $e) { // el archivo ya existe hay que actualizarlo
           $file=$this->getFirstChildByName($ctipo,$filename);
           $file->delete();// lo borro
           $file=$ctipo->upload($filename,$content,$options);// lo subo de nuevo
       }
       if ($file!=null) { // se ha subido ok
           unlink($filename_tmp); // borro el temporal
          echo "<script>alert('Se ha guardado el archivo en OneDrive correctamente');window.close()</script>";
          // echo "{'success':''}";
       } else {
           echo "{'failure':'Sorry, there was an error uploading your file to OneDrive.'}";
           //$api = new bd_attachs();
           //$api->borrarAttachBD($idadjunto);
       }
   }
   
   public function downloadFactura($tipo,$empresa,$filename,$carpeta,$destino,$asunto,$texto,$cc) {
       $options=[];

       // comprobar si existe el directorio y posicionarse creandolo sin es necesario
       $root= $this->client->getDriveByUser("jvilata@vidawm.com")->getRoot();
       $c_empresa=$this->getFirstChildByName($root,$empresa);//$this->client->getRoot()
       if ($c_empresa==null) $c_empresa=$root->createFolder($empresa,$options);//$this->client->getRoot()
       
       $ctipo=$this->getFirstChildByName($c_empresa,$tipo); // facturas
       if ($ctipo==null) $ctipo=$c_empresa->createFolder($tipo,$options);
  
       if ($carpeta!="") {
        $ejercicio = stristr($carpeta, '/', true); 
        $carpeta = substr($carpeta,strlen($ejercicio)+1);
        $cejercicio=$this->getFirstChildByName($ctipo,$ejercicio); // ejercicio
        if ($cejercicio==null) $cejercicio=$ctipo;
       
        $cenviadas=$this->getFirstChildByName($cejercicio,$carpeta); // facturas
        if ($cenviadas==null) $cenviadas=$cejercicio->createFolder($carpeta,$options);
       } else  {
           $cenviadas=$ctipo;
       }
     
       $file=$this->getFirstChildByName($cenviadas,$filename);
       if ($file!=null) { // lo encuenta
           $content= $file->download();
          // $filetmp=__DIR__."/tmp/tmp".mt_rand().".tmp"; //en onedrive
           $dirbase=$_SERVER['DOCUMENT_ROOT'].'/privado/uploads/';// carpeta tmp para facturas generadas
           file_put_contents($dirbase.$filename,$content);
           $api_sendmail = new SendMail();
           $api_sendmail->envia("send", $destino,$cc,$asunto,$texto, "", $dirbase.$filename);
           
           echo "<script>alert('Enviado mail');window.close();</script>";
       } else {
           echo "<script>alert('NO ENVIADO. ha habido un error enviando mail');window.close();</script>";
       }
   }
   
   public function downloadNomina($tipo,$empresa,$filename,$carpeta,$destino,$asunto,$texto,$cc) {
       $options=[];
       
       if ($filename=="") { // solo pago de notas de gasto, no hay pdf
            $api_sendmail = new SendMail();
           $api_sendmail->envia("send", $destino,$cc,$asunto,$texto, "", "","");
           
           echo "<script>alert('Enviado mail a ".$destino."');window.close();</script>";
           return;
       }
       
       // comprobar si existe el directorio y posicionarse creandolo sin es necesario
       $root= $this->client->getDriveByUser("jvilata@vidawm.com")->getRoot();
       $c_empresa=$this->getFirstChildByName($root,$empresa);//$this->client->getRoot()
       if ($c_empresa==null) $c_empresa=$root->createFolder($empresa,$options);//$this->client->getRoot()
       
       $ctipo=$this->getFirstChildByName($c_empresa,$tipo); // PERSONAL
       if ($ctipo==null) $ctipo=$c_empresa->createFolder($tipo,$options);
       
       $cenviadas=$this->getFirstChildByName($ctipo,$carpeta); // nombre persona
       if ($cenviadas==null) $cenviadas=$ctipo->createFolder($carpeta,$options);
       
       $file=$this->getFirstChildByName($cenviadas,$filename);
       if ($file!=null) { // lo encuenta
           $content= $file->download();
           // $filetmp=__DIR__."/tmp/tmp".mt_rand().".tmp"; //en onedrive
           $dirbase=$_SERVER['DOCUMENT_ROOT'].'/privado/uploads/';// carpeta tmp para facturas generadas
           file_put_contents($dirbase.$filename,$content);
           $api_sendmail = new SendMail();
           $api_sendmail->envia("send", $destino,$cc,$asunto,$texto, "", $dirbase.$filename);
           
           echo "<script>alert('Enviado mail a ".$destino."');window.close();</script>";
       } else {
           echo "<script>alert('NO ENVIADO. ha habido un error enviando mail');window.close();</script>";
       }
   }
   
    public function uploadFile($tipo,$codigo,$filename,$filename_tmp,$fileid,$idadjunto,$texto,$cc) {
        $options=[];
        
        // comprobar si existe el directorio y posicionarse creandolo sin es necesario
        
        $root= $this->client->getDriveByUser("jvilata@vidawm.com")->getRoot();
        $adjuntos=$this->getFirstChildByName($root,"adjuntos");
        
        $folderTipo=$this->getFirstChildByName($adjuntos,$tipo);
        if ($folderTipo==null) {
            $folderTipo=$adjuntos->createFolder($tipo,$options);
        }
        $folderCodigo=$this->getFirstChildByName($folderTipo,$codigo);
        if ($folderCodigo==null) {
            $folderCodigo=$folderTipo->createFolder($codigo,$options);
        }
        
        $content=file_get_contents($filename_tmp);

        try {
            $file=$folderCodigo->upload($filename,$content,$options);
        }catch  (Exception $e) { // el archivo ya existe hay que actualizarlo
            $fileCodigo=$this->getFirstChildByName($folderCodigo,$filename);
            $fileCodigo->delete();// lo borro
            $file=$folderCodigo->upload($filename,$content,$options);// lo subo de nuevo
        }
        if ($file!=null) { // se ha subido ok
         //   $api = new bd_attachs();
         //   $api->actualizarAttachBD($file->id,$idadjunto);
            echo "<script>window.close()</script>";
            //echo "{'success':''}";
        } else {
            echo "{'failure':'Sorry, there was an error uploading your file to OneDrive.'}";
            //$api = new bd_attachs();
            //$api->borrarAttachBD($idadjunto);
        }
    }
    
    public function downloadFile($tipo,$codigo,$filename,$filename_tmp,$fileid,$idajunto,$texto,$cc) {
        try {
            $item=$this->client->getDriveItemById($this->client->getMyDrive()->id, $fileid);
            if ($item!=null) {
                $content= $item->download();
                $filetmp=__DIR__."/tmp/tmp".mt_rand().".tmp"; //en onedrive
                file_put_contents($filetmp,$content);
                $url="/privado/php/onedrive/download.php?file=".$item->name."&filetmp=".$filetmp;
                header('Location: '.$url);
            }
        } catch (Exception $e) {};
        
    }
    
    public function deleteFile($tipo,$codigo,$filename,$filename_tmp,$fileid,$idadjunto,$texto,$cc) {
        try {
            $item=$this->client->getDriveItemById($this->client->getMyDrive()->id, $fileid);
            if ($item!=null) {
                $item->delete(); // borra este elemento
                echo "<script>window.close();</script>";
            }
        } catch (Exception $e) {};
    }
    
}

?>