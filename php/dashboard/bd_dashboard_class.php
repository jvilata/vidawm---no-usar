<?php

	require_once("../lib/bd_api_pdo.php");
	

	class bd_dashboard extends BD_API {

		public function analiza_method() {
		    
		    if (($this->key=='0') && ($this->method=="GET") && ($this->table!="dashboard")) { // lo hemos llamado con otro metodo
		        
		        $func=$this->table; // nombre de la funcion a la que llamo de esta clase,  en table guardo el nombre del metodo
		        
		        $this->$func();
		        
		    } else {
		        
		        $this->table="dashboard";
		        
		        $this->execStandardMethod();
		        
		    }
		    
		}

		private function findcpanelDatos() {			
				
				$sql = "select * from dashboard";

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
		
		
		public function insertarRegistro($empresa,$facturas,$notas,$cnominas) {
		        $sql=" insert into dashboard set  empresa=:empresa, facturas=:facturas, notas=:notas,nominas=:nominas";
		        $stmt = ($this->link)->prepare($sql);
		        $stmt->bindValue(':empresa',  $empresa);
		        $stmt->bindValue(':facturas', $facturas);
		        $stmt->bindValue(':notas',  $notas);
		        $stmt->bindValue(':nominas',  $cnominas);
		        
		        try {
		            $stmt->execute() ;
		        }
		        
		        catch ( PDOException $Exception)  {
		            die( $Exception->getMessage( )  );
		        }
		}
		
		private function borrarRegistros() {
		    
		    $sql=" delete from dashboard";
		    $stmt = ($this->link)->prepare($sql);
		    try {
		        $stmt->execute() ;
		    }
		    
		    catch ( PDOException $Exception)  {
		        die( $Exception->getMessage( )  );
		    }
		}
}



	
  
?>