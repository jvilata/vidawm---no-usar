<?php

require_once("../lib/bd_api_pdo.php");

require_once("../attach/bd_attach_class.php");


class bd_activos extends BD_API {
    
    public function analiza_method() {
        // el POST cuando viene de un formulario hay que hacer a mano el insert con un metodo por ejemplo guardaBD en estea clase. Si vienen con JSON puede ser automatico
        if ( (($this->method=="GET")||($this->method=="POST")) && ($this->table!="activos")) { // lo hemos llamado con otro metodo
            
            $func=$this->table; // nombre de la funcion a la que llamo de esta clase,  en table guardo el nombre del metodo
            
            $this->$func();
            
        } else {
            if ($this->method=="DELETE") { // hay que borra en tablas relacionadas
                    //borro movimientos
                    $stmt = ($this->link)->prepare("delete from movimientos where tipoObjeto='A'and idObjeto=$this->key");
                    $stmt->execute();
                    //borro act_rentaespanual
                    $stmt = ($this->link)->prepare("delete from act_rentaespanual where  idActivo=$this->key");
                    $stmt->execute();
                    // borro adjuntos
                    $vattach = new BorrarAdjuntos();
                    $vattach->borrarAdjuntosObjeto($this->link,'A', $this->key);
            }  // delete
             if ($this->method=="PUT" && $this->table=="cActivosInversion"){
                    $this->guardarActivosInversion();
            } else {
                $this->table="activos";
                $this->execStandardMethod();
            }
            
        }
        
    }
    
    
    
