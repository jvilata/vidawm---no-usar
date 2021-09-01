<?php
//JV. API para hacer consultas especificas que no se puedan hacer con el API generica lib/bd_api.php
// la URL debe tener el formato:  path/bd_suscritos.php/{operacion}/  , ejem: php/lib/bd_suscritos.php/findSuscritosFilter/

	require_once("../lib/bd_api_pdo.php");
    require_once("../onedrive/onedrive.php");

	class bd_attachs extends BD_API {
		protected $target_dir = "/uploads/"; // poner /privado/uploads/ para webempresa. habrá que poner el directorio donde queremos qeu se guarden los adjuntos
		
		public function analiza_method() {
			if ($this->table!="attachments") { // lo hemos llamado con otro metodo
				$func=$this->table; // nombre de la funcion a la que llamo de esta clase
				$this->$func();
			} else {
				$this->table="attachments";
			    $this->execStandardMethod();
			}	
		}

		
		private function findAttachFilter() {
			if ($this->key!=0) {
				$this->table="attachments";
			    $this->execStandardMethod();
			} else {
				$code=$_REQUEST['code'];
				$type=$_REQUEST['type'];
				$sql = "select * from attachments where tipoObjeto=:type and idObjeto=:code"; 
				$stmt = ($this->link)->prepare($sql);
				$stmt->bindParam(':type',  $type);
				$stmt->bindParam(':code',  $code);
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
		
		
		private  function addAttachment() {
				$this->guardarFicheroOneDrive();
		}

		private function attachDownload_old() {
				$sql = "select * from attachments where id=:id"; 
				$stmt = ($this->link)->prepare($sql);
				$stmt->bindParam(':id',  $this->key);
				try {
					if  ($stmt->execute()) {
						$result= $stmt->fetchAll(PDO::FETCH_ASSOC);
						if (file_exists($result[0]['nombreObjeto'])) {
							$file_url = $result[0]['nombreObjeto'];
							header('Content-Type: application/octet-stream');
							header("Content-Transfer-Encoding: Binary"); 
							header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
							readfile($file_url); 
						};
					}
				}
				catch ( PDOException $Exception)  {
					die( $Exception->getMessage( )  );
					
				}		
		}
		
		private function attachDownload() {
		    $sql = "select * from attachments where id=:id";
		    $stmt = ($this->link)->prepare($sql);
		    $stmt->bindParam(':id',  $this->key);
		    try {
		        if  ($stmt->execute()) {
		            $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
		            $VIDAOnedrive= new VIDAOneDrive("downloadFile",$result[0]['tipoObjeto'],$result[0]['idObjeto'],basename($result[0]["nombreObjeto"]),"",$result[0]["fileid"]);
		        }
		    }
		    catch ( PDOException $Exception)  {
		        die( $Exception->getMessage( )  );
		        
		    }
		}
		
		private function attachDelete() {
		    $sql = "select * from attachments where id=:id";
		    $stmt = ($this->link)->prepare($sql);
		    $stmt->bindParam(':id',  $this->key);
		    try {
		        if  ($stmt->execute()) {
		            $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
	                $vattach=new BorrarAdjuntos();
	                $vattach->borrarArchivoOneDrive($this->link,$this->key);
		        }
		    }
		    catch ( PDOException $Exception)  {
		        die( $Exception->getMessage( )  );
		        
		    }
		}
		
		/*
		* metodos propios de esta clase 
		*/
		// este metodo dejara de llamarse en favor de guardarFicheroOneDrive
		private function guardarFichero() {
				$dirbase=$_SERVER['DOCUMENT_ROOT'].$this->target_dir.$_REQUEST['type'].'/'.$_REQUEST['code'];
				if (!file_exists($dirbase))  mkdir($dirbase);
				$target_file = $dirbase .'/'. basename($_FILES["file"]["name"]);
				$uploadOk = 1;
				$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
				// Check if image file is a actual image or fake image
				if (filesize($_FILES["file"]["tmp_name"])==0) {
					echo "{'failure':'Sorry, your file was not uploaded.'}";
				// if everything is ok, try to upload file
				} else {
					if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
						echo "{'success': 'The file ". basename( $_FILES["file"]["name"]). " has been uploaded.'}";
						$this->guardarBD($target_file,0);
					} else {
						echo "{'failure':'Sorry, there was an error uploading your file.'";
					}
				}
		}
		/*
		 * 
		 */
		private function guardarFicheroOneDrive() {
		    $dirbase=$_SERVER['DOCUMENT_ROOT'].'/tmp/';
		    $target_file = $dirbase .'/'. basename($_FILES["file"]["tmp_name"]);
		    $uploadOk = 1;
		    $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		    // Check if image file is a actual image or fake image
		    if (filesize($_FILES["file"]["tmp_name"])==0) {
		        echo "{'failure':'Sorry, your file was not uploaded.'}";
		        // if everything is ok, try to upload file
		    } else {
		        $id=$this->guardarBD(basename($_FILES["file"]["name"]),0);
  		        move_uploaded_file($_FILES["file"]["tmp_name"], $target_file) ;
		        $VIDAOnedrive= new VIDAOneDrive("uploadFile",$_REQUEST['type'],$_REQUEST['code'],basename($_FILES["file"]["name"]),$target_file,null,$id);

		    }
		}
	
		public function actualizarAttachBD($fileid,$idadjunto) {
				$sql = "update attachments set fileid=:fileid where id=:idadjunto"; 
				$stmt = ($this->link)->prepare($sql);
				$stmt->bindParam(':fileid', $fileid);
				$stmt->bindParam(':idadjunto', $idadjunto);
				try {
					$stmt->execute() ;
				}
				catch ( PDOException $Exception)  {
					die( $Exception->getMessage( )  );
					
				}		
		}	
		public function borrarAttachBD($idadjunto) {
				$sql = "delete from attachments  where id=:idadjunto"; 
				$stmt = ($this->link)->prepare($sql);
				$stmt->bindParam(':idadjunto', $idadjunto);
				try {
					$stmt->execute() ;
				}
				catch ( PDOException $Exception)  {
					die( $Exception->getMessage( )  );
					
				}		
		}	
		public function guardarBD($target_file,$fileid) {
		    	$sql = "select * from attachments where nombreObjeto=:nombreObjeto";
	            $stmt = ($this->link)->prepare($sql);
	            $stmt->bindParam(':nombreObjeto',  basename($target_file));
                if  ($stmt->execute()) {
	                    $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else return 0;

	             $sqltmp=" attachments set tipoObjeto=:type, idObjeto=:code, fileid=:fileid,nombreObjeto=:nombreObjeto, asunto=:asunto, fecha=:fecha, tipoAdjunto=:tipoAdjunto, user=:user, ts=:ts";  
	            if (count($result)>0) 
	              $sql="update ".$sqltmp." where id=".$result[0]['id'];
	            else  
				  $sql = "insert into ".$sqltmp;
				$stmt = ($this->link)->prepare($sql);
				$date=$_REQUEST['ts'];
				$user=$_REQUEST['user'];// poner el usuario logado
				$stmt->bindParam(':type',  $_REQUEST['type']);
				$stmt->bindParam(':code',  $_REQUEST['code']);
				$stmt->bindParam(':nombreObjeto',  basename($target_file));
				$stmt->bindParam(':asunto',  $_REQUEST['asunto']);
				$stmt->bindParam(':fecha',  $date);
				$stmt->bindParam(':tipoAdjunto',  $_REQUEST['tipoAdjunto']);
				$stmt->bindParam(':fileid',  $fileid);
				$stmt->bindParam(':user',  $user); 
				$stmt->bindParam(':ts', $date);
				try {
					$stmt->execute() ;
					if (count($result)>0)  return $result[0]['id']; // update
					else return ($this->link)->lastInsertId();//insert
				}
				catch ( PDOException $Exception)  {
					die( $Exception->getMessage( )  );
					
				}		
		}
		
		
	}
	

	class BorrarAdjuntos {
	    /*
	     *	metodos llamables desde analiza_method
	     */
	    public function borrarAdjuntosObjeto($link,$tipo,$idobjeto) {
	        $sql = "select * from attachments where tipoObjeto=:tipo and idObjeto=:idobjeto";
	        $stmt = ($link)->prepare($sql);
	        $stmt->bindParam(':tipo',  $tipo);
	        $stmt->bindParam(':idobjeto',  $idobjeto);
	        try {
	            if  ($stmt->execute()) {
	                $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
	                for ($i=0;$i<count($result);$i++) {
	                    $this->borrarArchivoOneDrive($link,$result[$i]['id']);
	                    $stmt = ($link)->prepare("delete from attachments where id=:id");
	                    $stmt->bindValue(':id',  $result[$i]['id']);
	                    $stmt->execute();
	                }
	            }
	        }
	        catch ( PDOException $Exception)  {
	            die( $Exception->getMessage( )  );
	        }
	    }
	    /*
	     *	metodos llamables desde analiza_method
	     */
	    // este metodo dejara de llamarse con el uso de onedrive
	    public function borrarArchivo($link,$id) {
	        $sql = "select * from attachments where id=:id";
	        $stmt = ($link)->prepare($sql);
	        $stmt->bindParam(':id',  $id);
	        try {
	            if  ($stmt->execute()) {
	                $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
	                if (file_exists($result[0]['nombreObjeto'])) unlink($result[0]['nombreObjeto']);
	            }
	        }
	        catch ( PDOException $Exception)  {
	            die( $Exception->getMessage( )  );
	            
	        }
	        
	    }
	    public function borrarArchivoOneDrive($link,$id) {
	        $sql = "select * from attachments where id=:id";
	        $stmt = ($link)->prepare($sql);
	        $stmt->bindParam(':id',  $id);
	        try {
	            if  ($stmt->execute()) {
	                $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
	                    $fileid= $result[0]['fileid'];
	                    $stmt = ($link)->prepare("delete from attachments where id=:id");
	                    $stmt->bindValue(':id',  $result[0]['id']);
	                    $stmt->execute();
		                $VIDAOnedrive= new VIDAOneDrive("deleteFile",null,null,null,null,$fileid,$id);
	            }
	        }
	        catch ( PDOException $Exception)  {
	            die( $Exception->getMessage( )  );
	            
	        }
	        
	    }
	    
	}
	
	
?>
