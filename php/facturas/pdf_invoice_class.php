    <?php 
  require_once('../lib/fpdf/alphapdf.php');
  require_once("../lib/bd_api_pdo.php");
  require_once("../onedrive/onedrive.php");
  require_once("bd_facturas_class.php");
  
  class pdf_invoice_class extends BD_API {
      // CARGAR DATOS
      protected $dirbase=''; //$_SERVER['DOCUMENT_ROOT'].'/wealthmgr/img/'; // directorio logos
      protected $datos_vida=[]; // datos empresa que factura
      protected $logo='';
      protected $datos_aquien=[];       // datos empresa a quien factura
      protected $datos_cabfactura=[]; //datos cab factura
      protected $datos_iva=[[]];
      protected $datos_linfactura=[[]];//datos lineas
      
      public function main_imprimirFactura($id, $aDisco) {
        $this->dirbase=$_SERVER['DOCUMENT_ROOT'].'/privado/';
        $this->cargarDatosFactura($id);  //cargarDatosMuestra();
        $this->imprimeFactura($id,$aDisco); 
      }
      
      // cargar Datos Muestra
      protected function cargarDatosMuestra() {
          // datos empresa que factura
          $this->datos_vida=['logo'=>'VIDA_color.jpg','nombre'=>'VILATA DARDER HOLDING SL','cif'=>'B97830426',
              'direccion'=>'C/Gran Vía Marqués de Turia, 49, D705','cpprov'=>'46005 - Valencia',
              'registro'=>'Inscrita en el Registro Mercantil de Valencia al tomo 8598, Folio 56, Hoja V118965, inscripción 1ª'  ];
          $this->logo='';
          if ($this->datos_vida['logo']) $this->logo=$this->dirbase."img/".$this->datos_vida['logo'];// $logo="C:/eclipse-workspace/wealthmgr/img/VIDA_color.jpg";
          
          // datos empresa a quien factura
          $this->datos_aquien=['nombre'=>'CONSUM SOCIEDAD COOP. SLP','cif'=>'B97830426','direccion'=>'C/Gran Vía Marqués de Turia, 49, D705','cpprov'=>'46005 - Valencia'];
          //datos cab factura
          $this->datos_cabfactura=['nroFactura'=>'100','fecha'=>'01/10/2018','por_retencion'=>'19','retencion'=>'9.835,34','totalFactura'=>'123.456,78'];
          $this->datos_iva=[['piva'=>'10','totalIva'=>'12.56,67','base'=>'138.567,45'],['piva'=>'21','totalIva'=>'1.560,67','base'=>'13.567,45']];
          //datos lineas
          $this->datos_linfactura=[['descripcion'=>'ESTO ES UN CONCEPTO','unidades'=>'10','precio'=>'13.567,45','pdescuento'=>'0','neto'=>'135.345,67','piva'=>'21'],
              ['descripcion'=>'ESTO ES UN CONCEPTO','unidades'=>'10','precio'=>'13.567,45','pdescuento'=>'0','neto'=>'135.345,67','piva'=>'21']];
          
      }
      // cargar datos de la factura
      private function cargarDatosFactura($id){
          // CARGAR DATOS
          $cfacturas=[];
          $cDesgloseIvaFactura=[];
          // datos factura actual
          $sql="select * from cfacturas where id=:id";
          $stmt = ($this->link)->prepare($sql);
          $stmt->bindParam(':id',  $id);
          if  ($stmt->execute()) {
              $cfacturas= $stmt->fetchAll(PDO::FETCH_ASSOC);
              if (count($cfacturas)<=0)  return;// no existe salgo
              $sql="select * from cdesgloseivafactura where id=:id";
              $stmt = ($this->link)->prepare($sql);
              $stmt->bindParam(':id',  $id);
              if  ($stmt->execute()) 
                  $cDesgloseIvaFactura= $stmt->fetchAll(PDO::FETCH_ASSOC);
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
          if ($this->datos_vida['logo']) $this->logo=$this->dirbase."img/".$this->datos_vida['logo'];// $logo="C:/eclipse-workspace/wealthmgr/img/VIDA_color.jpg";
          
          // datos empresa a quien factura
          $this->datos_aquien=$cfacturas[0]; //['nombre'=>$cfacturas[0]['nombre'],'cif'=>$cfacturas[0]['cif'],'direccion'=>$cfacturas[0]['direccion'],'cpprov'=>$cfacturas[0]['cpostal'].' - '.$cfacturas[0]['provincia']];
          //datos cab factura
          $this->datos_cabfactura=$cfacturas[0]; //'nroFactura'=>$cfacturas[0]['nombre'],'fecha'=>'01/10/2018','por_retencion'=>'19','retencion'=>'9.835,34','totalFactura'=>'123.456,78'];
          $this->datos_iva=$cDesgloseIvaFactura; //[['piva'=>'10','totalIva'=>'12.56,67','base'=>'138.567,45'],['piva'=>'21','totalIva'=>'1.560,67','base'=>'13.567,45']];
          //datos lineas
          $this->datos_linfactura=$cfacturas; //[['descripcion'=>'ESTO ES UN CONCEPTO','unidades'=>'10','precio'=>'13.567,45','pdescuento'=>'0','neto'=>'135.345,67','piva'=>'21'],
//              ['descripcion'=>'ESTO ES UN CONCEPTO','unidades'=>'10','precio'=>'13.567,45','pdescuento'=>'0','neto'=>'135.345,67','piva'=>'21']];
          
      }
      
      public function imprimeFactura($idCabFactura,$aDisco) {
          
          // DIBUJAR FACTURA
          $pdf=new AlphaPDF(); // uso esta clase que implementa watermarks (transparencia) para el logo del fondo pagina
          $pdf->AddPage();
          $pdf->SetLeftMargin(15);
          if ($this->logo) { 
              $pdf->Image($this->logo,16,16,40); // adding the logo to the pdf 
              $pdf->SetFont('Arial','',8); //set the font-related propert
              $pdf->SetY(50);
              $pdf->Cell(10,3,utf8_decode($this->datos_vida['nombre']));//'VILATA DARDER HOLDING SL');
          } else { // no hay logo
              $pdf->SetFont('Arial','B',16); //set the font-related propert
              $pdf->SetY(35);
              $pdf->MultiCell(80,5,utf8_decode($this->datos_vida['nombre']),0,'L');//'VILATA DARDER HOLDING SL');
              $pdf->SetY(40);
          }
          
          $pdf->SetFont('Arial','',8); //set the font-related propert
          $pdf->Ln();// Line break
          $pdf->Cell(10,3,'CIF: '.$this->datos_vida['cif']);// B97830426');
          $pdf->Ln();// Line break
          $pdf->Cell(10,3,utf8_decode($this->datos_vida['direccion']));//'C/Gran Vía Marqués de Turia, 49, D705');
          $pdf->Ln();// Line break
          $pdf->Cell(10,3,$this->datos_vida['cpostal'].' - '.utf8_decode($this->datos_vida['poblacion']));//'46005 - Valencia');
          $pdf->Ln();// Line break
          if ($this->datos_vida['poblacion']!=$this->datos_vida['provincia']) $pdf->Cell(10,3,utf8_decode($this->datos_vida['provincia']));//'Valencia');
          
          $pdf->SetXY(120,35);
          $pdf->SetFont('Arial','B',16); //set the font-related propert
          $pdf->SetTextColor(130,127,126); // gris
          $pdf->Cell(40,0,'FACTURA');
          $pdf->SetTextColor(0,0,0);
          
          $pdf->SetAlpha(0.1);
          if ($this->logo) { $pdf->Image($this->logo,30,105,150); // adding the logo to the pdf
          }
          $pdf->SetAlpha(1);
          
          // apartados de FECHA y NRO FACTURA
          $pdf->SetY(70);
          $pdf->SetFont('Arial','B',10); //set the font-related propert
          $pdf->Cell(40,8,'FECHA',1);
          $pdf->SetFont('Arial','',10); //set the font-related propert
          $pdf->Cell(40,8,date("d/m/Y", strtotime($this->datos_cabfactura['fecha'])),1);
           $pdf->Ln();// Line break
          $pdf->SetFont('Arial','B',10); //set the font-related propert
          $pdf->Cell(40,8,'FACTURA',1);
          $pdf->SetFont('Arial','',10); //set the font-related propert
          $pdf->Cell(40,8,$this->datos_cabfactura['nroFactura'],1);
        
          // DATOS DEL CLIENTE
          $x=110;
          $pdf->SetXY($x,50);
          $pdf->SetFont('Arial','B',10); //set the font-related propert
          $pdf->setX($x);
          $pdf->Cell(10,3,'CLIENTE:',0,2);
          $pdf->Ln();// Line break
          $pdf->setX($x);
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
          //cabecera de lineas
          $y=90;
          $pdf->SetY($y);
          $pdf->Cell(0,8,'',1,1);
          $pdf->setY($y); 
          $pdf->SetFont('Arial','B',10); //set the font-related propert
          $pdf->Cell(80,8,'CONCEPTO');
          $pdf->Cell(20,8,'UNID.',0,0,'C');
          $pdf->Cell(25,8,'PRECIO',0,0,'C');
          $pdf->Cell(15,8,'%DTO',0,0,'C');
          $pdf->Cell(30,8,'NETO',0,0,'C');
          $pdf->Cell(15,8,'%IVA',0,0,'C');
          $pdf->Ln();// Line break
          
          $pdf->SetFont('Arial','',8); //set the font-related propert
          $x=$pdf->getX();
          $y=$pdf->getY();
          $altura=140;
          $pdf->Cell(80,$altura,'',1); // CONCPETO
          $pdf->Cell(20,$altura,'',1); // UNID
          $pdf->Cell(25,$altura,'',1);//PRECIO
          $pdf->Cell(15,$altura,'',1);   //DTO
          $pdf->Cell(30,$altura,'',1); //NETO
          $pdf->Cell(15,$altura,'',1); //IVA
          $pdf->SetXY($x, $y+3);
          
          // bucle repeticion de lineas
          for ($i=0;$i<count($this->datos_linfactura);$i++) {
              $pdf->MultiCell(80,5, utf8_decode($this->datos_linfactura[$i]['descripcion']));
              if ($this->datos_linfactura[$i]['neto']<>0) {
                  $pdf->SetXY($x+78,$pdf->getY()-5);
                  $pdf->Cell(20,5,$this->datos_linfactura[$i]['unidades'],0,0,'R');
                  $pdf->Cell(25,5,number_format($this->datos_linfactura[$i]['precio'], 2, ',', '.'),0,0,'R');//PRECIO
                  $pdf->Cell(15,5,number_format($this->datos_linfactura[$i]['pdescuento'], 2, ',', '.'),0,0,'R');   //DTO
                  $pdf->Cell(30,5,number_format($this->datos_linfactura[$i]['neto'], 2, ',', '.'),0,0,'R'); //NETO
                  $pdf->Cell(15,5,number_format($this->datos_linfactura[$i]['piva'], 2, ',', '.'),0,0,'R'); //IVA
              }
             $pdf->Ln();// Line break
          }
          // TOTALES
          //cabecera totales
          $y=$y+$altura+5;
          $pdf->SetY($y);
          $pdf->Cell(0,8,'',1,1);
          $pdf->setY($y);
          $pdf->SetFont('Arial','B',10); //set the font-related propert
          $pdf->Cell(40,8,'BASE',0,0,'C');
          $pdf->Cell(20,8,'%IVA',0,0,'C');
          $pdf->Cell(35,8,'CUOTA',0,0,'C');
          $pdf->Cell(15,8,'%RET,',0,0,'C');
          $pdf->Cell(35,8,'RETENCION',0,0,'C');
          $pdf->Cell(40,8,'TOTAL FACTURA',0,0,'C');
          
          // bucle repeticion bases
          for ($i=0; $i<count($this->datos_iva);$i++) {
              $pdf->Ln();// Line break
              $pdf->SetFont('Arial','',10); //set the font-related propert
              $pdf->Cell(40,8,number_format($this->datos_iva[$i]['base'], 2, ',', '.'),1,0,'C');
              $pdf->Cell(20,8,number_format($this->datos_iva[$i]['piva'], 2, ',', '.'),1,0,'C');
              $pdf->Cell(35,8,number_format($this->datos_iva[$i]['totalIva'], 2, ',', '.'),1,0,'C');
              $x=$pdf->getX();
              $pdf->Cell(15,8,'',1,0,'C');
              $pdf->Cell(35,8,'',1,0,'C');
              $pdf->Cell(40,8,'',1,0,'C');
          }
        
        
          // total retencion y total factura
          $pdf->setX($x);
          $pdf->Cell(15,8,number_format($this->datos_cabfactura['por_retencion'], 2, ',', '.'),1,0,'C');
          $pdf->Cell(35,8,number_format($this->datos_cabfactura['retencion'], 2, ',', '.'),1,0,'C');
          $pdf->SetFont('Arial','B',10); //set the font-related propert
          $pdf->Cell(40,8,number_format($this->datos_cabfactura['totalFactura'], 2, ',', '.'),1,0,'C');
          
          
          //PIE FACTURA
          $pdf->SetY(260);
          $pdf->SetFont('Arial','',10); //set the font-related propert
//          $pdf->SetTextColor(130,127,126); // gris
          $cuenta= $this->datos_vida['cuentaCorriente'];
          $pdf->Cell(0,5, 'IBAN: '.substr($cuenta,0,4).' '.substr($cuenta,4,4).' '.substr($cuenta,8,4).' '.substr($cuenta,12,4).' '.substr($cuenta,16,4).' '.substr($cuenta,20,4));
          
          $pdf->SetY(270);
          $pdf->SetFont('Arial','',8); //set the font-related propert
          $pdf->SetTextColor(130,127,126); // gris
          $pdf->Cell(0,5, utf8_decode($this->datos_vida['registro']),0,0,'C');
          $nombrePDF="";
          if ($aDisco==1 || $aDisco==2) { //1 disco version old, 2 version 2020
              $nombrePDF=substr($this->datos_aquien['nombre'],0,20)."_".$this->datos_cabfactura['nroFactura']."_".date('MY').".pdf";
              $dirbase=$this->dirbase."uploads/";
              $pdf->Output($dirbase.$nombrePDF,'F');
              
              $api=new bd_facturas();
              $api->uploadFacturaOneDrive($idCabFactura, $nombrePDF, $this->datos_vida['nombreCorto'], $aDisco);
          } else  {
              $pdf->Output();
          }
          return $nombrePDF;
      }
  }
  
 

?>