    private function findActivosFilter() {
        $codEmpresa=( isset($_REQUEST['codEmpresa'])?$_REQUEST['codEmpresa']:NULL);
        $id=( isset($_REQUEST['id'])?$_REQUEST['id']:$this->key);
        $nombre=( isset($_REQUEST['nombre'])?$_REQUEST['nombre']:NULL);
        $tipoActivo=( isset($_REQUEST['tipoActivo'])?$_REQUEST['tipoActivo']:NULL);
        $estadoActivo=( isset($_REQUEST['estadoActivo'])?$_REQUEST['estadoActivo']:NULL);
        $idEntidad=( isset($_REQUEST['idEntidad'])?$_REQUEST['idEntidad']:NULL);
        $computa=( isset($_REQUEST['computa'])?$_REQUEST['computa']:NULL);
        // tipoProducto viene en formato ['uno','otro']
        $arr_tipoProducto=( isset($_REQUEST['tipoProducto'])?json_decode($_REQUEST['tipoProducto'],true):NULL);
        $str1="";
        if ($arr_tipoProducto!=NULL) {
            foreach ($arr_tipoProducto as $value) {
                if (strlen($str1)>0) $str1.=" AND ";
                $str1=$str1." (tipoProducto like '%".$value."%') ";
            }
            
        } else
            if (isset($_REQUEST['tipoProducto']))
                $str1=" (tipoProducto like '%".$_REQUEST['tipoProducto']."%') ";
            
        $sql = "select activos.*,tabaux.valor1 as descEstadoActivo,entidades.nombre as nombreEntidad,rentabEsp.rentabEsp,rentabReal, ".
              " (select rentaAnt.rentabEsp from act_rentaespanual rentaAnt where rentaAnt.idActivo=activos.id and rentaAnt.ejercicio=year(curdate())-1) as rentabAnt " . 
            "from activos left join tablaauxiliar tabaux on  tabaux.codTabla='3' and tabaux.codElemento=activos.estadoActivo " .
            "          left join entidades on activos.idEntidad=entidades.id ".
            "          left join act_rentaespanual rentabEsp on activos.id=rentabEsp.idActivo and rentabEsp.ejercicio=year(curdate()) ".
            "where  " .
            " activos.id is not null " .
            ($id != NULL ? " AND (activos.id = :id )  " : "") .
            ($codEmpresa != NULL ? " AND (activos.codEmpresa = :codEmpresa)  " : "") .
            ($nombre != NULL ? " AND (activos.nombre like :nombre)  " : "") .
            ($tipoActivo != NULL ? " AND (tipoActivo = :tipoActivo)  " : "") .
            ($estadoActivo != NULL ? " AND (estadoActivo = :estadoActivo)  " : "") .
            ($idEntidad != NULL ? " AND (idEntidad = :idEntidad)  " : "") .
            ($computa != NULL ? " AND (computa = :computa)  " : "") .
            ($str1!="" ? " AND (".$str1.")" : "") 
            
            ;
            
            $sql .= " ORDER BY activos.nombre";
            
            $stmt = ($this->link)->prepare($sql);
            
            if (strpos($sql, ':nombre') !== false) $stmt->bindValue(':nombre',  '%'.$nombre.'%');//jv.uso bindvalue porque asigno una expresion
            if (strpos($sql, ':id ') !== false) $stmt->bindParam(':id',  $id);
            if (strpos($sql, ':codEmpresa') !== false) $stmt->bindParam(':codEmpresa',  $codEmpresa);
            if (strpos($sql, ':tipoActivo') !== false) $stmt->bindParam(':tipoActivo',  $tipoActivo);
            if (strpos($sql, ':estadoActivo') !== false) $stmt->bindParam(':estadoActivo',  $estadoActivo);
            if (strpos($sql, ':idEntidad') !== false) $stmt->bindParam(':idEntidad',  $idEntidad);
            if (strpos($sql, ':computa') !== false) $stmt->bindParam(':computa',  $computa);
            
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
    
    private function findActivosCombo() {
        $nombre=isset($_REQUEST['query'])?$_REQUEST['query']:( isset($_REQUEST['nombre'])?$_REQUEST['nombre']:NULL);
 // echo '--'.$_REQUEST['estadoActivo'].'--';    
        $codEmpresa=( isset($_REQUEST['codEmpresa'])?$_REQUEST['codEmpresa']:NULL);
        $id=( isset($_REQUEST['id'])?$_REQUEST['id']:$this->key);
      //  $nombre=( isset($_REQUEST['nombre'])?$_REQUEST['nombre']:NULL);
        $tipoActivo=( isset($_REQUEST['tipoActivo'])?$_REQUEST['tipoActivo']:NULL);
        $estadoActivo=( isset($_REQUEST['estadoActivo'])?$_REQUEST['estadoActivo']:NULL);
        $idEntidad=( isset($_REQUEST['idEntidad'])?$_REQUEST['idEntidad']:NULL);
        
        $sql = "select activos.id,activos.nombre ".
            "from activos left join tablaauxiliar tabaux on tabaux.codEmpresa=activos.codEmpresa and tabaux.codTabla='3' and tabaux.codElemento=activos.estadoActivo " .
            "          left join entidades on activos.idEntidad=entidades.id ".
            "where  " .
            " activos.id is not null " .
            ($id != NULL ? " AND (activos.id = :id )  " : "") .
            ($codEmpresa != NULL ? " AND (activos.codEmpresa = :codEmpresa)  " : "") .
            ($nombre != NULL ? " AND (activos.nombre like :nombre)  " : "") .
            ($tipoActivo != NULL ? " AND tipoActivo in ( :tipoActivo )  " : "") .
            ($estadoActivo != NULL ? " AND (estadoActivo in (" . $estadoActivo.") )  " : "") .
            ($idEntidad != NULL ? " AND (idEntidad = :idEntidad)  " : "")
            ;
            
            $sql .= " ORDER BY activos.nombre";
           
            $stmt = ($this->link)->prepare($sql);
            
            if (strpos($sql, ':nombre') !== false) $stmt->bindValue(':nombre',  '%'.$nombre.'%');//jv.uso bindvalue porque asigno una expresion
            if (strpos($sql, ':id ') !== false) $stmt->bindParam(':id',  $id);
            if (strpos($sql, ':codEmpresa') !== false) $stmt->bindParam(':codEmpresa',  $codEmpresa);
            if (strpos($sql, ':tipoActivo') !== false) $stmt->bindParam(':tipoActivo',  $tipoActivo);
            // if (strpos($sql, ':estadoActivo') !== false) $stmt->bindValue(':estadoActivo',  $estadoActivo);
            if (strpos($sql, ':idEntidad') !== false) $stmt->bindParam(':idEntidad',  $idEntidad);
            
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
    
    private function SQL_ActivosInversion() {
        
        $sql="select comp.id, comp.tipoActivo, comp.claseActivo,comp.pRentTeorica,
            comp.pTeorico,comp.user,comp.ts,
            
            comp.patrimonioGlobal,comp.patrimonio,comp.patrimonioInicialGlobal,comp.patrimonioInicial,comp.impRentEsperada,
            comp.impcompras,comp.impcompvent,comp.impcobropago,comp.facturado,
            ifnull(comp.impRentEsperada*100/comp.patrimonioInicial,0) as pRentEsperada, 
            ifnull(comp.patrimonioInicial*100/comp.patrimonioInicialGlobal,0) as prealInicial,
            ifnull(comp.patrimonio*100/comp.patrimonioGlobal,0) as preal,
            comp.comprometido,comp.comprometidoGlobal,
            ((comp.patrimonio+comp.comprometido)*100/(comp.comprometidoGlobal+comp.patrimonioGlobal)) as prealComprometido,

            comp.comprometido6m,
            comp.comprometido7_24m,
            
            /*  pajuste=prealcomprometido-pteoricoActivo */
            (comp.pTeorico)-((comp.patrimonio+comp.comprometido)*100/(comp.comprometidoGlobal+comp.patrimonioGlobal)) as pajuste,
            
            /*  impoorteAjuste=pajuste * patrimonio */
            ((comp.pTeorico)-((comp.patrimonio+comp.comprometido)*100/(comp.comprometidoGlobal+comp.patrimonioGlobal)))/100*(comp.comprometidoGlobal+comp.patrimonioGlobal) as importeAjuste, 
   
            comp.dividendosGlobal,
            
            /* dividendos= dividendos total * pTeoricoActivo */
            (comp.dividendosGlobal*comp.pTeorico/100) as dividendos, 

            /*  InversionAnual=importeAjuste+dividendos */
            ((comp.pTeorico)-((comp.patrimonio+comp.comprometido)*100/(comp.comprometidoGlobal+comp.patrimonioGlobal)))/100*(comp.comprometidoGlobal+comp.patrimonioGlobal)+
            (comp.dividendosGlobal*comp.pTeorico/100)  as InversionAnual,

            (((comp.pTeorico)-((comp.patrimonio+comp.comprometido)*100/(comp.comprometidoGlobal+comp.patrimonioGlobal)))/100*(comp.comprometidoGlobal+comp.patrimonioGlobal)+
            (comp.dividendosGlobal*comp.pTeorico/100))/(13-:Mes) as InversionMensual
        
        from (
        
        Select ta.id,ta.codElemento as tipoActivo, ta.valor1 as claseActivo,ta.valor2 as pRentTeorica,ta.valor3 as pTeorico, ta.user,ta.ts,/* en valor2 está %RentaTeorica, en valor3 está %proporcionTeorica */
            ifnull(patrimonioInicial.importe,0) as patrimonioInicial,
            ifnull(patrimonioInicial.impRentEsperada ,0) as impRentEsperada,
            ifnull(patrimonioInicialGlobal.importe,0) as patrimonioInicialGlobal,
            ifnull(patrimonio.importe,0) as patrimonio, 
            ifnull(patrimonio.impcompras,0) as impcompras, 
            ifnull(patrimonio.impcompvent,0) as impcompvent, 
            ifnull(patrimonio.impcobropago,0) as impcobropago, 
            ifnull(patrimonio.facturado,0) as facturado, 
            ifnull(patrimonioGlobal.importe,0) as patrimonioGlobal,
            ifnull(comprometidoGlobal.importe,0) as comprometidoGlobal,
            ifnull(comprometido.importe,0) as comprometido, 
            ifnull(comprometido6m.importe,0) as comprometido6m, 
            ifnull(comprometido7_24m.importe,0) as comprometido7_24m,

            ifnull(dividendos.importe,0) as dividendosGlobal
        
        from tablaauxiliar ta 
         
        join /* patrimonio global actual */
         
           (select sum(mov.importe) as importe 
            from movimientos mov 
            join activos act on mov.idObjeto=act.id and act.computa=1 
            where mov.codEmpresa=:codEmpresa and mov.tipoOperacion='VALORACION' and date_format(mov.fecha,'%m/%Y')=:MesAnyo) as patrimonioGlobal  /* '04/2019' */
         
        left join /* patrimonio tipoactivo actual*/ 
         
           (select act.tipoactivo,sum(mov.importe) as importe ,
               sum(mov.importe*rentabE.rentabEsp/100.0) as impRentEsperada,
               sum(mov.impcompras) as impcompras,
               sum(mov.impcompvent) as impcompvent,
               sum(mov.impcobropago) as impcobropago,
               sum(facturado) as facturado
            from (select mov1.*, 

                /* compras  */
		       coalesce((SELECT SUM(comven.importe)  
		  		     FROM cvaloraciones as comven 
                     WHERE comven.tipoOperacion in ('COMPRA') and comven.idActivo=mov1.idObjeto and year(comven.fecha)=(case when month(mov1.fecha)=1 then year(mov1.fecha)-1 else year(mov1.fecha) end)),0)
                  as impcompras ,
                /* compras ventas */
		       coalesce((SELECT SUM(case when comven.tipoOperacion='COMPRA' then comven.importe when comven.tipoOperacion='VENTA' then -comven.importe else 0 end)  
		  		     FROM cvaloraciones as comven 
                     WHERE comven.tipoOperacion in ('COMPRA','VENTA') and comven.idActivo=mov1.idObjeto and year(comven.fecha)=(case when month(mov1.fecha)=1 then year(mov1.fecha)-1 else year(mov1.fecha) end)),0)
                  as impcompvent ,
               /* cobros pagos */
		       coalesce((SELECT SUM(case when comven.tipoOperacion='COBRO' then comven.importe when comven.tipoOperacion='PAGO' then -comven.importe else 0 end)  
		  		    FROM cvaloraciones as comven 
                   WHERE comven.tipoOperacion in ('COBRO','PAGO') and comven.idActivo=mov1.idObjeto and year(comven.fecha)=(case when month(mov1.fecha)=1 then year(mov1.fecha)-1 else year(mov1.fecha) end)),0)
                 as impcobropago,  
               /* facturas */
		       coalesce((SELECT SUM(base) 
		  		      FROM cfacturas 
                      WHERE tipoFactura='EMITIDA' and cfacturas.idActivo=mov1.idObjeto and year(cfacturas.fecha)=(case when month(mov1.fecha)=1 then year(mov1.fecha)-1 else year(mov1.fecha) end)),0)
                 as facturado
             /* PRINCIPAL MOVIMIENTOS */       
                  from movimientos mov1
                  where  mov1.codEmpresa=:codEmpresa and mov1.tipoOperacion='VALORACION' and date_format(mov1.fecha,'%m/%Y')=:MesAnyo ) as mov 
            join activos act on mov.idObjeto=act.id and act.computa=1  
            left join act_rentaespanual rentabE on act.id=rentabE.idActivo and
                     rentabE.ejercicio= (case when :Mes=1 then :Anyo-1 else :Anyo end) /*(case when month(mov.fecha)=1 then year(mov.fecha)-1 else year(mov.fecha) end) */ 
            group by act.tipoActivo) as patrimonio  
         
            on patrimonio.tipoActivo=ta.codElemento  

        join /* patrimonio inicial global */
         
           (select sum(mov.importe) as importe 
            from movimientos mov 
            join activos act on mov.idObjeto=act.id and act.computa=1 
            where mov.codEmpresa=:codEmpresa and mov.tipoOperacion='VALORACION' and month(mov.fecha)=1 and year(mov.fecha)=(case when :Mes=1 then :Anyo-1 else :Anyo end)) as patrimonioInicialGlobal  /* year(mov.fecha)=:Anyo */

        left join /* patrimonio inicial tipoactivo*/ 
         
           (select act.tipoactivo,sum(mov.importe) as importe ,
               sum(mov.importe*rentabE.rentabEsp/100.0) as impRentEsperada
            from movimientos mov 
            join activos act on mov.idObjeto=act.id and act.computa=1  
            left join act_rentaespanual rentabE on act.id=rentabE.idActivo and
                    rentabE.ejercicio=(case when :Mes=1 then :Anyo-1 else :Anyo end)  
            where mov.codEmpresa=:codEmpresa and mov.tipoOperacion='VALORACION' and month(mov.fecha)=1 and year(mov.fecha)=(case when :Mes=1 then :Anyo-1 else :Anyo end)/*:Anyo*/ 
            group by act.tipoActivo) as patrimonioInicial 
         
            on patrimonioInicial.tipoActivo=ta.codElemento  

        join /*comprometido global */
         
           (select sum(mov.importe) as importe 
            from movimientos mov 
            join activos act on mov.idObjeto=act.id and act.computa=1 
            where mov.codEmpresa=:codEmpresa and mov.tipoOperacion='COMPROMETIDO' and date_format(mov.fecha,'%Y/%m')>=:AnyoMes) as comprometidoGlobal  
        
        left join /*comprometidotipoactivo */
         
           (select act.tipoactivo,sum(mov.importe) as importe 
            from movimientos mov 
            join activos act on mov.idObjeto=act.id and act.computa=1 
            where mov.codEmpresa=:codEmpresa and mov.tipoOperacion='COMPROMETIDO' and date_format(mov.fecha,'%Y/%m')>=:AnyoMes 
            group by act.tipoActivo) as comprometido  
         
            on comprometido.tipoActivo=ta.codElemento 

        left join /*comprometidotipoactivo  6m*/
         
           (select act.tipoactivo,sum(mov.importe) as importe 
            from movimientos mov 
            join activos act on mov.idObjeto=act.id and act.computa=1 
            where mov.codEmpresa=:codEmpresa and mov.tipoOperacion='COMPROMETIDO' and date_format(mov.fecha,'%Y/%m')>=:AnyoMes and date_format(mov.fecha,'%Y/%m')<=:fecha6m 
            group by act.tipoActivo) as comprometido6m  
         
            on comprometido6m.tipoActivo=ta.codElemento 
            
        left join /*comprometidotipoactivo 7-24m*/
         
           (select act.tipoactivo,sum(mov.importe) as importe 
            from movimientos mov 
            join activos act on mov.idObjeto=act.id and act.computa=1 
            where mov.codEmpresa=:codEmpresa and mov.tipoOperacion='COMPROMETIDO' and date_format(mov.fecha,'%Y/%m')>:fecha6m and date_format(mov.fecha,'%Y/%m')<=:fecha24m 
            group by act.tipoActivo) as comprometido7_24m  
         
            on comprometido7_24m.tipoActivo=ta.codElemento             

        join /*dividendos globales */
         
           (select sum(mov.importe) as importe 
            from movimientos mov  
            where mov.codEmpresa=:codEmpresa and mov.tipoOperacion='COBRO EXCL' and date_format(mov.fecha,'%m')>=:Mes and date_format(mov.fecha,'%Y')=:Anyo ) as dividendos  
         
        where codTabla=4 and ta.codEmpresa=:codEmpresa ) as comp order by tipoActivo";
        
        return $sql;
    }
    
    private function cActivosInversion() {
            $sql =$this->SQL_ActivosInversion();
        
            $stmt = ($this->link)->prepare($sql);
            
            if (isset($_REQUEST['codEmpresa'])) $codEmpresa=$_REQUEST['codEmpresa'];
            else $codEmpresa="01";
            if (isset($_REQUEST['mes'])) $MesAnyo=$_REQUEST['mes'];
	        else $MesAnyo=date("m/Y");
	        $fecha6m = date_create_from_format('m/Y', $MesAnyo);
	        $fecha24m = date_create_from_format('m/Y', $MesAnyo);
	        $fecha6m->add(new DateInterval('P6M'));
	        $fecha24m->add(new DateInterval('P24M'));
	        $AnyoMes=substr($MesAnyo,3,4).'/'.substr($MesAnyo,0,2);
	        if (intval(substr($MesAnyo,0,2))==1)
	          $AnyoSigDic=strval(intval(substr($AnyoMes,0,4))).'/12';
	        else
	          $AnyoSigDic=strval(intval(substr($AnyoMes,0,4))+1).'/12';
	        
	        $stmt->bindValue(':codEmpresa',  $codEmpresa);
	        $stmt->bindValue(':MesAnyo',  $MesAnyo);
	        $stmt->bindValue(':AnyoMes',  $AnyoMes);
	        // $stmt->bindValue(':AnyoSigDic',  $AnyoSigDic);
	        $stmt->bindValue(':fecha6m',  date_format($fecha6m,"Y/m"));
	        $stmt->bindValue(':fecha24m',  date_format($fecha24m,"Y/m"));
	        $stmt->bindValue(':Mes',  substr($MesAnyo,0,2));
            $stmt->bindValue(':Anyo',  substr($MesAnyo,3,4)); 
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
        $sql="select id from activos where id=:idActivo";
        $stmt = ($this->link)->prepare($sql);
        $stmt->bindParam(':idActivo',  $_REQUEST['id']);
        if  ($stmt->execute()) {
            $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result)<=0)  {// no existe activo, lo creo
                $sql="insert into activos ";
                $where="";
            } else { // si que existe, actualizo
                $sql="update activos ";
                $where=" where id=:idActivo";
            }
            
            $sql = $sql.
            " set  codEmpresa=:codEmpresa, nombre=:nombre, tipoActivo=:tipoActivo, estadoActivo=:estadoActivo ".
            ( isset($_REQUEST['descripcion'])?",descripcion=:descripcion":"") .
            ( isset($_REQUEST['codOtraEmpresa'])?",codOtraEmpresa=:codOtraEmpresa ":"") .
            ( isset($_REQUEST['idActivoOtra']) && $_REQUEST['idActivoOtra']!="" ?",idActivoOtra=:idActivoOtra ":"") .
            ( isset($_REQUEST['idEntidad'])?",idEntidad=:idEntidad ":"") .
            ( isset($_REQUEST['computa'])?",computa=:computa ":"") .
            ( isset($_REQUEST['carpetaDrive'])?",carpetaDrive=:carpetaDrive ":"") .
            ( isset($_REQUEST['urlinfo'])?",urlinfo=:urlinfo ":"") .
            ( isset($_REQUEST['enRenta'])?",enRenta=:enRenta ":"") .
            ( isset($_REQUEST['rentabilidadEsperada'])?",rentabilidadEsperada=:rentabilidadEsperada ":"") .
            ( isset($_REQUEST['rentabilidadFutura'])?",rentabilidadFutura=:rentabilidadFutura ":"") .
            ( isset($_REQUEST['tipoProducto'])?",tipoProducto=:tipoProducto ":"") .
            
            ( isset($_REQUEST['moneda'])?",moneda=:moneda ":"") .
            ( isset($_REQUEST['comentarios'])?",comentarios=:comentarios ":"") .
            ( isset($_REQUEST['user'])?",user=:user ":"") .
            ( isset($_REQUEST['ts'])?",ts=:ts ":"") .
            $where;
            $stmt = ($this->link)->prepare($sql);
            if (strpos($sql, ':idActivo') !== false) $stmt->bindValue(':idActivo',  $_REQUEST['id']);
            $stmt->bindValue(':codEmpresa',  $_REQUEST['codEmpresa']);
            $stmt->bindValue(':nombre',  $_REQUEST['nombre']);
            $stmt->bindValue(':tipoActivo',  $_REQUEST['tipoActivo']);
            $stmt->bindValue(':estadoActivo',  $_REQUEST['estadoActivo']);
            if (strpos($sql, ':idEntidad') !== false) $stmt->bindValue(':idEntidad',  $_REQUEST['idEntidad']);
            if (strpos($sql, ':descripcion') !== false) $stmt->bindValue(':descripcion',  ( isset($_REQUEST['descripcion'])?$_REQUEST['descripcion']:NULL));
            if (strpos($sql, ':idActivoOtra') !== false) $stmt->bindValue(':idActivoOtra',  ( isset($_REQUEST['idActivoOtra'])?$_REQUEST['idActivoOtra']:NULL));
            if (strpos($sql, ':codOtraEmpresa') !== false) $stmt->bindValue(':codOtraEmpresa',  ( isset($_REQUEST['codOtraEmpresa'])?$_REQUEST['codOtraEmpresa']:NULL));
            if (strpos($sql, ':computa') !== false) $stmt->bindValue(':computa',  ( isset($_REQUEST['computa'])?$_REQUEST['computa']:NULL));
            if (strpos($sql, ':carpetaDrive') !== false) $stmt->bindValue(':carpetaDrive',  ( isset($_REQUEST['carpetaDrive'])?$_REQUEST['carpetaDrive']:NULL));
            if (strpos($sql, ':urlinfo') !== false) $stmt->bindValue(':urlinfo',  ( isset($_REQUEST['urlinfo'])?$_REQUEST['urlinfo']:NULL));
            if (strpos($sql, ':enRenta') !== false) $stmt->bindValue(':enRenta',  ( isset($_REQUEST['enRenta'])?$_REQUEST['enRenta']:NULL));
            if (strpos($sql, ':rentabilidadEsperada') !== false) $stmt->bindValue(':rentabilidadEsperada',  ( isset($_REQUEST['rentabilidadEsperada'])?$_REQUEST['rentabilidadEsperada']:NULL));
            if (strpos($sql, ':rentabilidadFutura') !== false) $stmt->bindValue(':rentabilidadFutura',  ( isset($_REQUEST['rentabilidadFutura'])?$_REQUEST['rentabilidadFutura']:NULL));

            if (strpos($sql, ':tipoProducto') !== false) {
                $arr_tipoProd= json_decode($_REQUEST['tipoProducto'],true);
                $str1="";
                foreach ($arr_tipoProd as $value) {
                        if (strlen($str1)>0) $str1.=",";
                        $str1.=$value;
                 }
                $stmt->bindValue(':tipoProducto',  $str1);
                }
            if (strpos($sql, ':moneda') !== false) $stmt->bindValue(':moneda',  ( isset($_REQUEST['moneda'])?$_REQUEST['moneda']:NULL));
            if (strpos($sql, ':comentarios') !== false) $stmt->bindValue(':comentarios',  ( isset($_REQUEST['comentarios'])?$_REQUEST['comentarios']:NULL));
            if (strpos($sql, ':user') !== false) $stmt->bindValue(':user',  ( isset($_REQUEST['user'])?$_REQUEST['user']:NULL));
            if (strpos($sql, ':ts') !== false) $stmt->bindValue(':ts',  ( isset($_REQUEST['ts'])?$_REQUEST['ts']:NULL));
            
            try {
                $stmt->execute() ;
                if (count($result)<=0)  {// era un insert
                    $lastId= $this->link->lastInsertId();
			        echo "{\"id\":".$lastId."}";
                } else { // si que existe, era update
                    echo "{\"id\":".$_REQUEST['id']."}";
                }
                // echo "{success:1}";
            }
            
            catch ( PDOException $Exception)  {
                die( $Exception->getMessage( )  );
                echo "{failure:1}";
            }
        }
    }
    
    
    private function guardarActivosInversion() {

           $sql = "update tablaauxiliar ".
            " set  valor2=:pRentTeorica,valor3=:pTeorico, ".
                 " user='".(isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."',".
                 " ts=now()  ".
                 " where id=:id";
            $stmt = ($this->link)->prepare($sql);
            $stmt->bindValue(':id',  $this->input['id']);
            $stmt->bindValue(':pRentTeorica', $this->input['pRentTeorica']);
            $stmt->bindValue(':pTeorico', $this->input['pTeorico']);
            
            try {
                $stmt->execute() ;
                echo  "{id:".$this->key."}";
            }
            
            catch ( PDOException $Exception)  {
                die( $Exception->getMessage( )  );
            }
    }
    
}



// Initiiate Library



$api = new bd_activos();

$api->analiza_method();

?>