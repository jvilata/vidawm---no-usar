<?php

	require_once("../lib/bd_api_pdo.php");

	

	class bd_alternativos extends BD_API {

		public function analiza_method() {
		    // el POST cuando viene de un formulario hay que hacer a mano el insert con un metodo por ejemplo guardaBD en estea clase. Si vienen con JSON puede ser automatico
		    if ( (($this->method=="GET")||($this->method=="POST")) && ($this->table!="movimientos")) { // lo hemos llamado con otro metodo

				$func=$this->table; // nombre de la funcion a la que llamo de esta clase,  en table guardo el nombre del metodo

				$this->$func();

			} else {
			    if ($this->method=="GET" && isset($_REQUEST['idObjeto'])) {
			        $this->findMovimientosFilter();
			    } else {
    				$this->table="movimientos";
    				$this->execStandardMethod();
			    }
			}
			

		}

	
		private function findcProyeccionAlternativos() {
		    $arr_idActivo=( isset($_REQUEST['idActivo']) && $_REQUEST['idActivo']!=""?explode(",",$_REQUEST['idActivo']):NULL);
		    if ($arr_idActivo!=NULL) {
		        $strIdActivo="";
		        foreach ($arr_idActivo as $value) {
		            if (strlen($strIdActivo)>0) $strIdActivo.=" OR ";
		            $strIdActivo=$strIdActivo." (act1.id=".$value.") ";
		        }
		    }
		    
		    $arr_estadoActivo=( isset($_REQUEST['estadoActivo']) && $_REQUEST['estadoActivo']!=""?explode(",",$_REQUEST['estadoActivo']):NULL);
		    if ($arr_estadoActivo!=NULL) {
		        $str1="";
		        foreach ($arr_estadoActivo as $value) {
		            if (strlen($str1)>0) $str1.=" OR ";
		            $str1=$str1." (act1.estadoActivo=".$value.") ";
		        }
		    }
		    $arr_tipoActivo=( isset($_REQUEST['tipoActivo']) && $_REQUEST['tipoActivo']!=""?explode(",",$_REQUEST['tipoActivo']):NULL);
		    if ($arr_tipoActivo!=NULL) {
		        $strtipoActivo="";
		        foreach ($arr_tipoActivo as $value) {
		            if (strlen($strtipoActivo)>0) $strtipoActivo.=" OR ";
		            $strtipoActivo=$strtipoActivo." (act1.tipoActivo='".$value."') ";
		        }
		    }
		    
		    $arr_tipoProducto=( isset($_REQUEST['tipoProducto']) && $_REQUEST['tipoProducto']!=""?explode(",",$_REQUEST['tipoProducto']):NULL);
		    $strTipoProd="";
		    if ($arr_tipoProducto!=NULL) {
		        foreach ($arr_tipoProducto as $value) {
		            if (strlen($strTipoProd)>0) $strTipoProd.=" AND ";
		            $strTipoProd=$strTipoProd." (act1.tipoProducto like '%".$value."%') ";
		        }
		        
		    }
		    
		    
		    
	        $sql = "SELECT date_format(comp.fecha,'%Y') as ejercicio,act1.tipoActivo,".
                    "sum(case when comp.tipoOperacion='VALORACION'  then comp.importe else 0 end) as valoracion,".
                    "sum(case when comp.tipoOperacion='COMPROMETIDO' then comp.importe else 0 end)  as comprometido,".
                    "sum(case when comp.tipoOperacion='DISTRIBUCION' then comp.importe else 0 end) as distribucion,".
                    "sum(case when comp.tipoOperacion='COMPRA' then comp.importe else 0 end) as compra, ".
                    "sum(case when comp.tipoOperacion='VENTA' then comp.importe else 0 end) as venta, ".
                    "sum(case when comp.tipoOperacion='COBRO' then comp.importe else 0 end) as cobro ".
                "FROM movimientos comp  ".
            		"join activos as act1 on act1.codEmpresa = comp.codEmpresa  and ".
            		"	 act1.id=comp.idObjeto and comp.tipoObjeto='A' ".
                "WHERE comp.codEmpresa=:codEmpresa and ".
                	"( comp.tipoOperacion in ('COMPROMETIDO','DISTRIBUCION', 'COMPRA', 'VENTA', 'COBRO') or (comp.tipoOperacion='VALORACION' and month(comp.fecha)=1) ) and ".
                	"comp.importe > 0  ";           
		    $sql=$sql.
		    ($arr_idActivo!=NULL ? " AND (". $strIdActivo .")" : "") .
		    // ($_REQUEST['id'] != NULL ? " AND (act1.id = :id )  " : "").
		    // ($_REQUEST['tipoActivo'] != NULL ? " AND (act1.tipoActivo = :tipoActivo)  " : "").
		    ($arr_tipoActivo!=NULL ? " AND (".$strtipoActivo.")" : "") .
		    ($arr_estadoActivo!=NULL ? " AND (".$str1.")" : "") .
		    ($arr_tipoProducto!=NULL ? " AND (".$strTipoProd.")" : "") .
		    ($_REQUEST['idEntidad'] != NULL ? " AND (act1.idEntidad = :idEntidad)  " : "").
		    ($_REQUEST['computa'] != NULL ? " AND (act1.computa = :computa)  " : "").
		    ($_REQUEST['nombre'] != NULL ? " AND (act1.nombre like :nombre)  " : "").
		    ($_REQUEST['anyoDesde'] != NULL ? " AND (date_format(comp.fecha,'%Y')  >= :anyoDesde)  " : "")
		    // ($_REQUEST['fechaDesde'] != NULL ? " AND (comp.fecha  >= :fechaDesde)  " : "").
		    // ($_REQUEST['fechaHasta'] != NULL ? " AND (comp.fecha  <= :fechaHasta)  " : "").
		    ;
	        $sql = $sql . " GROUP BY date_format(comp.fecha,'%Y') ";
// echo $sql;	    
		    $stmt = ($this->link)->prepare($sql);
		    
		    if (strpos($sql, ':id ') !== false) $stmt->bindParam(':id', $_REQUEST['id']);
		    if (strpos($sql, ':tipoActivo') !== false) $stmt->bindValue(':tipoActivo',  $_REQUEST['tipoActivo']);
		    if (strpos($sql, ':codEmpresa ') !== false) $stmt->bindParam(':codEmpresa', $_REQUEST['codEmpresa']);
		    if (strpos($sql, ':idEntidad') !== false) $stmt->bindValue(':idEntidad',  $_REQUEST['idEntidad']);
		    if (strpos($sql, ':computa') !== false) $stmt->bindParam(':computa', $_REQUEST['computa']);
		    if (strpos($sql, ':nombre') !== false) $stmt->bindValue(':nombre',  '%'.$_REQUEST['nombre'].'%');
		    if (strpos($sql, ':anyoDesde') !== false) $stmt->bindValue(':anyoDesde',  $_REQUEST['anyoDesde']);
		    // if (strpos($sql, ':fechaDesde') !== false) $stmt->bindValue(':fechaDesde',  $_REQUEST['fechaDesde']);
		    // if (strpos($sql, ':fechaHasta') !== false) $stmt->bindValue(':fechaHasta',  $_REQUEST['fechaHasta']);
		    
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
	
}

	// Initiiate Library

	$api = new bd_alternativos();

	$api->analiza_method();
  
?>