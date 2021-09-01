<?php

require_once("../lib/bd_api_pdo.php");

class bd_personal extends BD_API {
    
    public function analiza_method() {
        // el POST cuando viene de un formulario hay que hacer a mano el insert con un metodo por ejemplo guardaBD en estea clase. Si vienen con JSON puede ser automatico
        if ( ( $this->method=="GET"  && $this->table!="personal")||
             ( $this->method=="POST" && $this->table=="guardarBD") ){ // lo hemos llamado con otro metodo
            
            $func=$this->table; // nombre de la funcion a la que llamo de esta clase,  en table guardo el nombre del metodo
            
            $this->$func();
            
        } else {
            if ($this->method=="DELETE") { // hay que borra en tablas relacionadas
                    //borro movimientos
                    $stmt = ($this->link)->prepare("delete from movimientos where tipoObjeto='N'and idObjeto=$this->key");
                    $stmt->execute();
            }  // delete
            $this->table="personal";
            $this->execStandardMethod();
            
        }
        
    }
    
    private function findPersonalEmail() {
        $email=$_REQUEST['email'];
        try {
            $sql = "select idpersonal from users where email=:email";
            $stmt = ($this->link)->prepare($sql);
            $stmt->bindValue(':email',  $email);
            $stmt->execute();
            $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
            $idpersonal=$result[0]['idpersonal'];
            
            $sql = "select personal.*,users.userRol from personal,users where personal.id=users.idPersonal and personal.id=:idpersonal";
            $stmt = ($this->link)->prepare($sql);
            
            $stmt->bindValue(':idpersonal',  $idpersonal);
            
            if  ($stmt->execute()) {
                $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
                $this->devolverResultados($result);
            }
        }
        catch ( PDOException $Exception)  {
            die( $Exception->getMessage( )  );
        }
    }
    private function findPersonalFilter() {
        $codEmpresa=( isset($_REQUEST['codEmpresa'])?$_REQUEST['codEmpresa']:NULL);
        $id=( isset($_REQUEST['id'])?$_REQUEST['id']:$this->key);
        $nombre=( isset($_REQUEST['nombre'])?$_REQUEST['nombre']:NULL);
        $email=( isset($_REQUEST['email'])?$_REQUEST['email']:NULL);
        $vigente=(isset($_REQUEST['vigente'])?intval($_REQUEST['vigente']):NULL);
        $sql = "select * ".
            " from personal ".
            " where  " .
            " id is not null " .
            ($id != NULL ? " AND (id = :id )  " : "") .
            ($codEmpresa != NULL ? " AND (codEmpresa = :codEmpresa)  " : "") .
            ($nombre != NULL ? " AND (nombre like :nombre)  " : "") .
            ($email != NULL ? " AND (email like :email)  " : "") .
            ($vigente !== NULL  ? ($vigente==1? " AND (fechaBaja<='0000-00-00')  " : " AND (fechaBaja>'0000-00-00')  ") : "")
            ;
            
            $sql .= " ORDER BY nombre";
            $stmt = ($this->link)->prepare($sql);
            
            if (strpos($sql, ':nombre') !== false) $stmt->bindValue(':nombre',  '%'.$nombre.'%');//jv.uso bindvalue porque asigno una expresion
            if (strpos($sql, ':id ') !== false) $stmt->bindParam(':id',  $id);
            if (strpos($sql, ':codEmpresa') !== false) $stmt->bindParam(':codEmpresa',  $codEmpresa);
            if (strpos($sql, ':email') !== false) $stmt->bindValue(':email',  '%'.$email.'%');
            
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
    
    private function findPersonalCombo() {
        $nombre=( isset($_REQUEST['query'])?$_REQUEST['query']:NULL); // cuando empiezo a teclar en un combo y paro en query devuelve trozo tecleado
        $codEmpresa=( isset($_REQUEST['codEmpresa'])?$_REQUEST['codEmpresa']:NULL); 
        $sql = "select id,nombre,email from personal where (fechaBaja<='0000-00-00')  " .
            ($codEmpresa != NULL ? " AND (codEmpresa=:codEmpresa)  " : "") .
            ($nombre != NULL ? " AND (nombre like :nombre)  " : "") .
            "order by nombre";
            
            $stmt = ($this->link)->prepare($sql);
            if (strpos($sql, ':nombre') !== false) $stmt->bindValue(':nombre',  '%'.$nombre.'%');
            if (strpos($sql, ':codEmpresa') !== false) $stmt->bindParam(':codEmpresa',  $codEmpresa);
            
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
    
    public function getAtributoPersona($codEmpresa,$nombreAbreviado,$atributo) {
        $sql = "select ".$atributo." from personal where 1=1  " .
            ($codEmpresa != NULL ? " AND (codEmpresa=:codEmpresa)  " : "") .
            ($nombreAbreviado != NULL ? " AND (nombreAbreviado = :nombreAbreviado)  " : "") ;
            
            $stmt = ($this->link)->prepare($sql);
            if (strpos($sql, ':nombreAbreviado') !== false) $stmt->bindValue(':nombreAbreviado',  $nombreAbreviado);
            if (strpos($sql, ':codEmpresa') !== false) $stmt->bindParam(':codEmpresa',  $codEmpresa);
            
            try {
                if  ($stmt->execute()) {
                    $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
                    return $result[0][$atributo];
                }
            }
            catch ( PDOException $Exception)  {
                die( $Exception->getMessage( )  );
            }
    }
    
    
    public function crearNominasFromDrive($codEmpresa,$nombreAbreviado,$nuevoNombre) { // en movimientos tambien se generan las nominas automaticas
        $idpersona=$this->getAtributoPersona($codEmpresa, $nombreAbreviado, 'id');
        $fechaDesde = date("Y-m-01", strtotime("-1 months"));
        $fechaUno =date("Y-m-01");
        $fechaHasta=date("Y-m-d", strtotime("-1 day",strtotime($fechaUno)));
        $sql = "insert into  movimientos  (codEmpresa,tipoObjeto,idObjeto,tipoOperacion,fecha,participaciones,precioUnitario,importe,retencion,descripcion,archivoDrive,user,ts)  ".
            " (select movant.codEmpresa,movant.tipoObjeto,movant.idObjeto,movant.tipoOperacion,curdate(),movant.participaciones,movant.precioUnitario,movant.importe,movant.retencion,CONCAT('NOMINA DE ',upper(DATE_FORMAT(CURDATE(),'%M'))),".
            " :nuevoNombre,".
            "'".(isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."',now() ".
            " from movimientos movant where  movant.codEmpresa=:codEmpresa and movant.tipoOperacion='NOMINA' and ".
            " movant.idObjeto=:idpersona and movant.fecha between :fechaDesde and :fechaHasta)";
        $stmt = ($this->link)->prepare($sql);
        $stmt->bindParam(':codEmpresa', $codEmpresa);
        $stmt->bindParam(':fechaDesde', $fechaDesde);
        $stmt->bindParam(':fechaHasta', $fechaHasta);
        $stmt->bindParam(':idpersona', $idpersona);
        $stmt->bindParam(':nuevoNombre', $nuevoNombre);
        $stmt->execute();
        
        
    }
    
    private function guardarBD() {
        $sql="select id from personal where id=:idPersonal";
        $stmt = ($this->link)->prepare($sql);
        $stmt->bindParam(':idPersonal',  $_REQUEST['id']);
        if  ($stmt->execute()) {
            $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result)<=0)  {// no existe activo, lo creo
                $sql="insert into personal ";
                $where="";
            } else { // si que existe, actualizo
                $sql="update personal ";
                $where=" where id=:idPersonal ";
            }
            
            $sql = $sql.
            " set  codEmpresa=:codEmpresa, nombre=:nombre, carpetaDrive=:carpetaDrive, cuentaCorriente=:cuentaCorriente,".
            " nombreAbreviado=:nombreAbreviado,telefono=:telefono,email=:email,fechaAlta=:fechaAlta,fechaBaja=:fechaBaja, ".
            " nif=:nif,horaInicio1=:horaInicio1,horaFin1=:horaFin1,horaInicio2=:horaInicio2,horaFin2=:horaFin2,horasJornada=:horasJornada ".
            
            ( isset($_REQUEST['user'])?",user=:user ":"") .
            ( isset($_REQUEST['ts'])?",ts=:ts ":"") .
            $where;
            $stmt = ($this->link)->prepare($sql);
            if (strpos($sql, ':idPersonal') !== false) $stmt->bindValue(':idPersonal',  $_REQUEST['id']);
            $stmt->bindValue(':codEmpresa',  $_REQUEST['codEmpresa']);
            $stmt->bindValue(':nombre',  $_REQUEST['nombre']);
            $stmt->bindValue(':nombreAbreviado',  $_REQUEST['nombreAbreviado']);
            $stmt->bindValue(':carpetaDrive',  $_REQUEST['carpetaDrive']);
            $stmt->bindValue(':cuentaCorriente',  $_REQUEST['cuentaCorriente']);
            $stmt->bindValue(':email',  $_REQUEST['email']);
            $stmt->bindValue(':telefono',  $_REQUEST['telefono']);
            $stmt->bindValue(':fechaAlta',  $_REQUEST['fechaAlta']);
            $stmt->bindValue(':fechaBaja',  $_REQUEST['fechaBaja']);
            $stmt->bindValue(':nif',  $_REQUEST['nif']);
            $stmt->bindValue(':horaInicio1',  $_REQUEST['horaInicio1']);
            $stmt->bindValue(':horaFin1',  $_REQUEST['horaFin1']);
            $stmt->bindValue(':horaInicio2',  $_REQUEST['horaInicio2']);
            $stmt->bindValue(':horaFin2',  $_REQUEST['horaFin2']);
            $stmt->bindValue(':horasJornada',  $_REQUEST['horasJornada']);
            if (strpos($sql, ':user') !== false) $stmt->bindValue(':user',  ( isset($_REQUEST['user'])?$_REQUEST['user']:NULL));
            if (strpos($sql, ':ts') !== false) $stmt->bindValue(':ts',  ( isset($_REQUEST['ts'])?$_REQUEST['ts']:NULL));
            
            try {
                $stmt->execute() ;
            }
            
            catch ( PDOException $Exception)  {
                die( $Exception->getMessage( )  );
            }
        }
    }
    
}


?>