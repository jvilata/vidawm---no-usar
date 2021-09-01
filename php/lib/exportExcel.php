<?php
	require_once("bd_api_pdo.php");
	class exportExcel  extends BD_API { 

		protected function  execStandardMethod() {
			$sql=$_REQUEST['SQL'];			
			$this->ExecSql($sql);
		}
		protected function devolverResultados($result) {
			$filename = $_REQUEST['nompdf']; // File Name
			// Download file
			header("Content-Disposition: attachment; filename=\"$filename\"");
			header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
			$flag = false;
			foreach ($result as $row) {
				if (!$flag) {
					// display field/column names as first row
					echo implode("\t", array_keys($row)) . "\r\n";
					$flag = true;
				}
				
				// echo implode("\t", array_values($row)) . "\r\n";		
				$line = '';
				foreach($row as $value){
				    if(!isset($value) || $value == ""){				        $value = "\t";
				    }else{
				        if (is_numeric($value)) $value = str_replace('.', ',', $value);
				        else  $value = str_replace('"', '""', $value);
				        $value = '"' . $value . '"' . "\t";
				    }
				    $line .= $value;
				}
				echo trim($line)."\r\n";
			}
		}
	}		
	// Initiiate Library
	
	$api = new exportExcel();
	$api->analiza_method();
?>