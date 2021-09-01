<?php

require_once("../lib/bd_api_pdo.php");


class bd_act_rentabEspAnual extends BD_API {
    
    public function analiza_method() {
        // el POST cuando viene de un formulario hay que hacer a mano el insert con un metodo por ejemplo guardaBD en estea clase. Si vienen con JSON puede ser automatico
        if (($this->key=='0') && (($this->method=="GET")) && ($this->table!="act_rentaespanual")) { // lo hemos llamado con otro metodo
            
            $func=$this->table; // nombre de la funcion a la que llamo de esta clase,  en table guardo el nombre del metodo
            
            $this->$func();
            
        } else {
                $this->table="act_rentaespanual";
                $this->execStandardMethod();
        }
        
    }
    
    
    
    private function findAct_RentabEspAnualFilter() {
        $id=( isset($_REQUEST['id'])?$_REQUEST['id']:$this->key);
        $idActivo=( isset($_REQUEST['idActivo'])?$_REQUEST['idActivo']:NULL);
        $ejercicio=( isset($_REQUEST['ejercicio'])?$_REQUEST['ejercicio']:NULL);
        
        $sql = "select * from act_rentaespanual where id is not null " .
            ($id != NULL ? " AND (id = :id )  " : "") .
            ($idActivo != NULL ? " AND (idActivo = :idActivo )  " : "") .
            ($ejercicio != NULL ? " AND (ejercicio = :ejercicio " : "") 
            
            ;
            
            $sql .= " ORDER BY ejercicio";
            
            $stmt = ($this->link)->prepare($sql);
            
            if (strpos($sql, ':id ') !== false) $stmt->bindParam(':id',  $id);
            if (strpos($sql, ':idActivo') !== false) $stmt->bindParam(':idActivo',  $idActivo);
            if (strpos($sql, ':ejercicio') !== false) $stmt->bindParam(':ejercicio',  $ejercicio);
            
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
    
    private function duplicarRentabAnual() {
		    // generamos registros de rentabilidades copiados de la ultima rentab del activo, si no existe a 0
		    // podremos consultar si hay registros duplicados con esta query:
		    //SELECT * FROM `act_rentaespanual` a WHERE exists (select count(*) from act_rentaespanual b where a.idActivo=b.idActivo and b.ejercicio=2021 having count(*)>1)
		 
		    try {
		       		        
		    // recorremos todos los activos seleccionando ultima rentab
		    $sql="insert into act_rentaespanual (idActivo, ejercicio, rentabEsp, rentabReal,user,ts) ".
		  		    " select activos.id,:ejercicio,rentabEsp, rentabReal, '".(isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."',now() ". 
		  		    " from activos ".
                    "  inner join act_rentaespanual on act_rentaespanual.idActivo=activos.id  ".
		  		    "  and act_rentaespanual.ejercicio= :ejercicioAnt ".
		  		    " where activos.codEmpresa=:codEmpresa";
		    
		    $stmt = ($this->link)->prepare($sql);
		    $stmt->bindValue(':ejercicio',  $_REQUEST['ejercicio']);
		    $ejerAnt = (int) $_REQUEST['ejercicio']-1;
		    $stmt->bindValue(':ejercicioAnt',  $ejerAnt);
		    $stmt->bindValue(':codEmpresa',  $_REQUEST['codEmpresa']);
		    $stmt->execute() ;
		    echo '{ "result": "OK" }';
		    
		} catch ( PDOException $Exception)  {
		        die( $Exception->getMessage( )  );
		    }
    }
    
      
}



// Initiiate Library



$api = new bd_act_rentabEspAnual();

$api->analiza_method();

?>