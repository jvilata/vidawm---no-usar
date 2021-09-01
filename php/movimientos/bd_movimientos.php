<?php

	require_once("../lib/bd_api_pdo.php");

	

	class bd_movimientos extends BD_API {

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

		

		private function findMovimientosFilter() {
				$id=( isset($_REQUEST['idObjeto'])?$_REQUEST['idObjeto']:$this->key);

				$sql = "select * from movimientos " .
				       "where  " .
				    " idObjeto is not null " .
				    ($id != NULL ? " AND (idObjeto = :id)  " : "") .
				    ($_REQUEST['tipoObjeto'] != NULL ? ($_REQUEST['tipoObjeto'] =='N' ? " AND (tipoObjeto IN ('N','G')) ":" AND (tipoObjeto = :tipoObjeto)  " ): "")
				    ;

				$stmt = ($this->link)->prepare($sql);

				if (strpos($sql, ':id') !== false) $stmt->bindParam(':id',  $id);
				if (strpos($sql, ':tipoObjeto') !== false) $stmt->bindValue(':tipoObjeto',  $_REQUEST['tipoObjeto']);
				
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
		//findemesesvaloracion - lista de meses donde tenemos movimientos
		private function findMesesMovimientos() {
		    $sql = "select distinct date_format(movimientos.fecha,'%m/%Y') as mes from movimientos ".
		        " WHERE tipoOperacion='VALORACION' order by date_format(movimientos.fecha,'%Y%m') desc";
	
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
		
		private function findcvaloraciones() {

		    $arr_estadoActivo=( isset($_REQUEST['estadoActivo'])?json_decode($_REQUEST['estadoActivo'],true):NULL);
		    if ($arr_estadoActivo!=NULL) {
		        $strEstadoActivo="";
		        foreach ($arr_estadoActivo as $value) {
		            if (strlen($strEstadoActivo)>0) $strEstadoActivo.=" OR ";
		            $strEstadoActivo=$strEstadoActivo." (estadoActivo=".$value.") ";
		        }
		    }
		    
		    $arr_tipoProducto=( isset($_REQUEST['tipoProducto'])?json_decode($_REQUEST['tipoProducto'],true):NULL);
		    $strTipoProd="";
		    if ($arr_tipoProducto!=NULL) {
		        foreach ($arr_tipoProducto as $value) {
		            if (strlen($strTipoProd)>0) $strTipoProd.=" AND ";
		            $strTipoProd=$strTipoProd." (tipoProducto like '%".$value."%') ";
		        }
		        
		    }
		    
		    $sql = "select * from cvaloraciones " .
		  		    "where  " .
		  		    " id is not null " .
		  		    ($_REQUEST['estadoActivo'] != NULL ? " AND (estadoActivo = :estadoActivo) ":"").
		  		    ($_REQUEST['codEmpresa'] != NULL ? " AND (codEmpresa = :codEmpresa )  " : "").
//		  		    ($_REQUEST['estadoActivo'] != NULL ? " AND (estadoActivo = :estadoActivo )  " : "").
		            ($arr_estadoActivo!=NULL ? " AND (".$strEstadoActivo.")" : "") .
		            ($arr_tipoProducto!=NULL ? " AND (".$strTipoProd.")" : "") .
		  		    ($_REQUEST['id'] != NULL ? " AND (id = :id )  " : "").
		  		    ($_REQUEST['tipoActivo'] != NULL ? " AND (tipoActivo = :tipoActivo)  " : "").
		  		    ($_REQUEST['computa'] != NULL ? " AND (computa = :computa)  " : "").
		  		    ($_REQUEST['idEntidad'] != NULL ? " AND (idEntidad = :idEntidad)  " : "").
		  		    ($_REQUEST['nombre'] != NULL ? " AND (nombre like :nombre)  " : "").
		  		    ($_REQUEST['fechaDesde'] != NULL ? " AND (fecha  >= :fechaDesde)  " : "").
		  		    ($_REQUEST['fechaHasta'] != NULL ? " AND (fecha  <= :fechaHasta)  " : "").
		  		    ($_REQUEST['mes'] != NULL ? " AND (date_format(fecha,'%m/%Y')  = :mes)  " : "").
		  		    ($_REQUEST['tipoOperacion'] != NULL ? " AND (tipoOperacion= :tipoOperacion)  " : "")
		  		    ;
	  		    
		  		    $stmt = ($this->link)->prepare($sql);
		  		    
		  		    if (strpos($sql, ':id ') !== false) $stmt->bindParam(':id', $_REQUEST['id']);
		  		    if (strpos($sql, ':estadoActivo') !== false) $stmt->bindValue(':estadoActivo',  $_REQUEST['estadoActivo']);
		  		    if (strpos($sql, ':tipoActivo') !== false) $stmt->bindValue(':tipoActivo',  $_REQUEST['tipoActivo']);
		  		    if (strpos($sql, ':codEmpresa ') !== false) $stmt->bindParam(':codEmpresa', $_REQUEST['codEmpresa']);
		  		    if (strpos($sql, ':computa ') !== false) $stmt->bindParam(':computa', $_REQUEST['computa']);
		  		    if (strpos($sql, ':idEntidad') !== false) $stmt->bindValue(':idEntidad',  $_REQUEST['idEntidad']);
		  		    if (strpos($sql, ':nombre') !== false) $stmt->bindValue(':nombre',  '%'.$_REQUEST['tipoActivo'].'%');
		  		    if (strpos($sql, ':fechaDesde') !== false) $stmt->bindValue(':fechaDesde',  $_REQUEST['fechaDesde']);
		  		    if (strpos($sql, ':fechaHasta') !== false) $stmt->bindValue(':fechaHasta',  $_REQUEST['fechaHasta']);
		  		    if (strpos($sql, ':mes') !== false) $stmt->bindValue(':mes',  $_REQUEST['mes']);
		  		    if (strpos($sql, ':tipoOperacion') !== false) $stmt->bindValue(':tipoOperacion',  $_REQUEST['tipoOperacion']);
		  		    
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
		
		private function findcMovimientosComparado() {
		    $arr_estadoActivo=( isset($_REQUEST['estadoActivo'])?json_decode($_REQUEST['estadoActivo'],true):NULL);
		    if ($arr_estadoActivo!=NULL) {
		        $str1="";
		        foreach ($arr_estadoActivo as $value) {
		            if (strlen($str1)>0) $str1.=" OR ";
		            $str1=$str1." (curval.estadoActivo=".$value.") ";
		        }
		    }
		    
		    $arr_tipoProducto=( isset($_REQUEST['tipoProducto'])?json_decode($_REQUEST['tipoProducto'],true):NULL);
		    $strTipoProd="";
		    if ($arr_tipoProducto!=NULL) {
		        foreach ($arr_tipoProducto as $value) {
		            if (strlen($strTipoProd)>0) $strTipoProd.=" AND ";
		            $strTipoProd=$strTipoProd." (curval.tipoProducto like '%".$value."%') ";
		        }
		        
		    }
		    
		    $sqlcompras=",coalesce((SELECT SUM(comven.importe)  ".
		  		    " FROM cvaloraciones as comven WHERE comven.tipoOperacion in ('COMPRA') and comven.idActivo=curval.idActivo and ( (month(curval.fecha)=1 and year(comven.fecha)=year(curval.fecha)-1) or (month(curval.fecha)>1 and year(comven.fecha)=year(curval.fecha)) ) ),0) as impcompras  ";
		    $sqlcompventas=",coalesce((SELECT SUM(case when comven.tipoOperacion='COMPRA' then comven.importe when comven.tipoOperacion='VENTA' then -comven.importe else 0 end)  ".
		  		    " FROM cvaloraciones as comven WHERE comven.tipoOperacion in ('COMPRA','VENTA') and comven.idActivo=curval.idActivo and ( (month(curval.fecha)=1 and year(comven.fecha)=year(curval.fecha)-1) or (month(curval.fecha)>1 and year(comven.fecha)=year(curval.fecha)) ) ),0) as impcompvent  ";
		    $sqlcompventastotales=",coalesce((SELECT SUM(case when comven.tipoOperacion='COMPRA' then comven.importe when comven.tipoOperacion='VENTA' then -comven.importe else 0 end)  ".
		  		    " FROM cvaloraciones as comven WHERE comven.tipoOperacion in ('COMPRA','VENTA') and comven.idActivo=curval.idActivo ),0) as impcompventastotales  ";
		    $sqlcobropago=",coalesce((SELECT SUM(case when comven.tipoOperacion='COBRO' then comven.importe when comven.tipoOperacion='PAGO' then -comven.importe else 0 end)  ".
		  		    " FROM cvaloraciones as comven WHERE comven.tipoOperacion in ('COBRO','PAGO') and comven.idActivo=curval.idActivo and ( (month(curval.fecha)=1 and year(comven.fecha)=year(curval.fecha)-1) or (month(curval.fecha)>1 and year(comven.fecha)=year(curval.fecha)) ) ),0) as impcobropago  ";
		    $sqlfacturas=",coalesce((SELECT SUM(base) ".
		  		    " FROM cfacturas WHERE tipoFactura='EMITIDA' and cfacturas.idActivo=curval.idActivo and ( (month(curval.fecha)=1 and year(cfacturas.fecha)=year(curval.fecha)-1) or (month(curval.fecha)>1 and year(cfacturas.fecha)=year(curval.fecha)) ) ),0) as facturado ";
		    $sqlcompventas2Y=",coalesce((SELECT SUM(case when comven.tipoOperacion='COMPRA' then comven.importe when comven.tipoOperacion='VENTA' then -comven.importe else 0 end)  ".
		  		    " FROM cvaloraciones as comven WHERE comven.tipoOperacion in ('COMPRA','VENTA') and comven.idActivo=curval.idActivo and ( (month(curval.fecha)=1 and year(comven.fecha)>=year(curval.fecha)-3) or (month(curval.fecha)>1 and year(comven.fecha)>=year(curval.fecha)-2) ) ),0) as impcompvent2Y  ";
		    $sqlcobropago2Y=",coalesce((SELECT SUM(case when comven.tipoOperacion='COBRO' then comven.importe when comven.tipoOperacion='PAGO' then -comven.importe else 0 end)  ".
		  		    " FROM cvaloraciones as comven WHERE comven.tipoOperacion in ('COBRO','PAGO') and comven.idActivo=curval.idActivo and ( (month(curval.fecha)=1 and year(comven.fecha)>=year(curval.fecha)-3) or (month(curval.fecha)>1 and year(comven.fecha)>=year(curval.fecha)-2) ) ) ,0) as impcobropago2Y  ";
		    $sqlfacturas2Y=",coalesce((SELECT SUM(base) ".
		  		    " FROM cfacturas WHERE tipoFactura='EMITIDA' and cfacturas.idActivo=curval.idActivo and ( (month(curval.fecha)=1 and year(cfacturas.fecha)>=year(curval.fecha)-3) or (month(curval.fecha)>1 and year(cfacturas.fecha)>=year(curval.fecha)-2) ) ),0) as facturado2Y ";
            $sqlcomprometido=", coalesce((select sum(movcom.importe) ". 
                    "FROM movimientos movcom WHERE movcom.tipoOperacion='COMPROMETIDO' and movcom.idObjeto=curval.idActivo and date_format(movcom.fecha,'%Y/%m')>=date_format(curval.fecha,'%Y/%m')), 0) as comprometido  "	 ;
		    $sql = "SELECT curval.*, ".
		       "minval.fecha as minval_fecha,minval.importe as minval_importe,". 
		            " valant.fecha as valant_fecha,valant.importe as valant_importe, ".
		            " cval2Y.fecha as cval2Y_fecha,cval2Y.importe as cval2Y_importe ".
		            // 		            " curval.importe-valant.importe as difanterior,curval.importe-(minval.importe) as difinicio,".
// 		            " case when isnull(minval.importe) then 0 else (curval.importe-(minval.importe))/minval.importe end as rentabinicio ". 
                    $sqlcompras .
		            $sqlcompventas .
		            $sqlcompventastotales .
		            $sqlfacturas .
		            $sqlcobropago .
		            $sqlcompventas2Y .
		            $sqlfacturas2Y .
		            $sqlcobropago2Y . 
		            $sqlcomprometido .
		            " FROM cvaloraciones as curval ".
		  		    " left join (SELECT * FROM cvaloraciones   ) as valant ".
		  		    "  on valant.idActivo=curval.idActivo and date_format(valant.fecha,'%m%Y') = date_format(date_sub(curval.fecha, interval 1 month),'%m%Y') and valant.tipoOperacion=curval.tipoOperacion ".
		  		    " left join (SELECT * FROM cvaloraciones cval WHERE cval.codEmpresa=:codEmpresa)  as minval ".
		  		    "  on minval.idActivo=curval.idActivo and minval.tipoOperacion=curval.tipoOperacion and (date_format(minval.fecha,'%m%Y')=(case when month(curval.fecha)=1 then concat('01',year(curval.fecha)-1) else concat('01',year(curval.fecha)) end) )   ".
		  		    " left join (SELECT * FROM cvaloraciones cval2Y WHERE cval2Y.codEmpresa=:codEmpresa)  as cval2Y ".
		  		    "  on cval2Y.idActivo=curval.idActivo and cval2Y.tipoOperacion=curval.tipoOperacion and (date_format(cval2Y.fecha,'%m%Y')=(case when month(curval.fecha)=1 then concat('01',year(curval.fecha)-3) else concat('01',year(curval.fecha)-2) end) )   ".
		  		    " WHERE curval.codEmpresa=:codEmpresa ";
		            
		    $sql=$sql.
		    ($_REQUEST['id'] != NULL ? " AND (curval.id = :id )  " : "").
		    ($_REQUEST['tipoActivo'] != NULL ? " AND (curval.tipoActivo = :tipoActivo)  " : "").
//		    ($_REQUEST['estadoActivo'] != NULL ? " AND (curval.estadoActivo = :estadoActivo) ":"").
		    ($arr_estadoActivo!=NULL ? " AND (".$str1.")" : "") .
		    ($arr_tipoProducto!=NULL ? " AND (".$strTipoProd.")" : "") .
		    ($_REQUEST['idEntidad'] != NULL ? " AND (curval.idEntidad = :idEntidad)  " : "").
		    ($_REQUEST['importem0'] != NULL ? ($_REQUEST['importem0']=='1' ? " AND (curval.importe <> 0) ": "") : "").
		    ($_REQUEST['computa'] != NULL ? " AND (curval.computa = :computa)  " : "").
		    ($_REQUEST['nombre'] != NULL ? " AND (curval.nombre like :nombre)  " : "").
		    ($_REQUEST['fechaDesde'] != NULL ? " AND (curval.fecha  >= :fechaDesde)  " : "").
		    ($_REQUEST['fechaHasta'] != NULL ? " AND (curval.fecha  <= :fechaHasta)  " : "").
		    ($_REQUEST['mes'] != NULL ? " AND (date_format(curval.fecha,'%m/%Y')  = :mes)  " : "").
		    ($_REQUEST['tipoOperacion'] != NULL ? " AND (curval.tipoOperacion= :tipoOperacion)  " : "")
		    ;
	    
		    $stmt = ($this->link)->prepare($sql);
		    
		    if (strpos($sql, ':id ') !== false) $stmt->bindParam(':id', $_REQUEST['id']);
		    if (strpos($sql, ':tipoActivo') !== false) $stmt->bindValue(':tipoActivo',  $_REQUEST['tipoActivo']);
//		    if (strpos($sql, ':estadoActivo') !== false) $stmt->bindValue(':estadoActivo',  $_REQUEST['estadoActivo']);
		    if (strpos($sql, ':codEmpresa ') !== false) $stmt->bindParam(':codEmpresa', $_REQUEST['codEmpresa']);
		    if (strpos($sql, ':idEntidad') !== false) $stmt->bindValue(':idEntidad',  $_REQUEST['idEntidad']);
		    if (strpos($sql, ':computa') !== false) $stmt->bindParam(':computa', $_REQUEST['computa']);
		    if (strpos($sql, ':nombre') !== false) $stmt->bindValue(':nombre',  '%'.$_REQUEST['nombre'].'%');
		    if (strpos($sql, ':fechaDesde') !== false) $stmt->bindValue(':fechaDesde',  $_REQUEST['fechaDesde']);
		    if (strpos($sql, ':fechaHasta') !== false) $stmt->bindValue(':fechaHasta',  $_REQUEST['fechaHasta']);
		    if (strpos($sql, ':mes') !== false) $stmt->bindValue(':mes',  $_REQUEST['mes']);
		    if (strpos($sql, ':tipoOperacion') !== false) $stmt->bindValue(':tipoOperacion',  $_REQUEST['tipoOperacion']);
		    
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
		
		private function borrarValoraciones() {
		    //primero borramos si existe previo
		    $sql="delete from movimientos  where codEmpresa=:codEmpresa and tipoObjeto='A' and tipoOperacion='VALORACION' ".
		  		    "and fecha=:fecha  ".
		    ($_REQUEST['idEntidad'] != NULL ? " AND  exists (select e.id from activos a,entidades e where a.idEntidad=e.id and a.id=movimientos.idObjeto and e.id= :idEntidad )  " : "");
		    
		    $stmt = ($this->link)->prepare($sql);
		    if (strpos($sql, ':idEntidad') !== false) $stmt->bindValue(':idEntidad',  $_REQUEST['idEntidad']);
		    $stmt->bindValue(':fecha',  $_REQUEST['fecha']);
		    $stmt->bindValue(':codEmpresa',  $_REQUEST['codEmpresa']);
		    try {
		        $stmt->execute() ;
		        echo '{ "result": "OK" }';
		    } catch ( PDOException $Exception)  {
		        die( $Exception->getMessage( )  );
		    }
		}
		
		private function generarValoraciones() {
		    // generamos registros de movimientos copiados de la ultima valoracion del activo, si no existe a 0
		 
		    try {
		       		        
		    // recorremos todos los activos seleccionando ultima valoracion
// 		    $sql="insert into movimientos (codEmpresa,tipoObjeto,idObjeto,tipoOperacion,fecha,participaciones,precioUnitario,importe,retencion,descripcion,user,ts) ".
// 		  		" select :codEmpresa,'A',activos.id,'VALORACION',:fecha,participaciones,precioUnitario,importe,retencion,'Generada',:user,:ts ".
// 		       " from activos left join movimientos on activos.id=movimientos.tipoObjeto='A' and movimientos.idObjeto=activos.id and movimientos.tipoOperacion='VALORACION' ".
// 		       "  and movimientos.fecha= (select max(fecha) from movimientos mov where mov.tipoObjeto='A' and mov.tipoOperacion='VALORACION' and mov.idObjeto=movimientos.idObjeto) ".
// 		       " where activos.codEmpresa=:codEmpresa and activos.estadoActivo=1";
		    $sql="insert into movimientos (codEmpresa,tipoObjeto,idObjeto,tipoOperacion,fecha,participaciones,precioUnitario,importe,retencion,descripcion,user,ts) ".
		  		    " select :codEmpresa,'A',activos.id,'VALORACION',:fecha,participaciones,precioUnitario,importe,retencion,'Generada',:user,:ts ".
		  		    " from activos ".
		  		    ($_REQUEST['idEntidad'] != NULL ? "  inner join entidades e on e.id=activos.idEntidad and e.id=:idEntidad ":" ").
                    "  inner join movimientos on activos.id=movimientos.tipoObjeto='A' and movimientos.idObjeto=activos.id and movimientos.tipoOperacion='VALORACION' ".
		  		    "  and date_format(movimientos.fecha,'%m/%Y')= :mes ".
		  		    " where activos.codEmpresa=:codEmpresa and activos.estadoActivo in (1,4)";
		    
		    $stmt = ($this->link)->prepare($sql);
		    $stmt->bindValue(':mes',  $_REQUEST['mes']);
		    if (strpos($sql, ':idEntidad') !== false) $stmt->bindValue(':idEntidad',  $_REQUEST['idEntidad']);
		    $stmt->bindValue(':fecha',  $_REQUEST['fecha']);
		    $stmt->bindValue(':codEmpresa',  $_REQUEST['codEmpresa']);
		    $stmt->bindValue(':user',  $_REQUEST['user']);
		    $stmt->bindValue(':ts',  $_REQUEST['ts']);
		    $stmt->execute() ;
		    echo '{ "result": "OK" }';
		    
		} catch ( PDOException $Exception)  {
		        die( $Exception->getMessage( )  );
		    }
	}
	
	/*
	 * GRAFICOS
	 */
	private function findcvaloracionesActivo() {
	    $sql = "select 'Euros' as serie,date_format(fecha,'%m/%Y') AS etiquetavalor,SUM(case when tipoOperacion='VALORACION' then importe else 0 end)  AS valor, ".
	   	    " SUM(case when tipoOperacion in ('COBRO','COMPRA') then importe else 0 end)  AS valor1, ".
	   	    " SUM(case when tipoOperacion in ('PAGO','VENTA') then -importe else 0 end)  AS valor2 ".
	   	    "from movimientos inner join activos on activos.id=movimientos.idObjeto ".
	   	    "where tipoObjeto = 'A' ".
	    (isset($_REQUEST['idEntidad']) ? " AND (activos.idEntidad = :idEntidad )  " : " and ( idObjeto=:id ) ").
	    
	   	    "  and tipoOperacion in ('VALORACION','COBRO','COMPRA','VENTA','PAGO') ".
	   	    " and fecha>=CURDATE()-INTERVAL 400 DAY and movimientos.codEmpresa=:codEmpresa ".
	   	    "group by date_format(fecha,'%m/%Y') ORDER BY  date_format(fecha,'%Y/%m')" ;
	    
	    $stmt = ($this->link)->prepare($sql);
	    
	    $stmt->bindValue(':codEmpresa',  $_REQUEST['codEmpresa']);
	   // $stmt->bindValue(':id', 60);
	    
	    if (strpos($sql, ':id ') !== false) $stmt->bindValue(':id',  $_REQUEST['id']);
	    if (strpos($sql, ':idEntidad') !== false) $stmt->bindValue(':idEntidad',  $_REQUEST['idEntidad']);
	    
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
	
	
	private function findcresumenPatrimonio() {
	    $sql = "select tabAux.codElemento as serie,date_format(fecha,'%m/%Y') AS etiquetavalor,sum(importe) AS valor ".
	   	    "from (activos join movimientos on(((movimientos.idObjeto =activos.id) and (movimientos.tipoObjeto = 'A') and (movimientos.tipoOperacion = 'VALORACION')))) ".
	   	    " left join tablaauxiliar tabAux on activos.tipoActivo=tabAux.codElemento and tabAux.codTabla=4 and tabAux.codEmpresa=:codEmpresa ".
	   	    " where activos.estadoActivo in (1,4) and activos.computa=1 and activos.codEmpresa=:codEmpresa and date_format(movimientos.fecha,'%m/%Y')=:mes ".
	   	    "group by tabAux.codElemento,date_format(movimientos.fecha,'%m/%Y') " ;
	   	    
	   	    $stmt = ($this->link)->prepare($sql);
	   	    
// 	   	    if (strpos($sql, ':tipoActivo') !== false) $stmt->bindValue(':tipoActivo',  $_REQUEST['tipoActivo']);
	   	    if (isset($_REQUEST['mes'])) $stmt->bindValue(':mes',  $_REQUEST['mes']);
	   	    else $stmt->bindValue(':mes',  date("m/Y"));
	   	    $stmt->bindValue(':codEmpresa',  $_REQUEST['codEmpresa']);
	   	    
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
	

	private function findcresumenPatrimonio2() {
	    $sql = "select tabAux.valor1 as serie,date_format(fecha,'%m/%Y') AS etiquetavalor,sum(importe) AS valor ".
	   	    "from (activos join movimientos on(((movimientos.idObjeto =activos.id) and (movimientos.tipoObjeto = 'A') and (movimientos.tipoOperacion = 'VALORACION')))) ".
	   	    " left join tablaauxiliar tabAux on activos.tipoActivo=tabAux.codElemento and tabAux.codTabla=4 and tabAux.codEmpresa=:codEmpresa ".
	   	    " where activos.estadoActivo in (1,4) and activos.computa=1 and activos.codEmpresa=:codEmpresa and date_format(movimientos.fecha,'%m/%Y')=:mes ".
	   	    "group by tabAux.valor1,date_format(movimientos.fecha,'%m/%Y') " ;
	    
	    $stmt = ($this->link)->prepare($sql);
	    if (isset($_REQUEST['mes'])) $stmt->bindValue(':mes',  $_REQUEST['mes']);
	    else $stmt->bindValue(':mes',  date("m/Y"));
	    $stmt->bindValue(':codEmpresa',  $_REQUEST['codEmpresa']);
	    
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

	private function findcevolucionPatrimonioTipoActivo() {
	    $compraVenta=" select tipoOperacion as serie,date_format(fecha,'%Y/%m') as etiquetavalor,SUM(case when tipoOperacion in ('COBRO','COMPRA') then importe else -importe end)  AS valor ".
	   	         " from cvaloraciones ".
	   	         " where estadoActivo in (1,4) and computa=1 and codEmpresa=:codEmpresa and tipoOperacion in ('PAGO','COBRO','COMPRA','VENTA') and fecha>=CURDATE()- INTERVAL 400 DAY ".
	   	         (isset($_REQUEST['idEntidad']) ? " AND (idEntidad = :idEntidad )  " : " ").
	   	         " GROUP BY date_format(fecha,'%Y/%m'),tipoOperacion ";
        //	    " ORDER BY date_format(fecha,'%Y/%m'),tipoOperacion ";
	    
	    $claseActivo = "select claseActivo as serie,date_format(fecha,'%Y/%m') as etiquetavalor,sum(importe) as valor ".
	         " from cvaloraciones ".
	         " WHERE estadoActivo in (1,4) and computa=1 and codEmpresa=:codEmpresa and tipoOperacion='VALORACION' and fecha>=CURDATE()- INTERVAL 400 DAY ".
	         (isset($_REQUEST['idEntidad']) ? " AND (idEntidad = :idEntidad )  " : " ").
	         
	         " GROUP BY date_format(fecha,'%Y/%m'),claseActivo ";
	   //      " ORDER BY date_format(fecha,'%Y/%m'),claseActivo ";
	    
	         $sql= "select * from (".$compraVenta . " union ". $claseActivo .") as sql1 order by etiquetavalor,serie";
	    
	    $stmt = ($this->link)->prepare($sql);
	    $stmt->bindValue(':codEmpresa',  $_REQUEST['codEmpresa']);
	    if (strpos($sql, ':idEntidad') !== false) $stmt->bindValue(':idEntidad',  $_REQUEST['idEntidad']);
	    
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
	
		
	private function findcevolucionPatrimonioEntidad() {
	    $sql = "select 'Euros' as serie,nombreEntidad as etiquetavalor,sum(importe) as valor ".
	         " from cvaloraciones ".
	         " WHERE estadoActivo in (1,4) and computa=1 and codEmpresa=:codEmpresa and ".
             " tipoOperacion='VALORACION' and date_format(cvaloraciones.fecha,'%m/%Y')=:mes ".
             " GROUP BY nombreEntidad ".
	         " ORDER BY sum(importe) desc";
	    
	    $stmt = ($this->link)->prepare($sql);
	    $stmt->bindValue(':codEmpresa',  $_REQUEST['codEmpresa']);
	    if (isset($_REQUEST['mes'])) $stmt->bindValue(':mes',  $_REQUEST['mes']);
	    else $stmt->bindValue(':mes',  date("m/Y"));
	    
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
	
	    private function findcevolucionPatrimonioComprometido() {
	        $sql = "select left(nombre,20) as serie,date_format(fecha,'%m/%Y') as etiquetavalor,sum(importe) as valor ".
	          " from cvaloraciones ".
	          " WHERE  codEmpresa=:codEmpresa and tipoOperacion='COMPROMETIDO' and date_format(fecha,'%Y%m')>=:AnyoMes and  date_format(fecha,'%Y%m')<=:AnyoMas2".
	          " GROUP BY date_format(fecha,'%Y/%m'),nombre".
	          " ORDER BY date_format(fecha,'%Y/%m'),nombre";
	        
	        if (isset($_REQUEST['mes'])) $AnyoMes=substr($_REQUEST['mes'],3,4).substr($_REQUEST['mes'],0,2);
	        else $AnyoMes= date("Ym");
	        $AnyoMas2=date("Ym",strtotime('+2 years'));
  	        
	        $stmt = ($this->link)->prepare($sql);
	        $stmt->bindValue(':codEmpresa',  $_REQUEST['codEmpresa']);
	        $stmt->bindValue(':AnyoMes',  $AnyoMes);
	        $stmt->bindValue(':AnyoMas2',  $AnyoMas2);
	        
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
	    
	    
	private function findcevolucionPatrimonio() {
	    $evolucion="(SELECT date_format(bruto.fecha,'%Y/%m') as etiquetavalor,sum(bruto.importe) as bruto ,dividendos.importeDividendoIni,dividendos.importe as dividendos,sum(bruto.importe)-dividendos.importe as neto,dividendos.importeinicial,
                           sum(bruto.importe)-(dividendos.importeinicial-dividendos.importeDividendoIni)-dividendos.importe as beneficio
                    FROM movimientos as bruto join activos as act1 on act1.id=bruto.idObjeto and bruto.tipoObjeto='A' and act1.estadoActivo in (1,4) and act1.computa=1 and act1.codEmpresa=:codEmpresa
                 LEFT JOIN (SELECT date_format(cdiv.fecha,'%Y-%m-01') as fecha ,  
                               /* importe dividendos acumulados*/
                               (SELECT case when isnull(sum(importe)) then 0 else sum(importe) end                                                                
                			   from movimientos mov join activos as act2 on act2.id=mov.idObjeto and mov.tipoObjeto='A' and act2.estadoActivo in (1,4) and act2.codEmpresa=:codEmpresa
                  			   WHERE mov.codEmpresa=:codEmpresa and mov.tipoOperacion in ('COBRO EXCL') and
                    		   ((month(cdiv.fecha)=1 and year(mov.fecha)=year(cdiv.fecha)-1) or (month(cdiv.fecha)>1 and year(mov.fecha)=year(cdiv.fecha) and date_format(mov.fecha,'%Y/%m')<date_format(cdiv.fecha,'%Y/%m')) )) as importe,
                               /* valoracion inicial en enero */
                			   (SELECT case when isnull(sum(importe)) then 0 else sum(importe) end                                                                      
                			   from movimientos mov join activos as act2 on act2.id=mov.idObjeto and mov.tipoObjeto='A' and act2.estadoActivo in (1,4) and act2.codEmpresa=:codEmpresa and act2.computa=1
                  			   WHERE mov.codEmpresa=:codEmpresa and mov.tipoOperacion in ('VALORACION') and
                    		   ((month(cdiv.fecha)=1 and month(mov.fecha)=1 and year(mov.fecha)=year(cdiv.fecha)-1) or (month(cdiv.fecha)>1 and month(mov.fecha)=1 and year(mov.fecha)=year(cdiv.fecha) ) )) as importeinicial,
                               /* dividendos repartidos iniciales */
                                (SELECT sum(mov.importe) 
                                 FROM movimientos mov 
            	                  join activos as act2 on act2.id=mov.idObjeto and mov.tipoObjeto='A' and act2.estadoActivo in (1,4) and act2.tipoActivo='DIVIDENDO' and act2.codEmpresa=:codEmpresa 
            	                 WHERE mov.codEmpresa=:codEmpresa and (mov.tipoOperacion in ('VALORACION')) and 
            	                 ((month(cdiv.fecha)=1 and month(mov.fecha)=1 and year(mov.fecha)=year(cdiv.fecha)-1) or (month(cdiv.fecha)>1 and month(mov.fecha)=1 and year(mov.fecha)=year(cdiv.fecha) ) )) as importeDividendoIni 
            	                 
                			 FROM movimientos as cdiv join activos as act1 on act1.id=cdiv.idObjeto and cdiv.tipoObjeto='A' and act1.estadoActivo in (1,4) and act1.computa=1 and act1.codEmpresa=:codEmpresa
                			 WHERE cdiv.codEmpresa=:codEmpresa and (cdiv.tipoOperacion in ('VALORACION')) and cdiv.fecha>=CURDATE()- INTERVAL 400 DAY GROUP BY date_format(cdiv.fecha,'%Y-%m-01') ) as dividendos 
                   ON date_format(bruto.fecha,'%Y-%m-01')=dividendos.fecha
                             
                WHERE bruto.codEmpresa=:codEmpresa and (bruto.tipoOperacion in ('VALORACION')) and bruto.fecha>=CURDATE()- INTERVAL 400 DAY GROUP BY date_format(bruto.fecha,'%Y/%m'))  as evolucion";
	    
	    $sqlbruto="SELECT 'Patrimonio Bruto' as serie, etiquetavalor, bruto as valor,importeDividendoIni FROM ".$evolucion;
	    $sqlneto="SELECT 'Patrimonio Neto' as serie, etiquetavalor, neto as valor,importeDividendoIni FROM ".$evolucion;
	    $sqlbeneficio="SELECT 'Beneficio' as serie, etiquetavalor, beneficio as valor,importeDividendoIni FROM ".$evolucion;
	    $sql="select * from (".$sqlbruto . " union ". $sqlneto ." union ". $sqlbeneficio . ") as sql1 order by etiquetavalor,serie";
	    
/*	    
	    
	   $neto = " , (select (case when isnull(sum(bruto.importe)) then 0 else sum(bruto.importe) end)-(case when isnull(sum(neto.importe)) then 0 else sum(neto.importe)  end)  ".
	   	    " from movimientos as neto join activos as act2 on act2.id=neto.idObjeto and neto.tipoObjeto='A' and act2.estadoActivo in (1,4) and act2.codEmpresa=:codEmpresa WHERE neto.codEmpresa=:codEmpresa and (neto.tipoOperacion in ('COBRO EXCL')) 
	   	    and ((month(bruto.fecha)=1 and year(neto.fecha)=year(bruto.fecha)-1) or (month(bruto.fecha)>1 and year(neto.fecha)=year(bruto.fecha) and date_format(neto.fecha,'%Y/%m')<date_format(bruto.fecha,'%Y/%m')) ) ) as valor ";
	   	    
	   	  // bruto - impinicial - dividendos  
	   $dividendos= " , (select (case when isnull(sum(bruto.importe)) then 0 else sum(bruto.importe) end)-ini.impini-(case when isnull(sum(neto.importe)) then 0 else sum(neto.importe)  end)   from movimientos as neto join activos as act2 on act2.id=neto.idObjeto and neto.tipoObjeto='A' and act2.estadoActivo in (1,4) and act2.codEmpresa=:codEmpresa ".
	   " left join (select date_format(fecha,'%Y-%m-01') as fini,sum(importe) as impini from movimientos  where tipoOperacion='VALORACION' group by date_format(fecha,'%Y-%m-01') ) ini ON  month(ini.fini)=1 and year(ini.fini)=year(neto.fecha) ".
	   " WHERE neto.codEmpresa=:codEmpresa and (neto.tipoOperacion in ('COBRO EXCL')) 
	   	    and ((month(bruto.fecha)=1 and year(neto.fecha)=year(bruto.fecha)-1) or (month(bruto.fecha)>1 and year(neto.fecha)=year(bruto.fecha) and date_format(neto.fecha,'%Y/%m')<date_format(bruto.fecha,'%Y/%m')) ) ) as valor " ;
	   $beneficio=" select 'Beneficio' as serie,date_format(bruto.fecha,'%Y/%m') as etiquetavalor,sum(bruto.importe) as valor1 ". $dividendos .
	   	    " from movimientos as bruto join activos as act1 on act1.id=bruto.idObjeto and bruto.tipoObjeto='A' and act1.estadoActivo in (1,4) and act1.computa=1 and act1.codEmpresa=:codEmpresa WHERE bruto.codEmpresa=:codEmpresa and (bruto.tipoOperacion in ('VALORACION')) and bruto.fecha>=CURDATE()- INTERVAL 400 DAY GROUP BY date_format(bruto.fecha,'%Y/%m')   ";
	   
	
	    $sqlneto = " select 'Patrimonio Neto' as serie,date_format(bruto.fecha,'%Y/%m') as etiquetavalor,sum(bruto.importe) as valor1 ". $neto .
	   	    " from movimientos as bruto join activos as act1 on act1.id=bruto.idObjeto and bruto.tipoObjeto='A' and act1.estadoActivo in (1,4) and act1.computa=1 and act1.codEmpresa=:codEmpresa WHERE bruto.codEmpresa=:codEmpresa and (bruto.tipoOperacion in ('VALORACION')) and bruto.fecha>=CURDATE()- INTERVAL 400 DAY GROUP BY date_format(bruto.fecha,'%Y/%m')   ";
        $sqlbruto = " select 'Patrimonio Bruto' as serie,date_format(bruto.fecha,'%Y/%m') as etiquetavalor, 1 as valor1,sum(bruto.importe) as valor " .
	   	    " from movimientos as bruto join activos as act1 on act1.id=bruto.idObjeto and bruto.tipoObjeto='A' and act1.estadoActivo in (1,4) and act1.computa=1 and act1.codEmpresa=:codEmpresa WHERE bruto.codEmpresa=:codEmpresa and (bruto.tipoOperacion in ('VALORACION')) and bruto.fecha>=CURDATE()-INTERVAL 400 DAY GROUP BY date_format(bruto.fecha,'%Y/%m')   ";	   	    
	    $sql="select * from (".$sqlbruto . " union ". $sqlneto ." union ". $beneficio . ") as sql1 order by etiquetavalor,serie";
*/	    
	    $stmt = ($this->link)->prepare($sql);
	    $stmt->bindValue(':codEmpresa',  $_REQUEST['codEmpresa']);
	    
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
	
	private function findcpanelDatos() {
	    $neto = "  (select sum(neto.importe) from movimientos as neto ".
	            " join activos as act2 on act2.id=neto.idObjeto and neto.tipoObjeto='A' and act2.estadoActivo in (1,4) and act2.codEmpresa=:codEmpresa ".
	            " WHERE neto.codEmpresa=:codEmpresa and (neto.tipoOperacion in ('COBRO EXCL')) and ((month(comp.fecha)=1 and year(neto.fecha)=year(comp.fecha)-1) or (month(comp.fecha)>1 and   year(neto.fecha)=year(comp.fecha) and date_format(neto.fecha,'%Y/%m')<date_format(comp.fecha,'%Y/%m')) ) )   as importeADescontar ";
	    $dividendosIni = "  (select sum(neto.importe) from movimientos as neto ".
	            " join activos as act2 on act2.id=neto.idObjeto and neto.tipoObjeto='A' and act2.estadoActivo in (1,4) and act2.tipoActivo='DIVIDENDO' and act2.codEmpresa=:codEmpresa ".
	            " WHERE neto.codEmpresa=:codEmpresa and (neto.tipoOperacion in ('VALORACION')) and date_format(neto.fecha,'%m/%Y')=:primerMes )   as importeDividendoIni ";       
	    $sqlcomputable = " select date_format(comp.fecha,'%m/%Y') as mesFecha, ".
	        " sum(case when act1.computa=1 then comp.importe else 0 end) as patrimonioBruto, ".
	        " sum(case when act1.computa=1 then comp.importe else 0 end) as patrimonioNeto,". 
	        " sum((case when act1.computa=1 then comp.importe else 0 end)*rentae.rentabEsp/100) as rentEsperada, " . $neto . ", " . $dividendosIni .
	   	    " from movimientos as comp join activos as act1 on act1.id=comp.idObjeto and comp.tipoObjeto='A' and act1.estadoActivo in (1,4) and act1.computa=1  ".
	   	    " left join act_rentaespanual as rentae on act1.id=rentae.idActivo  ".
	   	     "  AND rentae.ejercicio=(case when (substr(:mes,1,2)='01') then substr(:mes,4,4)-1 else substr(:mes,4,4) end) ". /*case when month(comp.fecha)=1 then year(comp.fecha)-1 else year(comp.fecha) end*/
	   	    " WHERE comp.codEmpresa=:codEmpresa and act1.codEmpresa=:codEmpresa and comp.tipoOperacion in ('VALORACION') and (date_format(comp.fecha,'%m/%Y')=:mes or date_format(comp.fecha,'%m/%Y')=:primerMes ) ".
	   	   
	   	    " GROUP BY date_format(comp.fecha,'%m/%Y')   ";
	    
	    $stmt = ($this->link)->prepare($sqlcomputable);
	    $stmt->bindValue(':codEmpresa',  $_REQUEST['codEmpresa']);
	    if (isset($_REQUEST['mes'])) {
	        $stmt->bindValue(':mes',  $_REQUEST['mes']);// 11/2018
	        $ejercicio=intval(substr($_REQUEST['mes'],3));
	        if (substr($_REQUEST['mes'],0,2)=="01") $ejercicio=$ejercicio-1;
	        $primerMes=  '01/'.$ejercicio;// 11/2018
	    } else {
	        $stmt->bindValue(':mes',  date("m/Y"));
	        $primerMes= date("01/Y");
	    }
	    $stmt->bindValue(':primerMes',$primerMes);
	    try {
	        if  ($stmt->execute()) {
	            $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
	            $tmp=array();
	            $tmp['patrimonioBrutoInicial']=1;
	            $tmp['patrimonioNetoInicial']=1;
	            $tmp['rentabilidadEsperadaInicial']=0;
	            $tmp['importeADescontarInicial']=0;
	            $tmp['patrimonioBrutoMes']=1;
	            $tmp['patrimonioNetoMes']=1;
	            $tmp['importeADescontarMes']=0;
	            $tmp['rentabilidadEsperadaMes']=0;
	            for ($i=0;$i< count($result);$i++)  {
	                if ($result[$i]['mesFecha']==$primerMes) {
	                    $dividendoIni = 0;
	                    if (! is_null($result[$i]['importeDividendoIni'])) $dividendoIni = $result[$i]['importeDividendoIni'];
	                    $tmp['importeDividendoIni'] = $dividendoIni ;
	                    $tmp['patrimonioBrutoInicial']=$result[$i]['patrimonioBruto'] - $dividendoIni ; // resto posible valoracion de dividendos a socios en enero
	                    $tmp['importeADescontarInicial']=$result[$i]['importeADescontar'];
	                    $tmp['patrimonioNetoInicial']=$result[$i]['patrimonioNeto'] - $dividendoIni ; // resto posible valoracion de dividendos a socios en enero
	                    if ($tmp['patrimonioNetoInicial']==0) $tmp['patrimonioNetoInicial']=1;
	                    $tmp['rentabilidadEsperadaInicial']=round($result[$i]['rentEsperada']*100/$tmp['patrimonioNetoInicial'],2);
	                }
    	            else {
    	                $tmp['patrimonioBrutoMes']=$result[$i]['patrimonioBruto'];
    	                $tmp['importeADescontarMes']=$result[$i]['importeADescontar'];
    	                $tmp['patrimonioNetoMes']=$result[$i]['patrimonioNeto'] -$tmp['importeADescontarMes'];
    	                if ($tmp['patrimonioNetoMes']==0) $tmp['patrimonioNetoMes']=1;
    	                $tmp['rentabilidadEsperadaMes']=$result[$i]['rentEsperada']*100/$tmp['patrimonioNetoMes'];
    	            }
//echo $primerMes	    . " ".$ejercicio." ".$result[$i]['mesFecha']." ".json_encode($result[$i]);
    	            
	            }
	            $tmp['rentabilidadReal']=($tmp['patrimonioNetoMes']-$tmp['patrimonioNetoInicial'])*100/$tmp['patrimonioNetoInicial'];
	            echo json_encode($tmp);
	        }
	    }
	    catch ( PDOException $Exception)  {
	        die( $Exception->getMessage( )  );
	    }
	}
	
	/*
	 *  PAGOS Y COBROS DE FACTURAS
	 */
	private function cPagosCobros() {
	    if ($_REQUEST['tipoOperacion']=='NOMINA') $strtabla='cpagosnominas';
	    else $strtabla='cpagoscobros';
	    
	    $sql = "select * from ".$strtabla.
	   	    " where  " .
	   	    " id is not null " .
	   	    ($_REQUEST['generar'] != NULL ? " AND (generar = :generar) ":"").
	   	    ($_REQUEST['codEmpresa'] != NULL ? " AND (codEmpresa = :codEmpresa )  " : "").
	   	    ($_REQUEST['id'] != NULL ? " AND (id = :id )  " : "").
	   	    ($_REQUEST['idEntidad'] != NULL ? " AND (idEntidad = :idEntidad)  " : "").
	   	    ($_REQUEST['fecha'] != NULL ? " AND (fecha  = :fecha )  " : "").
	   	    ($_REQUEST['tipoOperacion'] != NULL ? " AND (tipoOperacion= :tipoOperacion)  " : "").
	   	    ($_REQUEST['fechaGeneracionGT'] != NULL ? " AND (fechaGeneracion>= :fechaGeneracionGT )  " : "").
	   	    ($_REQUEST['fechaGeneracionLT'] != NULL ? " AND (fechaGeneracion<= :fechaGeneracionLT )  " : "") . 
	   	    ($_REQUEST['generar'] == NULL && $_REQUEST['fechaGeneracionLT'] == NULL && $_REQUEST['fechaGeneracionGT'] == NULL ? " AND fechaGeneracion is null " : "")
	   	    ;
	   	    
	   	    $stmt = ($this->link)->prepare($sql);
	   	    
	   	    if (strpos($sql, ':id ') !== false) $stmt->bindParam(':id', $_REQUEST['id']);
	   	    if (strpos($sql, ':codEmpresa ') !== false) $stmt->bindParam(':codEmpresa', $_REQUEST['codEmpresa']);
	   	    if (strpos($sql, ':idEntidad') !== false) $stmt->bindValue(':idEntidad',  $_REQUEST['idEntidad']);
	   	    if (strpos($sql, ':fecha ') !== false) $stmt->bindValue(':fecha',  $_REQUEST['fecha']);
	   	    if (strpos($sql, ':tipoOperacion') !== false) $stmt->bindValue(':tipoOperacion',  $_REQUEST['tipoOperacion']);
	   	    if (strpos($sql, ':fechaGeneracionGT ') !== false) $stmt->bindValue(':fechaGeneracionGT',  $_REQUEST['fechaGeneracionGT']);
	   	    if (strpos($sql, ':fechaGeneracionLT ') !== false) $stmt->bindValue(':fechaGeneracionLT',  $_REQUEST['fechaGeneracionLT']);
	   	    if (strpos($sql, ':generar') !== false) $stmt->bindValue(':generar',  $_REQUEST['generar']);
	   	    
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
	
	private function borrarMarcasPago() {
	    $sql = "update movimientos set generar=0 where codEmpresa=:codEmpresa and generar=1";
	    $stmt = ($this->link)->prepare($sql);
	    $stmt->bindParam(':codEmpresa', $_REQUEST['codEmpresa']);
	    $stmt->execute();
	   	    
	}
	private function generarMarcasPago($ids=null) {
	    $ids=$_REQUEST['ids'];
	    
	    $sql = "update movimientos set generar=1 where codEmpresa=:codEmpresa and id in (".$ids.")";
	    $stmt = ($this->link)->prepare($sql);
	    $stmt->bindParam(':codEmpresa', $_REQUEST['codEmpresa']);
	    $stmt->execute();
	    
	}
	
	private function generarNominas() {
	    // empresa
	    $sql="select * from entidades where id=:idObjeto";
	    $stmt = ($this->link)->prepare($sql);
	    $stmt->bindValue(':idObjeto',   $_REQUEST['idObjeto']);
	    if  ($stmt->execute()) {
	        $cdatosEntidad= $stmt->fetchAll(PDO::FETCH_ASSOC);
	    }
	    $fechaDesde = date("Y-m-01", strtotime("-1 months"));
	    $fechaUno =date("Y-m-01");
	    $fechaHasta=date("Y-m-d", strtotime("-1 day",strtotime($fechaUno)));
	    $sql = "insert into  movimientos  (codEmpresa,tipoObjeto,idObjeto,tipoOperacion,fecha,participaciones,precioUnitario,importe,retencion,descripcion,user,ts)  ".
	       " (select movant.codEmpresa,movant.tipoObjeto,movant.idObjeto,movant.tipoOperacion,curdate(),movant.participaciones,movant.precioUnitario,movant.importe,movant.retencion,CONCAT('NOMINA DE ',upper(DATE_FORMAT(CURDATE(),'%M'))),".
	       "'".(isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."',now() ".
	       " from movimientos movant where  movant.codEmpresa=:codEmpresa and movant.tipoOperacion='NOMINA' and movant.fecha between :fechaDesde and :fechaHasta)";
	    $stmt = ($this->link)->prepare($sql);
	    $stmt->bindParam(':codEmpresa', $cdatosEntidad[0]['codEmpresa']);
	    $stmt->bindParam(':fechaDesde', $fechaDesde);
	    $stmt->bindParam(':fechaHasta', $fechaHasta);
	    $stmt->execute();
	    
	    echo "<script>alert('N�minas generadas');window.close();</script>";
	    
	}
	
	public function findEstadoPagosNominas() {
	    //  $codEmpresa=( isset($_REQUEST['codEmpresa'])?$_REQUEST['codEmpresa']:NULL);
	    
	    $sql = "select CONCAT('PAGOS/NOM PEND.PAGO ',tabaux.valor1) as empresa,sum(case when tipoOperacion='PAGO' then 1 else 0 end) as facturas,sum(case when tipoOperacion='NOMINA' then 1 else 0 end) as notas ".
	   	    " from movimientos join tablaauxiliar tabaux on tabaux.codtabla=8 and tabaux.codElemento=movimientos.codEmpresa  ".
	   	    "where  " .
	   	    " movimientos.tipoObjeto in ('F','E') and movimientos.tipoOperacion in ('NOMINA','PAGO') and movimientos.fechaGeneracion is null " .
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
	
	private function recalcularImportesMoneda() {
        $sql = "update movimientos mov inner join activos act on act.id=mov.idObjeto inner join tablaauxiliar tabaux on tabaux.codTabla=13 and act.moneda=tabaux.codElemento ".
               "set mov.importe=mov.importeOriginal/cast(tabaux.valor1 as decimal(10,3)), mov.descripcion=concat(mov.descripcion,'\r\nCambio (',curdate(),'):',tabaux.valor1), ".
               " mov.user='".(isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."', mov.ts=now() ".
	           "where mov.codEmpresa=:codEmpresa and mov.tipoOperacion in ('COMPROMETIDO', 'DISTRIBUCION') and act.moneda is not null and act.moneda<>'EUROS' ".
	           " and mov.importeOriginal>0 and cast(tabaux.valor1 as decimal(10,3))>0";
	           
	    $stmt = ($this->link)->prepare($sql);
	    $stmt->bindValue(':codEmpresa', $_REQUEST['codEmpresa']);
	    $stmt->execute();
	    
	    echo '{ "result": "OK" }';
	}
	
}

	// Initiiate Library

	$api = new bd_movimientos();

	$api->analiza_method();
  
?>