<?php

	require_once("../lib/bd_api_pdo.php");

	

	class bd_entidades extends BD_API {

		public function analiza_method() {

			if (($this->key=='0') && ($this->method=="GET") && ($this->table!="entidades")) { // lo hemos llamado con otro metodo

				$func=$this->table; // nombre de la funcion a la que llamo de esta clase,  en table guardo el nombre del metodo

				$this->$func();

			} else {

				$this->table="entidades";

			    $this->execStandardMethod();

			}	

		}

		

		private function findEntidadesFilter() {

		      $id=( isset($_REQUEST['id'])?$_REQUEST['id']:NULL);
		      $nombre=( isset($_REQUEST['nombre'])?'%'.$_REQUEST['nombre'].'%':NULL);

				$personaContacto=( isset($_REQUEST['personaContacto'])?$_REQUEST['personaContacto']:NULL);
				$codEmpresa=( isset($_REQUEST['codEmpresa'])?$_REQUEST['codEmpresa']:NULL);

				$telefono=( isset($_REQUEST['telefono'])?$_REQUEST['telefono']:NULL);

				$cargo=( isset($_REQUEST['cargo'])?$_REQUEST['cargo']:NULL);

				$email=( isset($_REQUEST['email'])?$_REQUEST['email']:NULL);
                
				$web=( isset($_REQUEST['web'])?$_REQUEST['web']:NULL);
				
				$tipoEntidad=( isset($_REQUEST['tipoEntidad'])?$_REQUEST['tipoEntidad']:NULL);
				
				$pais=( isset($_REQUEST['pais'])?$_REQUEST['pais']:NULL);


				$sql = "select * from entidades where  " .

				    " id is not null " .

				    ($id != NULL ? " AND (id = :id )  " : "") .
				    ($codEmpresa != NULL ? " AND (codEmpresa = :codEmpresa )  " : "") .
				    ($nombre != NULL ? " AND (nombre like :nombre)  " : "") .

					($personaContacto != NULL ? " AND ( personaContacto like :personaContacto or persona2 like :personaContacto  or persona3 like :personaContacto)  " : "") .

					($telefono != NULL ? " AND (telefono like :telefono or telefono2 like :telefono or telefono3 like :telefono)  " : "") .

					($cargo != NULL ? " AND (cargo like :cargo or cargo2 like :cargo or cargo3 like :cargo)  " : "") .
					
					($email != NULL ? " AND (email like :email or email2 like :email or email3 like :email)  " : "") .
					
					($web != NULL ? " AND (web like :web)  " : "") .					
					
					($tipoEntidad != NULL ? " AND (tipoEntidad like :tipoEntidad)  " : "") .
					
					($pais != NULL ? " AND (pais like :pais)  " : "")	
					;

	

				$sql .= " ORDER BY nombre"; 

 

				$stmt = ($this->link)->prepare($sql);

				if (strpos($sql, ':id ') !== false) $stmt->bindParam(':id',  $id);
				if (strpos($sql, ':codEmpresa ') !== false) $stmt->bindParam(':codEmpresa',  $codEmpresa);
				if (strpos($sql, ':nombre') !== false) $stmt->bindParam(':nombre',  $nombre);
				
				if (strpos($sql, ':personaContacto') !== false) $stmt->bindValue(':personaContacto', '%'.$personaContacto.'%');

				if (strpos($sql, ':telefono') !== false) $stmt->bindValue(':telefono',  '%'.$telefono.'%');

				if (strpos($sql, ':cargo') !== false) $stmt->bindValue(':cargo',  '%'.$cargo.'%');

				if (strpos($sql, ':email') !== false) $stmt->bindValue(':email',  '%'.$email.'%');
				
				if (strpos($sql, ':web') !== false) $stmt->bindParam(':web',  $web);
				
				if (strpos($sql, ':tipoEntidad') !== false) $stmt->bindParam(':tipoEntidad',  $tipoEntidad);
				
				if (strpos($sql, ':pais') !== false) $stmt->bindParam(':pais',  $pais);

				 

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

	

	private function findEntidadesCombo() {
	        $nombre=( isset($_REQUEST['query'])?$_REQUEST['query']:NULL); // cuando empiezo a teclar en un combo y paro en query devuelve trozo tecleado
	        $sql = "select id,nombre,email from entidades where 1=1 " .
	   	       ($nombre != NULL ? " AND (nombre like :nombre)  " : "") .
	   	    "order by nombre";
	        
	   	    $stmt = ($this->link)->prepare($sql);
	   	    if (strpos($sql, ':nombre') !== false) $stmt->bindValue(':nombre',  '%'.$nombre.'%');
	   	    
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

	

	$api = new bd_entidades();

	$api->analiza_method();

?>