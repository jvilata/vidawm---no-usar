<?php

	require_once("../lib/bd_api_pdo.php");

	class bd_notas extends BD_API {
	    protected $dirbase=''; 
	    
        public function __construct($saltarId = false){
             parent::__construct($saltarId);
		    $this->dirbase= $_SERVER['DOCUMENT_ROOT'].'/privado/uploads/';// carpeta tmp para facturas generadas
            
        }
         public function __destruct(){
        }
	        
		public function analiza_method() {
		    // el POST cuando viene de un formulario hay que hacer a mano el insert con un metodo por ejemplo guardaBD en estea clase. Si vienen con JSON puede ser automatico
		    if ( ($this->method=="GET" && $this->table!="cabnotas")||($this->method=="POST" &&  $this->table!="findNotasFilter" )) { // lo hemos llamado con otro metodo
				$func=$this->table; // nombre de la funcion a la que llamo de esta clase,  en table guardo el nombre del metodo
				$this->$func();
			} else {
			    if ($this->method=="DELETE") { // hay que borra en tablas relacionadas
			        if ($this->table=="findNotasFilter") {
    			        $stmt = ($this->link)->prepare("select * from cabfacturas where id=$this->key");
    			        if  ($stmt->execute()) {
    			            $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
    			            if (count($result)>0)  {// existe , lo borro
    			                    // borro movimientos
//     			                    $stmt = ($this->link)->prepare("delete from movimientos where tipoObjeto='F'and idObjeto=$this->key");
//     			                    $stmt->execute();
    			            }
    			        }
    				    $this->table="cabnotas";
    				    $this->execStandardMethod();
			        } 
			    }  // delete
			    else // POST,PUT
			        if ($this->table=="findNotasFilter") $this->guardarCabNotas();
			}	

		}

		

		public function findNotasFilter() {
				$codEmpresa=( isset($_REQUEST['codEmpresa'])?$_REQUEST['codEmpresa']:NULL);
				$id=( isset($_REQUEST['id'])?$_REQUEST['id']:$this->key);
				$nroNota=( isset($_REQUEST['nroNota'])?$_REQUEST['nroNota']:NULL);

				$idPersonal=(isset($_REQUEST['idPersonal'])?$_REQUEST['idPersonal']:NULL);
				$tipoNota=( isset($_REQUEST['tipoNota'])?$_REQUEST['tipoNota']:NULL);
				$fechainicial=( isset($_REQUEST['fechainicial'])?$_REQUEST['fechainicial']:NULL);
				$fechafinal=( isset($_REQUEST['fechafinal'])?$_REQUEST['fechafinal']:NULL);
				$estadoNota=(isset($_REQUEST['estadoNota'])?$_REQUEST['estadoNota']:NULL);
				$cobrada=(isset($_REQUEST['cobrada'])?$_REQUEST['cobrada']:NULL);
				
				$str1=" (select 1 from movimientos where movimientos.idObjeto=cabnotas.idPersonal and movimientos.tipoObjeto='G' and movimientos.nroNota = cabnotas.nroNota ) ";
				$sql = "select cabnotas.*,personal.nombre as nomPersona,".
                       "(select count(1) AS nummov from movimientos where (movimientos.idObjeto=cabnotas.idPersonal and movimientos.tipoObjeto='G' and movimientos.nroNota = cabnotas.nroNota)) AS nummov ". 
                       " from cabnotas left join personal on personal.id=cabnotas.idPersonal ".
				       "where  " .
				    " cabnotas.id is not null " .
				    ($id != NULL ? " AND (cabnotas.id = :id )  " : "") .
				    ($codEmpresa != NULL ? " AND (cabnotas.codEmpresa = :codEmpresa)  " : "") .
				    ($nroNota != NULL ? " AND (nroNota like :nroNota)  " : "") .
					($idPersonal != NULL ? " AND (idPersonal in (:idPersonal,0))  " : "") .
					($estadoNota != NULL ? " AND (estadoNota = :estadoNota)  " : "").
					($fechainicial != NULL ? " AND (fecha >= :fechainicial)  " : "").
					($fechafinal != NULL ? " AND (fecha <= :fechafinal)  " : "") .
					($cobrada!=NULL ? ($cobrada=='0'?" and not exists ".$str1:" and  exists ".$str1):"")
					;

				$sql .= " ORDER BY nroNota"; 

				$stmt = ($this->link)->prepare($sql);

				if (strpos($sql, ':id ') !== false) $stmt->bindParam(':id',  $id);
				if (strpos($sql, ':codEmpresa') !== false) $stmt->bindParam(':codEmpresa',  $codEmpresa);
				if (strpos($sql, ':nroNota') !== false) $stmt->bindParam(':nroNota',  $nroNota);
				if (strpos($sql, ':idPersonal') !== false) $stmt->bindParam(':idPersonal',  $idPersonal);
				if (strpos($sql, ':estadoNota') !== false) $stmt->bindParam(':estadoNota',  $estadoNota);
				if (strpos($sql, ':fechainicial') !== false) $stmt->bindParam(':fechainicial',  $fechainicial);
				if (strpos($sql, ':fechafinal') !== false) $stmt->bindParam(':fechafinal',  $fechafinal);
				
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

		public function findEstadoNotas() {
		    //  $codEmpresa=( isset($_REQUEST['codEmpresa'])?$_REQUEST['codEmpresa']:NULL);
		    
		    $sql = "select CONCAT('NOTAS PEND.PAGO ',tabaux.valor1) as empresa,0 as facturas,count(*) as notas ".
		  		    " from cabnotas join tablaauxiliar tabaux on tabaux.codtabla=8 and tabaux.codElemento=cabnotas.codEmpresa  ".
		  		    "where  " .
		  		    " cabnotas.id is not null " .
		  		    //		  		    ($codEmpresa != NULL ? " AND (cabfacturas.codEmpresa = :codEmpresa)  " : "") .
		    " AND not exists  (select 1 from movimientos where movimientos.idObjeto=cabnotas.idPersonal and movimientos.tipoObjeto='G' and movimientos.nroNota = cabnotas.nroNota ) ".
		    " GROUP BY tabaux.valor1"
		        ;
		        
		        
		        $stmt = ($this->link)->prepare($sql);
		        
		        //		  		    if (strpos($sql, ':codEmpresa') !== false) $stmt->bindParam(':codEmpresa',  $codEmpresa);
		        
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
		
	public function guardarCabNotas() {
	    // en la case BD_APi hemos pasado el JSON al array $this->input
	 	    
	    $lastId=$this->guardarCabNotasGenerico($this->input['codEmpresa'],(isset($this->input['id'])?$this->input['id']:null), $this->input['nroNota'],$this->input['idPersonal'],
	               $this->input['fecha'], $this->input['totalNota'],$this->input['archivoDrive'],$this->input['estadoNota'],
	        $this->input['carpeta'],$this->input['comentarios']);
	    
	    echo '{ "id": '.$lastId.' }';
	}
	
	public function crearCabNotasFromDrive($codEmpresa=null,$name=null) {
	    $sql="select id from cabnotas where codEmpresa=:codEmpresa and archivoDrive=:archivoDrive";
	    $stmt = ($this->link)->prepare($sql);
	    $stmt->bindValue(':codEmpresa', $codEmpresa);//$codEmpresa
	    $stmt->bindValue(':archivoDrive', $name);
	    $stmt->execute();
	    $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
	    if (count($result)<=0){ // no existe factura
	        
    	    $sql="insert into cabnotas set codEmpresa=:codEmpresa,nroNota=-1,archivoDrive=:archivoDrive,".
    	   	    "estadoNota='PENDIENTE',fecha=now(),carpeta='',comentarios=:archivoDrive ".
    	   	    " ,user='".(isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."',ts=now() ";
    	   	$stmt = ($this->link)->prepare($sql);
    	    $stmt->bindValue(':codEmpresa', $codEmpresa);//$codEmpresa
    	    $stmt->bindValue(':archivoDrive', $name);
    	    $stmt->execute();
	    }
	}

	public function actualizarCabNotasFromDrive($codEmpresa,$nomDriveItem,$estado,$carpeta) {
	    $sql="update cabnotas set ".
	   	    "estadoNota='ENVIADA',carpeta=:carpeta ".
	   	    " ,user='".(isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."',ts=now() ".
	   	    " where codEmpresa=:codEmpresa and archivoDrive=:archivoDrive";
	    $stmt = ($this->link)->prepare($sql);
	    $stmt->bindValue(':codEmpresa', $codEmpresa);//$codEmpresa
	    $stmt->bindValue(':archivoDrive', $nomDriveItem);
	    $stmt->bindValue(':carpeta', $carpeta);
	    $stmt->execute();
	    
	}
	
	public function buscarUltimoNumero($codEmpresa,$idPersonal) {
	        $sql="select max(CAST(nroNota AS INT)) as numreg from cabnotas where codEmpresa=:codEmpresa and idPersonal=:idPersonal and estadoNota='ENVIADA'";
	        $stmt = ($this->link)->prepare($sql);
	        $stmt->bindParam(':codEmpresa', $codEmpresa);
	        $stmt->bindParam(':idPersonal', $idPersonal);
	        if  ($stmt->execute()) {
	            $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
	            if (count($result)<=0)  // no existen registros,num=1
	                $nroReg=1;
	                else  // si que existen, sumo 1 a la ultima
	                    $nroReg=$result[0]['numreg']+1;
	            return $nroReg;        
	        }
            return 1;
	}
	
	public function guardarCabNotasGenerico($codEmpresa,$id,$nroNota,$idPersonal,
	            $fecha,$totalNota,$archivoDrive, $estadoNota,$carpeta,$comentarios) {
	    // en la case BD_APi hemos pasado el JSON al array $this->input
    
	    if ($id!=0) {
	       $sql="update cabnotas ";
	       $where=" where id=:id ";
	    } else {
	        $sql="insert into cabnotas ";
	        $where="";
	    }
	    if ( $nroNota<0 && $idPersonal>"0") { // busco nro nota
	        $nroNota=$this->buscarUltimoNumero($codEmpresa, $idPersonal);
            
	    }
	    
	    $sql = $sql.
	    " set  codEmpresa=:codEmpresa,nroNota=:nroNota, idPersonal=:idPersonal,fecha=:fecha, ".
	    " totalNota=:totalNota ,".
	    " archivoDrive=:archivoDrive,estadoNota=:estadoNota,carpeta=:carpeta,comentarios=:comentarios ".
	    " ,user='".(isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."',ts=now() ".
	    $where;
	    $stmt = ($this->link)->prepare($sql);
	    if (strpos($sql, ':id ') !== false) $stmt->bindValue(':id', $id);
	    $stmt->bindValue(':codEmpresa',  $codEmpresa);
	    $stmt->bindValue(':nroNota',  $nroNota);
	    $stmt->bindValue(':idPersonal',  $idPersonal);
	    $stmt->bindValue(':fecha',  $fecha);
	    $stmt->bindValue(':totalNota', $totalNota);
	    $stmt->bindValue(':archivoDrive', $archivoDrive);
	    $stmt->bindValue(':estadoNota', $estadoNota);
	    $stmt->bindValue(':carpeta', $carpeta);
	    $stmt->bindValue(':comentarios', $comentarios);
	    
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
	
	public function generarPagoCobroNota($nroNota=null,$idPersonal=null) {
	    if ($nroNota==null) $nroNota=$_REQUEST['nroNota'];
	    if ($idPersonal==null) $idPersonal=$_REQUEST['idPersonal'];
	    
	    $sql="insert into movimientos (codEmpresa,tipoObjeto,idObjeto,nroNota,tipoOperacion,fecha,importe,descripcion,user,ts) ".
	   	    " select codEmpresa,'G',idPersonal,nroNota, 'NOMINA',curdate(), sum(totalNota),".
	   	    " concat('NOTA ',nroNota,' - ',min(comentarios)),".
	   	    "'".  (isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."',now() ".
	   	    " from cabnotas where nroNota=:nroNota and idPersonal=:idPersonal group by nroNota";
	    $stmt = ($this->link)->prepare($sql);
	    $stmt->bindValue(':nroNota', $nroNota);
	    $stmt->bindValue(':idPersonal', $idPersonal);
	    $stmt->execute();
	    echo "{'success':'Insertado movimiento de NOMINA en empleado ".$idPersonal." '}";
	    
	}
	            
}


  
?>