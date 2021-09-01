<?php 
  require_once('../lib/fpdf/alphapdf.php');
  require_once("../lib/bd_api_pdo.php");
  require_once("bd_facturas_class.php");
  class pdf_liquidacion extends BD_API {
      // CARGAR DATOS
      protected $dirbase=''; //$_SERVER['DOCUMENT_ROOT'].'/wealthmgr/img/'; // directorio logos
      protected $datos_vida=[]; // datos empresa que factura
      protected $logo='';
      protected $datos_aquien=[];       // datos empresa a quien factura
      protected $datos_cabfactura=[]; //datos cab factura
      protected $datos_iva=[[]];
      protected $datos_linfactura=[[]];//datos lineas
       
      public function analiza_method() {
          $this->dirbase=$_SERVER['DOCUMENT_ROOT'].'/wealthmgr/img/'; // /privado/img/  en publico || /wealthmgr/img/ en local
          $func=$this->table; // nombre de la funcion a la que llamo de esta clase,  en table guardo el nombre del metodo
          $this->$func();

          
      //    $this->generaMovimientoPago();
          
      }
      
      private function generaMovimientoPago() {
          $sql="select * from cfacturas where tipoFactura='PLANT.RECIBIDA' and idActivo=:idObjeto";
          $stmt = ($this->link)->prepare($sql);
          $stmt->bindParam(':idObjeto',  $_REQUEST['idObjeto']);
          if  ($stmt->execute()) {
              $cfacturas= $stmt->fetchAll(PDO::FETCH_ASSOC);
              $cfacturas[0]['fecha']=date();
              $this->datos_cabfactura=$cfacturas[0];
          }
          
          $api=new bd_facturas();
          $lastId=$api->copiarFactura($cfacturas[0]['id']);
          
          $sql="insert into movimientos set codEmpresa=:codEmpresa,tipoObjeto='F',idObjeto=:idFactura,tipoOperacion='PAGO',".
              "fecha=now(), participaciones=:unidades,precioUnitario=:precio,importe=:neto,retencion=:retencion,descripcion=:descripcion,".
              "user='".(isset($_SESSION['emailLogin'])?$_SESSION['emailLogin']:'system')."',ts=now() ";
          $stmt = ($this->link)->prepare($sql);
          $stmt->bindValue(':codEmpresa',  $this->datos_cabfactura['codEmpresa']);
          $stmt->bindValue(':idFactura',  $lastId);
//          $stmt->bindValue(':idActivo',  $this->datos_cabfactura['idActivo']);
          $stmt->bindValue(':unidades',  $this->datos_cabfactura['unidades']);
          $stmt->bindValue(':precio',  $this->datos_cabfactura['precio']);
          $stmt->bindValue(':neto',  $this->datos_cabfactura['totalFactura']);
          $stmt->bindValue(':retencion',  $this->datos_cabfactura['retencion']);
          $stmt->bindValue(':descripcion',  $this->replaceString($this->datos_cabfactura['descripcion'],0));
          $stmt->execute();
          $idmov= $this->link->lastInsertId();
          
          $this->imprimirLiquidacion($idmov,1); // a disco
          
      }
      
      // cargar datos de la factura
      private function cargarDatosLiquidacion($idMov){
          // CARGAR DATOS
          $cfacturas=[];
          // datos factura actual
          //$sql="select * from cfacturas where tipoFactura='PLANT.RECIBIDA' and idActivo=:idObjeto";
          $sql="select movimientos.*,cabfacturas.idCliente from movimientos ".
            " left join cabfacturas on cabfacturas.id=movimientos.idObjeto ".
            " where movimientos.id=:idMov";
          $stmt = ($this->link)->prepare($sql);
          $stmt->bindParam(':idMov',  $idMov);
          if  ($stmt->execute()) {
              $cfacturas= $stmt->fetchAll(PDO::FETCH_ASSOC);
          }
          
          // datos empresa que factura
            $sql="select * from entidades where codEmpresa=:codEmpresa and tipoentidad='SELF'";
            $stmt = ($this->link)->prepare($sql);
            $stmt->bindValue(':codEmpresa',  $cfacturas[0]['codEmpresa']);
            if  ($stmt->execute()) {
              $cdatosEntidad= $stmt->fetchAll(PDO::FETCH_ASSOC);
              if (count($cdatosEntidad)<=0)
                  $this->datos_vida=['logo'=>'VIDA_color.jpg','nombre'=>'VILATA DARDER HOLDING SL','cif'=>'B97830426',
                      'direccion'=>'C/Gran Vía Marqués de Turia, 49, D705','cpostal'=>'46005','poblacion'=>'Valencia','provincia'=>'Valencia',
                      'registro'=>'Inscrita en el Registro Mercantil de Valencia al tomo 8598, Folio 56, Hoja V118965, inscripción 1ª'  ];
              else
                  $this->datos_vida=$cdatosEntidad[0]; 
            }
            
            $this->datos_vida['nombreCorto']="NO EXISTE";
            $sql="select * from tablaauxiliar where codTabla=8 and codElemento=:codEmpresa";
            $stmt = ($this->link)->prepare($sql);
            $stmt->bindValue(':codEmpresa',  $cfacturas[0]['codEmpresa']);
            if  ($stmt->execute()) {
                $ctabAux= $stmt->fetchAll(PDO::FETCH_ASSOC);
                $this->datos_vida['nombreCorto']=$ctabAux[0]['valor1'];
            }
            
            
          $this->logo='';
          if ($this->datos_vida['logo']) $this->logo=$this->dirbase.$this->datos_vida['logo'];// $logo="C:/eclipse-workspace/wealthmgr/img/VIDA_color.jpg";
          
          // datos empresa a quien factura
          $sql="select * from entidades where  id=:idEntidad";
          $stmt = ($this->link)->prepare($sql);
        //  $stmt->bindValue(':codEmpresa',  $cfacturas[0]['codEmpresa']);
          $stmt->bindValue(':idEntidad',  $cfacturas[0]['idCliente']);
          if  ($stmt->execute()) {
              $cdatosEntidad= $stmt->fetchAll(PDO::FETCH_ASSOC);
              if (count($cdatosEntidad)<=0)
                  $this->datos_aquien=['logo'=>'VIDA_color.jpg','nombre'=>'VILATA DARDER HOLDING SL','cif'=>'B97830426',
                      'direccion'=>'C/Gran Vía Marqués de Turia, 49, D705','cpostal'=>'46005','poblacion'=>'Valencia','provincia'=>'Valencia',
                      'registro'=>'Inscrita en el Registro Mercantil de Valencia al tomo 8598, Folio 56, Hoja V118965, inscripción 1ª'  ];
                  else
                      $this->datos_aquien=$cdatosEntidad[0];
          }
          //datos cab factura
          $this->datos_cabfactura=$cfacturas[0]; //'nroFactura'=>$cfacturas[0]['nombre'],'fecha'=>'01/10/2018','por_retencion'=>'19','retencion'=>'9.835,34','totalFactura'=>'123.456,78'];
          //datos lineas
          $this->datos_linfactura=$cfacturas[0]; 
          
      }
      
      private function imprimirLiquidacion($idmov=null,$aDisco=0) {
          if (isset($_REQUEST['aDisco']) && $aDisco==0) $aDisco = $_REQUEST['aDisco'];
          if ($idmov==null) $idmov= $_REQUEST['id'];
          $this->cargarDatosLiquidacion($idmov);  //cargarDatosMuestra();
          
          // DIBUJAR LIQUIDACION
          $pdf=new AlphaPDF(); // uso esta clase que implementa watermarks (transparencia) para el logo del fondo pagina
          $pdf->AddPage();
          $pdf->SetLeftMargin(15);
          if ($this->logo) { $pdf->Image($this->logo,16,16,40); // adding the logo to the pdf 
          }
          
          $pdf->SetFont('Arial','B',12); //set the font-related propert
          $pdf->SetY(50);
          $pdf->Cell(10,5,utf8_decode($this->datos_vida['nombre']));//'VILATA DARDER HOLDING SL');
          $pdf->Ln();// Line break
          $pdf->SetFont('Arial','',10); //set the font-related propert
          $pdf->Cell(10,5,'CIF: '.$this->datos_vida['cif']);// B97830426');
          $pdf->Ln();// Line break
          $pdf->Cell(10,5,utf8_decode($this->datos_vida['direccion']));//'C/Gran Vía Marqués de Turia, 49, D705');
          $pdf->Ln();// Line break
          $pdf->Cell(10,5,$this->datos_vida['cpostal'].' - '.utf8_decode($this->datos_vida['poblacion']));//'46005 - Valencia');
          $pdf->Ln();// Line break
          if ($this->datos_vida['poblacion']!=$this->datos_vida['provincia']) $pdf->Cell(10,3,utf8_decode($this->datos_vida['provincia']));//'Valencia');
          
          $pdf->SetXY(80,35);
          $pdf->SetFont('Arial','B',16); //set the font-related propert
          $pdf->SetTextColor(130,127,126); // gris
          $pdf->Cell(40,0,'LIQUIDACION');
          $pdf->SetTextColor(0,0,0);
          
          $pdf->SetAlpha(0.1);
          if ($this->logo) { $pdf->Image($this->logo,30,105,150); // adding the logo to the pdf
          }
          $pdf->SetAlpha(1);
          
          // apartados de FECHA y NRO FACTURA
          $pdf->SetY(80);
          $pdf->SetFont('Arial','',10); //set the font-related propert
          setlocale(LC_TIME, 'es_ES.UTF-8');
          $pdf->Cell(40,8,'En '.$this->datos_vida['poblacion'].' a '.strftime("%d de %B de %Y",strtotime($this->datos_cabfactura['fecha'])),0); //date("d F Y", strtotime($this->datos_cabfactura['fecha'])),0);
           $pdf->Ln();// Line break
          
          // DATOS DEL CLIENTE
          $x=110;
          $pdf->SetXY($x,50);
          $pdf->Cell(10,3,utf8_decode($this->datos_aquien['nombre']),0,2);
          $pdf->Ln();// Line break
          $pdf->SetFont('Arial','',10); //set the font-related propert
          $pdf->setX($x);
          $pdf->Cell(10,3,'CIF: '.$this->datos_aquien['cif'],0,2);
          $pdf->Ln();// Line break
          $pdf->setX($x);
          $pdf->Cell(10,3,utf8_decode($this->datos_aquien['direccion']),0,2);
          $pdf->Ln();// Line break
          $pdf->setX($x);
          $pdf->Cell(10,3,$this->datos_aquien['cpostal'].' - '.utf8_decode($this->datos_aquien['poblacion']),0,2);
          $pdf->Ln();// Line break
          $pdf->setX($x);
          $pdf->Cell(10,3,utf8_decode($this->datos_aquien['provincia']),0,2);
          
          //CUERPO DE FACTURA
          $y=90;
          $pdf->SetY($y);
          $pdf->SetFont('Arial','',10); //set the font-related propert
          
         
          $pdf->MultiCell(150,5, utf8_decode($this->replaceString($this->datos_linfactura['descripcion'],1)));
   
          if ($aDisco==1) {
              $nombrePDF='cartaLiq_'.$idmov.' '.substr($this->datos_aquien['nombre'],0,20)."_".date('MY').".pdf";
              $dirbase= $_SERVER['DOCUMENT_ROOT'].'/privado/';
              $dirbase=$dirbase."uploads/";
              $pdf->Output($dirbase.$nombrePDF,'F');
              
              $sql="update cabfacturas set archivoDrive=:nombrePDF,estadoFactura='PENDIENTE',carpeta='' ".
                  " where id=:lastId";
              $stmt = ($this->link)->prepare($sql);
              $stmt->bindValue(':lastId', $this->datos_linfactura['idObjeto']);
              $stmt->bindValue(':nombrePDF', $nombrePDF);
              $stmt->execute();
              
              //subir a Onedrive
              $url="/privado/php/onedrive/uploadFactura.php?empresa=".$this->datos_vida['nombreCorto']."&nombrePDF=".$nombrePDF."&nombrePDF_tmp=".$dirbase.$nombrePDF;
              header('Location: '.$url);
              
          
          
          } else  {
              $pdf->Output();
          }
          
      }
      
      private function replaceString($str,$convierteChars) {
          $fecha = new DateTime($this->datos_cabfactura['fecha']);
          $intervalo = new DateInterval('P1M');
          $fechaAnt=clone $fecha;
          $fechaAnt->sub($intervalo);
          $fechaSig=clone $fecha;
          $fechaSig->add($intervalo);
          
          $curMonth = $fecha->format('m');
          $curQuarter = ceil($curMonth/3);
          
          $antQuarter = $curQuarter-1;
          if ($antQuarter==0) $antQuarter=4;

          
          setlocale(LC_TIME, 'es_ES.UTF-8');
          $str=str_replace("%Tant",$antQuarter,$str);
          $str=str_replace("%T",$curQuarter,$str);
          $str=str_replace("%Mant",strtoupper(strftime("%B",$fechaAnt->getTimestamp())),$str);
          $str=str_replace("%Msig",strtoupper(strftime("%B",$fechaSig->getTimestamp())),$str);
          $str=str_replace("%M",strtoupper(strftime("%B",$fecha->getTimestamp())),$str);
          $str=str_replace("%Ytant",strftime("%Y",$fechaAnt->getTimestamp()),$str);
          $str=str_replace("%Y",strftime("%Y",$fecha->getTimestamp()),$str);
          if ($convierteChars==1) $str=str_replace("%E",utf8_encode(chr(128)),$str);
          //.chr(128)
          return $str;
      }
  }
  
  // Initiiate Library
    $api = new pdf_liquidacion();
    $api->analiza_method();

?>