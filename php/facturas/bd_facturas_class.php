<?php

	require_once("../lib/bd_api_pdo.php");
	require_once("pdf_invoice_class.php");

	class bd_facturas extends BD_API {
	    protected $dirbase=''; 
	    
        public function __construct($saltarId = false){
             parent::__construct($saltarId);
		    $this->dirbase= $_SERVER['DOCUMENT_ROOT'].'/privado/uploads/';// carpeta tmp para facturas generadas
            
        }
         public function __destruct(){
        }
	        
		public function analiza_method() {
		    // el POST cuando viene de un formulario hay que hacer a mano el insert con un metodo por ejemplo guardaBD en estea clase. Si vienen con JSON puede ser automatico
		    if ( ($this->method=="GET" && $this->table!="cabfacturas")||($this->method=="POST" &&  $this->table!="findFacturasFilter" && $this->table!="findLinFacturasFilter")) { // lo hemos llamado con otro metodo
				$func=$this->table; // nombre de la funcion a la que llamo de esta clase,  en table guardo el nombre del metodo
				$this->$func();
			} else {
			    if ($this->method=="DELETE") { // hay que borra en tablas relacionadas
			        if ($this->table=="findFacturasFilter") {
    			        $stmt = ($this->link)->prepare("select * from cabfacturas where id=$this->key");
    			        if  ($stmt->execute()) {
    			            $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
    			            if (count($result)>0)  {// existe , lo borro
    			                    // borro las lineas
    			                    $stmt = ($this->link)->prepare("delete from linfacturas where idcabfactura=$this->key");
    			                    $stmt->execute();
    			                    // borro movimientos
    			                    $stmt = ($this->link)->prepare("delete from movimientos where tipoObjeto='F'and idObjeto=$this->key");
    			                    $stmt->execute();
    			                    // borro adjuntos
    			                    //$vattach = new BorrarAdjuntos();
    			                    //$vattach->borrarAdjuntosObjeto($this->link,'F', $this->key);
    			            }
    			        }
    				    $this->table="cabfacturas";
    				    $this->execStandardMethod();
			        } else { // son las lineas
			            $this->table="linfacturas";
			            $this->execStandardMethod();
			            
			        }
			    }  // delete
			    else // POST,PUT
			        if ($this->table=="findFacturasFilter") $this->guardarCabFacturas();
			        else {
			            $this->table="linfacturas";
			            $this->execStandardMethod();
			            //$this->guardarLinFacturas();
			        }
			    
			}	

		}

		

		public function findFacturasFilter() {
				$codEmpresa=( isset($_REQUEST['codEmpresa'])?$_REQUEST['codEmpresa']:NULL);
				$id=( isset($_REQUEST['id'])?$_REQUEST['id']:$this->key);
				$nroFactura=( isset($_REQUEST['nroFactura'])?$_REQUEST['nroFactura']:NULL);

				$idCliente=(isset($_REQUEST['idObjeto']) && $_REQUEST['tipoObjeto']=='E')?$_REQUEST['idObjeto']:( isset($_REQUEST['idCliente'])?$_REQUEST['idCliente']:NULL);
				$tipoFactura=( isset($_REQUEST['tipoFactura'])?$_REQUEST['tipoFactura']:NULL);
				$fechainicial=( isset($_REQUEST['fechainicial'])?$_REQUEST['fechainicial']:NULL);
				$fechafinal=( isset($_REQUEST['fechafinal'])?$_REQUEST['fechafinal']:NULL);
				$idActivo=(isset($_REQUEST['idObjeto']) && $_REQUEST['tipoObjeto']=='A')?$_REQUEST['idObjeto']:( isset($_REQUEST['idActivo'])?$_REQUEST['idActivo']:NULL);
				$estadoFactura=(isset($_REQUEST['estadoFactura'])?$_REQUEST['estadoFactura']:NULL);
				
				$sql = "select cabfacturas.*,entidades.nombre as nomEntidad,entidades.email as emailEntidad, ".
                       "(select count(1) AS nummov from movimientos where ((movimientos.tipoObjeto='F' and movimientos.idObjeto = cabfacturas.id) and (movimientos.tipoOperacion in ('PAGO','COBRO')))) AS nummov ". 
                       " from cabfacturas left join entidades on entidades.id=cabfacturas.idCliente ".
				       "where  " .
				    " cabfacturas.id is not null " .
				    ($id != NULL ? " AND (cabfacturas.id = :id )  " : "") .
				    ($codEmpresa != NULL ? " AND (cabfacturas.codEmpresa = :codEmpresa)  " : "") .
				    ($nroFactura != NULL ? " AND (nroFactura like :nroFactura)  " : "") .
					($idCliente != NULL ? " AND (idCliente = :idCliente)  " : "") .
					($tipoFactura != NULL ? " AND (tipoFactura = :tipoFactura)  " : "").
					($estadoFactura != NULL ? " AND (estadoFactura = :estadoFactura)  " : "").
					($fechainicial != NULL ? " AND (fecha >= :fechainicial)  " : "").
					($fechafinal != NULL ? " AND (fecha <= :fechafinal)  " : "").
					($idActivo!=NULL ? " AND exists (select 1 from linfacturas l where l.idcabFactura=cabfacturas.id and l.idActivo=:idActivo)":"")
					;

				$sql .= " ORDER BY nroFactura"; 

				$stmt = ($this->link)->prepare($sql);

				if (strpos($sql, ':id ') !== false) $stmt->bindParam(':id',  $id);
				if (strpos($sql, ':codEmpresa') !== false) $stmt->bindParam(':codEmpresa',  $codEmpresa);
				if (strpos($sql, ':nroFactura') !== false) $stmt->bindParam(':nroFactura',  $nroFactura);
				if (strpos($sql, ':idCliente') !== false) $stmt->bindParam(':idCliente',  $idCliente);
				if (strpos($sql, ':tipoFactura') !== false) $stmt->bindParam(':tipoFactura',  $tipoFactura);
				if (strpos($sql, ':estadoFactura') !== false) $stmt->bindParam(':estadoFactura',  $estadoFactura);
				if (strpos($sql, ':fechainicial') !== false) $stmt->bindParam(':fechainicial',  $fechainicial);
				if (strpos($sql, ':fechafinal') !== false) $stmt->bindParam(':fechafinal',  $fechafinal);
				if (strpos($sql, ':idActivo') !== false) $stmt->bindParam(':idActivo',  $idActivo);
				
				try {
					if  ($stmt->execute()) {
						$result= $stmt->fetchAll(PDO::FETCH_ASSOC);
						$this->devolverResultados($result);
					}
				}
				catch ( PDOException $Exception)  {
					die( $Exception->getMessage( )  );
				}
		}
		
		public function findEstadoFacturas() {
		  //  $codEmpresa=( isset($_REQUEST['codEmpresa'])?$_REQUEST['codEmpresa']:NULL);
		    
		    $sql = "select CONCAT('FACTURAS PEND.PAGO ',tabaux.valor1) as empresa,count(*) as facturas,0 as notas ".
		  		    " from cabfacturas join tablaauxiliar tabaux on tabaux.codtabla=8 and tabaux.codElemento=cabfacturas.codEmpresa  ".
		  		    "where  " .
		  		    " cabfacturas.id is not null " .
//		  		    ($codEmpresa != NULL ? " AND (cabfacturas.codEmpresa = :codEmpresa)  " : "") .
		  		    " AND (tipoFactura = 'RECIBIDA')  " .
		  		    " AND not exists (select 1 from movimientos where ((movimientos.tipoObjeto='F' and movimientos.idObjeto = cabfacturas.id) and (movimientos.tipoOperacion in ('PAGO','COBRO')))) ".
                    " GROUP BY tabaux.valor1"
		  		    ;
		  		  
		  		    
		  		    $stmt = ($this->link)->prepare($sql);
		  		    
		  		    try {
		  		        if  ($stmt->execute()) {
		  		            $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
		  		            $this->devolverResultados($result);
		  		        }
		  		    }
		  		    catch ( PDOException $Exception)  {
		  		        die( $Exception->getMessage( )  );
		  		    }
		}
		
		public function findLinFacturasFilter() {
		    $idcabFactura=( isset($_REQUEST['idcabFactura'])?$_REQUEST['idcabFactura']:$this->key);
		    $sql = "select * from linfacturas where " .
		  		    ($idcabFactura != NULL ? "  (idcabFactura = :idcabFactura)  " : " idcabFactura = -1 ") ;
		  		    $sql .= " ORDER BY id";
		  		    
		  		    $stmt = ($this->link)->prepare($sql);
		  		    if (strpos($sql, ':idcabFactura') !== false) $stmt->bindParam(':idcabFactura',  $idcabFactura);
		  		    
		  		    try {
		  		        if  ($stmt->execute()) {
		  		            $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
		  		            $this->devolverResultados($result);
		  		        }
		  		    }
		  		    catch ( PDOException $Exception)  {
		  		        die( $Exception->getMessage( )  );
		  		    }
		}

		    
	public function guardarCabFacturas() {
	    // en la case BD_APi hemos pasado el JSON al array $this->input
	 	    
	    $lastId=$this->guardarCabFacturasGenerico($this->input['codEmpresa'],(isset($this->input['id'])?$this->input['id']:null), $this->input['tipoFactura'], $this->input['nroFactura'],$this->input['idCliente'],
	               $this->input['fecha'], $this->input['por_retencion'],$this->input['retencion'],$this->input['base'],
	        $this->input['totalIva'], $this->input['totalFactura'],$this->input['archivoDrive'],$this->input['estadoFactura'],
	        $this->input['carpeta']);
	        
	    echo "{\"id\":".$lastId."}";
	}
	
	public function crearCabFacturasFromDrive($codEmpresa=null,$name=null) {
	    $sql="select id from cabfacturas where codEmpresa=:codEmpresa and archivoDrive=:archivoDrive";
	    $stmt = ($this->link)->prepare($sql);
	    $stmt->bindValue(':codEmpresa', $codEmpresa);//$codEmpresa
	    $stmt->bindValue(':archivoDrive', $name);
	    $stmt->execute();
	    $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
	    if (count($result)<=0){ // no existe factura
	        
    	    $sql="insert into cabfacturas set codEmpresa=:codEmpresa,archivoDrive=:archivoDrive,".
    	   	    "tipofactura='RECIBIDA',estadoFactura='PENDIENTE',fecha=now(),carpeta='',comentarios=concat('Cargada automaticamente el ',date_format(now(),'%d/%m/%Y')) ".
    	   	    " ,user='".(isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."',ts=now() ";
    	   	$stmt = ($this->link)->prepare($sql);
    	    $stmt->bindValue(':codEmpresa', $codEmpresa);//$codEmpresa
    	    $stmt->bindValue(':archivoDrive', $name);
    	    $stmt->execute();
	    }
	}

	public function actualizarCabFacturasFromDrive($codEmpresa,$nomDriveItem,$estado,$carpeta) {
	    $sql="update cabfacturas set ".
	   	    "estadoFactura='ENVIADA',carpeta=:carpeta ".
	   	    " ,user='".(isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."',ts=now() ".
	   	    " where codEmpresa=:codEmpresa and archivoDrive=:archivoDrive";
	    $stmt = ($this->link)->prepare($sql);
	    $stmt->bindValue(':codEmpresa', $codEmpresa);//$codEmpresa
	    $stmt->bindValue(':archivoDrive', $nomDriveItem);
	    $stmt->bindValue(':carpeta', $carpeta);
	    $stmt->execute();
	    
	}
	
	public function guardarCabFacturasGenerico($codEmpresa,$id,$tipoFactura,$nroFactura,$idCliente,
	            $fecha,$por_retencion,$retencion,$base,$totalIva,$totalFactura,$archivoDrive,
	            $estadoFactura,$carpeta) {
	    // en la case BD_APi hemos pasado el JSON al array $this->input
    
	    $sql="update cabfacturas ";
	    $where=" where id=:id ";
	    if ( $nroFactura<0) { // busco nro factura
	        $sql="select max(CAST(nroFactura AS INT)) as numfac from cabfacturas where codEmpresa=:codEmpresa and tipoFactura='EMITIDA'";
	        $stmt = ($this->link)->prepare($sql);
	        $stmt->bindParam(':codEmpresa', $codEmpresa);
	        if  ($stmt->execute()) {
	            $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
	            if (count($result)<=0)  // no existen facturas,numfac=1
	                $nroFactura=1;
	                else  // si que existen, sumo 1 a la ultima
	                    $nroFactura=$result[0]['numfac']+1;
	                    $sql="insert into cabfacturas ";
	                    $where="";
	        }
	    }
	    $sql = $sql.
	    " set  codEmpresa=:codEmpresa, tipoFactura=:tipoFactura, nroFactura=:nroFactura, idCliente=:idCliente,fecha=:fecha, ".
	    " base=:base,por_retencion=:por_retencion,retencion=:retencion,totalIva=:totalIva,totalFactura=:totalFactura ,".
	    " archivoDrive=:archivoDrive,estadoFactura=:estadoFactura,carpeta=:carpeta ".
	    " ,user='".(isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."',ts=now() ".
	    $where;
	    $stmt = ($this->link)->prepare($sql);
	    if (strpos($sql, ':id ') !== false) $stmt->bindValue(':id', $id);
	    $stmt->bindValue(':codEmpresa',  $codEmpresa);
	    $stmt->bindValue(':tipoFactura', $tipoFactura);
	    $stmt->bindValue(':nroFactura',  $nroFactura);
	    $stmt->bindValue(':idCliente',  $idCliente);
	    $stmt->bindValue(':fecha',  $fecha);
	    $stmt->bindValue(':por_retencion', $por_retencion);
	    $stmt->bindValue(':retencion',  $retencion);
	    $stmt->bindValue(':base',  $base);
	    $stmt->bindValue(':totalIva',  $totalIva);
	    $stmt->bindValue(':totalFactura', $totalFactura);
	    $stmt->bindValue(':archivoDrive', $archivoDrive);
	    $stmt->bindValue(':estadoFactura', $estadoFactura);
	    $stmt->bindValue(':carpeta', $carpeta);
	    
	    try {
	        $stmt->execute() ;
	        $lastId= $this->link->lastInsertId();
	        return $lastId;
// 	        echo "{id:".$lastId."}";
	    }
	    
	    catch ( PDOException $Exception)  {
	        die( $Exception->getMessage( )  );
	    }
	}
	
	public function copiarFactura($id=null) {
	    if ($id==null) $id=$_REQUEST['id'];

	    $sql = "select * from cabfacturas where  id = :id";
   	    $stmt = ($this->link)->prepare($sql);
   	    $stmt->bindParam(':id',  $id);
   	    try {
   	        if  ($stmt->execute()) {
   	            $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
   	            if (count($result)>0) {
   	                if ($result[0]['tipoFactura']=="PLANT.EMITIDA"||$result[0]['tipoFactura']=="EMITIDA") $tipoFactura="EMITIDA";
   	                else $tipoFactura="RECIBIDA";
   	                $lastId=$this->guardarCabFacturasGenerico($result[0]['codEmpresa'], null, $tipoFactura,
   	                    -1,  $result[0]['idCliente'],  date("Y-m-d"),  $result[0]['por_retencion'],
   	                    $result[0]['retencion'],  $result[0]['base'],  $result[0]['totalIva'],  $result[0]['totalFactura'],
   	                    $result[0]['archivoDrive'],'PENDIENTE',$result[0]['carpeta']);
   	                $sql="insert into linfacturas (idcabFactura,idActivo,descripcion,unidades,precio,pdescuento,".
   	   	                "neto,piva,totalIva,totalLinea,user,ts) ".
   	   	                " select ".$lastId.",idActivo,".
   	   	                " REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(descripcion,'%Tant',if(quarter(curdate())=1, 4,quarter(CURDATE())-1)),'%T',quarter(CURDATE())), '%Mant',upper(date_format(DATE_ADD(CURDATE(),INTERVAL -1 MONTH),'%M'))),'%Msig',upper(date_format(DATE_ADD(CURDATE(),INTERVAL 1 MONTH),'%M'))),'%Ytant',date_format(DATE_ADD(CURDATE(),INTERVAL -1 MONTH),'%Y')),'%Y',date_format(CURDATE(),'%Y')),'%M', upper(date_format(CURDATE(),'%M'))),".
   	   	                " unidades,precio,pdescuento,neto,piva,totalIva,totalLinea,".
   	   	                "'".(isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."',now() ".
   	   	                " FROM linfacturas where idcabFactura=:id";
   	                $stmt = ($this->link)->prepare($sql);
   	                $stmt->bindParam(':id',  $id);
	                $stmt->execute();
	                echo "{'msg':'Factura Copiada al registro:".$lastId."'}";
	                return $lastId;
   	            }
   	        }
   	    }
   	    catch ( PDOException $Exception)  {
   	        die( $Exception->getMessage( )  );
   	    }
	}
	
	public function generarFactura($tipoObjeto=null,$idObjeto=null) {
// 	    if ($tipoObjeto==null) $tipoObjeto=$_REQUEST['tipoObjeto'];
// 	    if ($idObjeto==null) $idObjeto=$_REQUEST['idObjeto'];
	    if ($tipoObjeto==null) $tipoObjeto=$_POST['tipoObjeto'];
	    if ($idObjeto==null) $idObjeto=$_POST['idObjeto'];
	    
	    if ($tipoObjeto=='A') //tipo Activo
    	    $sql = "select * from cabfacturas where tipoFactura = 'PLANT.EMITIDA' ".
    	      " AND exists (select 1 from linfacturas l where l.idcabFactura=cabfacturas.id and l.idActivo=:idObjeto)";
        else //tipo Entidad
            $sql = "select * from cabfacturas where tipoFactura = 'PLANT.EMITIDA' ".
            " AND IdCliente=:idObjeto";
            
	    $stmt = ($this->link)->prepare($sql);
	    $stmt->bindParam(':idObjeto',  $idObjeto);
	    try {
	        $stmt->execute();
            $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result)>0) {// ha encontrado plantilla
               $lastId=$this->copiarFactura($result[0]['id']);
               // guarda en drive
               $api = new pdf_invoice_class();
               $nombrePDF=$api->main_imprimirFactura($lastId,1); // a disco
               
            } else
                echo "{'msg':'No se ha encontrado plantilla de factura emitida para este Objeto ".$tipoObjeto."-".$idObjeto.", debe crearlo antes de generar'}";
	    }
	    catch ( PDOException $Exception)  {
	        die( $Exception->getMessage( )  );
	    }
	}
	
	public function subirFactura(){ //idFactura,user,ts
	    $idCabFactura=$_POST['idFactura'];
	    $empresa=$_POST['empresa'];
	    $user=$_POST['user'];
	    $ts=$_POST['ts'];
	    
	    $nombrePDF=basename($_FILES["fileToUpload"]["name"]);
	    $target_file = $this->dirbase . $nombrePDF;
	        move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
	        echo "{'success':'/privado/php/facturas/bd_facturas.php/uploadFacturaOneDrive?idCabFactura=".$idCabFactura."&empresa=".$empresa."&nombrePDF=".$nombrePDF."'}";
	      //  $this->uploadFacturaOneDrive($idCabFactura, $nombrePDF, $empresa);

	    
	}
	
	public function uploadFacturaOneDrive($idCabFactura=null,$nombrePDF=null,$empresa=null, $aDisco=1) {
	    if (isset($_REQUEST['idCabFactura'])) $idCabFactura=$_REQUEST['idCabFactura'];
        if ($empresa===null) $empresa=$_REQUEST['empresa'];
	    if ($nombrePDF===null) $nombrePDF=$_REQUEST['nombrePDF'];
	    // guarda nombre archivo en registro
	    $sql="update cabfacturas set archivoDrive=:nombrePDF,estadoFactura='PENDIENTE',carpeta='' ".
	   	    " where id=:lastId";
	    $stmt = ($this->link)->prepare($sql);
	    $stmt->bindValue(':lastId', $idCabFactura);
	    $stmt->bindValue(':nombrePDF', $nombrePDF);
	    $stmt->execute();

	    //subir a Onedrive
	    $url="/privado/php/onedrive/uploadFactura.php?empresa=".$empresa."&nombrePDF=".$nombrePDF."&nombrePDF_tmp=".$this->dirbase.$nombrePDF;
	    if ($aDisco==1) header('Location: '.$url); // version old
	    else echo ' { "success": "'.$url. '" }'; // version 2020
	    // echo ' {"success": "/privado/php/onedrive/bd_facturas.php/uploadFacturaOneDrive?idCabFactura='.$idCabFactura.'&empresa='.$empresa.'&nombrePDF='.$nombrePDF.'"}';
	    
	}
	
	public function generarPagoCobroFactura($id=null) {
	    if ($id==null) $id=$_REQUEST['id'];
	    
        $sql="insert into movimientos (codEmpresa,tipoObjeto,idObjeto,tipoOperacion,fecha,importe,descripcion,user,ts) ".
             " select codEmpresa,'F',id,case when tipoFactura='EMITIDA' then 'COBRO' else 'PAGO' end,curdate(), totalFactura,concat('FRA ',nroFactura,' - ',(select descripcion from linfacturas where linfacturas.idcabFactura=cabfacturas.id limit 1)),".
             "'".  (isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."',now() from cabfacturas where id=:id";
        $stmt = ($this->link)->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        echo "{'success':'Insertado movimiento'}";
        
	}
	            
}


  
?>