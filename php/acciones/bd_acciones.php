<?php

	require_once("../lib/bd_api_pdo.php");
	

	class bd_acciones extends BD_API {

		public function analiza_method() {
		    if (($this->key=='0') && ($this->method=="GET") && ($this->table!="acciones")) { // lo hemos llamado con otro metodo
		        
		        $func=$this->table; // nombre de la funcion a la que llamo de esta clase,  en table guardo el nombre del metodo
		        
		        $this->$func();
		        
		    } else {
		        
		        $this->table="acciones";
		        $this->execStandardMethod();
		        
		    }
		    
		}

		private function findAccionesFilter() {			
				$id=( isset($_REQUEST['id'])?$_REQUEST['id']:$this->key);
				$tipoObjeto=( isset($_REQUEST['tipoObjeto'])?$_REQUEST['tipoObjeto']:NULL);				
				$idObjeto=( isset($_REQUEST['idObjeto'])?$_REQUEST['idObjeto']:NULL);
				$idUserQuien=( isset($_REQUEST['idUserQuien'])?$_REQUEST['idUserQuien']:NULL);
				$fechaDesde=( isset($_REQUEST['fechaDesde'])?$_REQUEST['fechaDesde']:NULL);
				$fechaHasta=( isset($_REQUEST['fechaHasta'])?$_REQUEST['fechaHasta']:NULL);
				$descripcion=( isset($_REQUEST['descripcion'])?$_REQUEST['descripcion']:NULL);
				// idUserQuienProx viene en formato ['jvilata@vidawm.com','vjulia@vidawm.com']
				$arr_idUserQuienProx=( isset($_REQUEST['idUserQuienProx'])?json_decode($_REQUEST['idUserQuienProx'],true):NULL);
				$fechaProxDesde=( isset($_REQUEST['fechaProxDesde'])?$_REQUEST['fechaProxDesde']:NULL);
				$fechaProxHasta=( isset($_REQUEST['fechaProxHasta'])?$_REQUEST['fechaProxHasta']:NULL);
				$tipoAccion=( isset($_REQUEST['tipoAccion'])?$_REQUEST['tipoAccion']:NULL);
				$realizada=( isset($_REQUEST['realizada'])?$_REQUEST['realizada']:NULL);
				$str1="";
				if ($arr_idUserQuienProx!=NULL) {
    				    foreach ($arr_idUserQuienProx as $value) {
    				        if (strlen($str1)>0) $str1.=" OR ";
    				            $str1=$str1." (idUserQuienProx like '%".$value."%') ";
    				    }
				    
				} else 
				    if (isset($_REQUEST['idUserQuienProx'])) 
				        $str1=" (idUserQuienProx like '%".$_REQUEST['idUserQuienProx']."%') ";
				
				
				$sql = "select acciones.*,case when activos.nombre is null then entidades.nombre else activos.nombre end as nomObjeto from acciones ".
				       "  left join activos on acciones.tipoObjeto='A' and activos.id=acciones.idObjeto  ".
				       "  left join entidades on acciones.tipoObjeto='E' and entidades.id=acciones.idObjeto  ".
				       " where  " .
								
								" acciones.id is not null " .
					($id != NULL ? " AND (acciones.id = :id )  " : "") .
				    ($tipoObjeto != NULL ? " AND (acciones.tipoObjeto = :tipoObjeto)  " : "") .
				    ($idObjeto != NULL ? " AND (idObjeto = :idObjeto)  " : "") .
				    ($descripcion != NULL ? " AND (acciones.descripcion like :descripcion)  " : "") .
				    ($idUserQuien != NULL ? " AND (idUserQuien = :idUserQuien )  " : "") .
				    ($fechaDesde != NULL ? " AND (fecha >= :fechaDesde)  " : "") .
				    ($fechaHasta != NULL ? " AND (fecha <= :fechaHasta)  " : "") .
			//	    ($idUserQuienProx != NULL ? " AND (idUserQuienProx in (".$idUserQuienProx.") )  " : "") .
			         
				    ($str1!="" ? " AND (".$str1.")" : "") .
				    ($fechaProxDesde != NULL ? " AND (fechaProx >= :fechaProxDesde)  " : "") .
				    ($fechaProxHasta != NULL ? " AND (fechaProx <= :fechaProxHasta)  " : "") .
				    ($tipoAccion != NULL ? " AND (tipoAccion = :tipoAccion)  " : "").
				    ($realizada  != NULL ? " AND (realizada = :realizada)  " : "")
				    ;

				$sql .= " ORDER BY fechaProx"; 
//var_dump($idUserQuienProx);
				$stmt = ($this->link)->prepare($sql);

				if (strpos($sql, ':tipoObjeto') !== false) $stmt->bindValue(':tipoObjeto', $tipoObjeto);//jv.uso bindvalue porque asigno una expresion
				if (strpos($sql, ':id ') !== false) $stmt->bindParam(':id',  $id);
				if (strpos($sql, ':idObjeto') !== false) $stmt->bindParam(':idObjeto',  $idObjeto);
				if (strpos($sql, ':tipoAccion') !== false) $stmt->bindParam(':tipoAccion',  $tipoAccion);
				if (strpos($sql, ':idUserQuien ') !== false) $stmt->bindParam(':idUserQuien',  $idUserQuien);
			//	if (strpos($sql, ':idUserQuienProx') !== false) $stmt->bindParam(':idUserQuienProx',  $idUserQuienProx);
				if (strpos($sql, ':fechaDesde') !== false) $stmt->bindParam(':fechaDesde',  $fechaDesde);
				if (strpos($sql, ':fechaHasta') !== false) $stmt->bindParam(':fechaHasta',  $fechaHasta);
				if (strpos($sql, ':fechaProxDesde') !== false) $stmt->bindParam(':fechaProxDesde',  $fechaProxDesde);
				if (strpos($sql, ':fechaProxHasta') !== false) $stmt->bindParam(':fechaProxHasta',  $fechaProxHasta);
				if (strpos($sql, ':descripcion') !== false) $stmt->bindValue(':descripcion',  '%'.$descripcion.'%');
				if (strpos($sql, ':realizada') !== false) $stmt->bindValue(':realizada',  $realizada);
				
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
		
	
	private function guardarBD() {
	    $sql="select id from acciones where id=:id";
	    $stmt = ($this->link)->prepare($sql);
	    $stmt->bindParam(':id',  $_REQUEST['id']);
	    if  ($stmt->execute()) {
    	    $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
    	    if (count($result)<=0)  {// no existe Accion, lo creo
    	        $sql="insert into acciones ";
    	        $where="";
    	    } else { // si que existe, actualizo
    	        $sql="update acciones ";
    	        $where=" where id=:id";
    	    }
    	    
    	    $sql = $sql.
    	    " set  tipoObjeto=:tipoObjeto, idObjeto=:idObjeto, idUserQuien=:idUserQuien, fecha=:fecha, descripcion=:descripcion, fechaProx=:fechaProx, idUserQuienProx=:idUserQuienProx, tipoAccion=:tipoAccion ".
    	    " ,user='".(isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."',ts=now() ".
    	           $where;
    	    $stmt = ($this->link)->prepare($sql);
    	    if (strpos($sql, ':idAccion') !== false) $stmt->bindValue(':idAccion',  $_REQUEST['id']);
    	    $stmt->bindValue(':codEmpresa',  $_REQUEST['codEmpresa']);
    	    $stmt->bindValue(':nombre',  $_REQUEST['nombre']);
    	    $stmt->bindValue(':tipoAccion',  $_REQUEST['tipoAccion']);
    	    $stmt->bindValue(':estadoAccion',  $_REQUEST['estadoAccion']);
    	    $stmt->bindValue(':descripcion',  ( isset($_REQUEST['descripcion'])?$_REQUEST['descripcion']:NULL));
    	    
    	    try {
    	        $stmt->execute() ;
    	    }
    	    
    	    catch ( PDOException $Exception)  {
    	        die( $Exception->getMessage( )  );
    	    }
	    }
	}
}



	// Initiiate Library

	

	$api = new bd_acciones();

	$api->analiza_method();
  
?>