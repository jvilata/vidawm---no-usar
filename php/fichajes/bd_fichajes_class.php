<?php

	require_once("../lib/bd_api_pdo.php");

	

	class bd_fichajes extends BD_API {

		public function analiza_method() {
		   
			if (($this->key=='0') && ($this->method=="GET"||$this->method=="POST") &&($this->table!="fichajes")) { // lo hemos llamado con otro metodo

				$func=$this->table; // nombre de la funcion a la que llamo de esta clase,  en table guardo el nombre del metodo

				$this->$func();

			} else {
			    
				$this->table="fichajes";

			    $this->execStandardMethod();

			}	

		}

		

		public function findFichajesFilter($tipo=null) {

				$idpersonal=( isset($_REQUEST['idpersonal'])?$_REQUEST['idpersonal']:NULL);
				$tipoJornada=( isset($_REQUEST['tipoJornada'])?$_REQUEST['tipoJornada']:NULL);
				$fecha=( isset($_REQUEST['fecha'])?$_REQUEST['fecha']:NULL);
				$fechaD=( isset($_REQUEST['fechaD'])?$_REQUEST['fechaD']:NULL);
				$fechaH=( isset($_REQUEST['fechaH'])?$_REQUEST['fechaH']:NULL);
				$estado=( isset($_REQUEST['estado'])?$_REQUEST['estado']:NULL);
				$ultimo=( isset($_REQUEST['ultimo'])?1:NULL);
				if ($idpersonal==0) $idpersonal=null;
				
				$sql = "select * from fichajes where  " .
				    " id is not null " .
					($idpersonal != NULL ? " AND (idpersonal=:idpersonal)  " : "") .
					($tipoJornada != NULL ? " AND (tipoJornada = :tipoJornada)  " : "") .
					($estado != NULL ? " AND (estado = :estado)  " : "") .
					($fecha != NULL ? " AND (date_format(fechaInicio,'%Y-%m-%d') = :fechaDia)  " : "") .
					($fechaD != NULL ?($fechaH != NULL? " AND (date_format(fechaFin,'%Y-%m-%d') >= :fechaDDia AND date_format(fechainicio,'%Y-%m-%d') <= :fechaHDia) ": " AND (date_format(fechaInicio,'%Y-%m-%d') >= :fechaDDia)  " ): "") .
					($fechaH != NULL ? ($fechaD != NULL? " AND (date_format(fechainicio,'%Y-%m-%d') <= :fechaHDia AND date_format(fechaFin,'%Y-%m-%d') >= :fechaDDia) ":" AND (fechaFin is null or date_format(fechaFin,'%Y-%m-%d') <= :fechaHDia)  ") : "") .
					($ultimo != NULL ? " AND (fechaInicio = (select max(fechaInicio) from fichajes where idpersonal=:idpersonal))  " : "") 
					;

				$sql .= " ORDER BY idpersonal,fechaInicio"; 

 

				$stmt = ($this->link)->prepare($sql);

				if (strpos($sql, ':idpersonal') !== false) $stmt->bindParam(':idpersonal',  $idpersonal);
				if (strpos($sql, ':tipoJornada') !== false) $stmt->bindParam(':tipoJornada',  $tipoJornada);
				if (strpos($sql, ':estado') !== false) $stmt->bindParam(':estado',  $estado);
				if (strpos($sql, ':fechaDia') !== false) $stmt->bindValue(':fechaDia',  date_format(date_create_from_format('Y-m-d\TH:i:s', $fecha),"Y-m-d"));
				if (strpos($sql, ':fechaDDia') !== false) $stmt->bindValue(':fechaDDia',  date_format(date_create_from_format('Y-m-d\TH:i:s', $fechaD),"Y-m-d"));
				if (strpos($sql, ':fechaHDia') !== false) $stmt->bindValue(':fechaHDia',  date_format(date_create_from_format('Y-m-d\TH:i:s', $fechaH),"Y-m-d"));
				
				try {
					if  ($stmt->execute()) {
						$result= $stmt->fetchAll(PDO::FETCH_ASSOC);
						if ($tipo!=null) return $result;
						else $this->devolverResultados($result);
					}
				}

				catch ( PDOException $Exception)  {
					die( $Exception->getMessage( )  );
				}
		}
		
		public function comprobarHoras($id=null,$idpersonal=null,$fechaD=null,$fechaH=null) {
		    // vemos horas ordinarias y extra
		    if (isset($_GET['id'])) $id=$_GET['id'];
		    if (isset($_GET['idpersonal'])) $idpersonal=$_GET['idpersonal'];
		    if (isset($_GET['fechaD'])) $fechaD=$_GET['fechaD'];
		    if (isset($_GET['fechaH'])) $fechaH=$_GET['fechaH'];
		    $strid="";
		    if ($id!=null && $id>0) $strid=" fichajes.id<>:id and ";
		    $sql="select idpersonal,personal.horasJornada,tipoJornada,estado,sum(difDias) as dias,sum(difHoras) as horas, sum(difMinutos) as minutos ".
		         " from fichajes,personal where ".$strid. " personal.id=:idpersonal and idpersonal=:idpersonal  ".
		          " and (date_format(fechaFin,'%Y-%m-%d') >= :fechaD AND date_format(fechainicio,'%Y-%m-%d') <= :fechaH) " .
		          " and (date_format(fechainicio,'%Y-%m-%d') <= :fechaH AND date_format(fechaFin,'%Y-%m-%d') >= :fechaD) " .
		         " group by idpersonal,personal.horasJornada,tipoJornada,estado";
		    $stmt = ($this->link)->prepare($sql);
		    if ($id!=null && $id>0) $stmt->bindValue(':id', $id);
		    $stmt->bindValue(':idpersonal',$idpersonal);
		    $stmt->bindValue(':fechaD', date_format(date_create_from_format('Y-m-d\TH:i:s', $fechaD),"Y-m-d"));
		    $stmt->bindValue(':fechaH', date_format(date_create_from_format('Y-m-d\TH:i:s', $fechaH),"Y-m-d"));
		    try {
		        if  ($stmt->execute()) {
		            $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
		            if ($id!=null) return $result; // devuelvo array
		            else $this->devolverResultados($result); // json
		        }
		    }
		    
		    catch ( PDOException $Exception)  {
		        die( $Exception->getMessage( )  );
		    }
		    
		}
		
		private function guardarBD() {
		    // nos aseguramos de que no haya solapamiento
		    if (isset($_POST['id'])) $id=$_POST['id'];
		    if (isset($_POST['idpersonal'])) $idpersonal=$_POST['idpersonal'];
		    if (isset($_POST['fechaInicio'])) $fechaInicio=date_create_from_format('Y-m-d H:i:s',$_POST['fechaInicio']);
		    if (isset($_POST['fechaFin'])) $fechaFin=date_create_from_format('Y-m-d H:i:s',$_POST['fechaFin']);
		    
		    $dif=date_diff($fechaInicio,$fechaFin,true);
		    
		    
		    $sql="select fichajes.* from fichajes where id<>:id  and idpersonal=:idpersonal and ".
		      " ((fechaInicio<=:fechaInicio and fechaFin>=:fechaInicio) or ".
		      " (fechaInicio<=:fechaFin and fechaFin>=:fechaFin)) ";
		    $stmt = ($this->link)->prepare($sql);
		    $stmt->bindValue(':id', $id);
		    $stmt->bindValue(':idpersonal', $idpersonal);
		    $stmt->bindValue(':fechaInicio',date_format($fechaInicio,'Y-m-d H:i:s'));
		    $stmt->bindValue(':fechaFin', date_format($fechaFin,'Y-m-d H:i:s'));
		    if  ($stmt->execute()) {
		        $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
		        if (count($result)>0) {// hay solapamiento
		            echo '{ "failure": "Hay solapamiento con otras fechas registradas:'.$result[0]['tipoJornada'].': '.$result[0]['fechaInicio'].' - '.$result[0]['fechaFin'].'" }';
		            return;
		         } 
		         else {
		             $result=$this->comprobarHoras($id,$idpersonal,substr($_POST['fechaInicio'],0,10).'T00:00:00',substr($_POST['fechaFin'],0,10).'T00:00:00');
		             if ($result[0]['tipoJornada']=='ORDINARIA' && intval($result[0]['horas'])+$dif->h>$result[0]['horasJornada']) {
		                 echo '{ "failure": "Se quieren registrar '.(intval($result[0]['horas'])+$dif->h).' horas > jornada del empleado '.$result[0]['horasJornada'].' horas" }';
		                 return;
		             }
		         }
		    }
		            
		    $sql=" set idpersonal=:idpersonal,fechaInicio=:fechaInicio,fechaFin=:fechaFin,".
		       "tipoJornada=:tipoJornada,longitudInicio=:longitudInicio,latitudInicio=:latitudInicio,".
		       "longitudFin=:longitudFin,latitudFin=:latitudFin,estado=:estado,".
		       "difDias=:difDias,difHoras=:difHoras,difMinutos=:difMinutos,".
		       "user='".  (isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."',".
		       "ts=now() ";
		    if ($_POST['id']>0) {
		         $sql="update fichajes ".$sql." where id=:id";
		    } else {
		        $sql="insert into fichajes ". $sql;
		    }
		    
		    $stmt = ($this->link)->prepare($sql);
		    if ($_POST['id']>0)  $stmt->bindValue(':id', $id);
		    $stmt->bindValue(':idpersonal', $idpersonal);
		    $stmt->bindValue(':fechaInicio', date_format($fechaInicio,'Y-m-d H:i:s'));
		    $stmt->bindValue(':tipoJornada', $_POST['tipoJornada']);
		    $stmt->bindValue(':longitudInicio', 0);
		    $stmt->bindValue(':latitudInicio', 0);
		    $stmt->bindValue(':fechaFin', date_format($fechaFin,'Y-m-d H:i:s'));
		    $stmt->bindValue(':estado', $_POST['estado']);
		    $stmt->bindValue(':longitudFin', 0);
		    $stmt->bindValue(':latitudFin', 0);
		    $stmt->bindValue(':difDias', $dif->days);
		    $stmt->bindValue(':difHoras', $dif->h);
		    $stmt->bindValue(':difMinutos', $dif->i);
		    try {
		        if  ($stmt->execute()) {
		            echo '{ "success": "Insertado movimiento" }';
		        }
		    }
		    
		    catch ( PDOException $Exception)  {
		        die( $Exception->getMessage( )  );
		    }
		}

	}

?>