    <?php 
  require_once('../lib/fpdf/alphapdf.php');
  require_once("../lib/bd_api_pdo.php");
  require_once("../onedrive/onedrive.php");
  require_once("bd_fichajes_class.php");
  
  class pdf_fichajes_class extends BD_API {
      // CARGAR DATOS
      protected $dirbase=''; //$_SERVER['DOCUMENT_ROOT'].'/wealthmgr/img/'; // directorio logos
      protected $datos_vida=[]; // datos empresa que emplea
      protected $logo='';
      protected $datos_aquien=[];       // datos empleado
      protected $datos_fichajes=[[]];//datos fichajes
      protected $datos_fichajesTot=[[]];//datos totales
      protected $api=null;
      
      
      public function main_imprimirFichajes($aDisco,$to,$cc) {
        $this->dirbase=$_SERVER['DOCUMENT_ROOT'].'/privado/';
        $this->cargarDatosFichajes();
//         $this->cargarDatosMuestra();//
        $this->imprimeFichajes($aDisco,$to,$cc); 
      }
      
      // cargar Datos Muestra
      protected function cargarDatosMuestra() {
          // datos empresa que factura
          $this->datos_vida=['logo'=>'VIDA_color.jpg','nombre'=>'VILATA DARDER HOLDING SL','cif'=>'B97830426',
          'direccion'=>'C/Gran Vía Marqués de Turia, 49, D705','cpostal'=>'46005','poblacion'=>'Valencia','provincia'=>'Valencia',
              'registro'=>'Inscrita en el Registro Mercantil de Valencia al tomo 8598, Folio 56, Hoja V118965, inscripción 1ª'  ];
          $this->logo='';
          if ($this->datos_vida['logo']) $this->logo=$this->dirbase."img/".$this->datos_vida['logo'];// $logo="C:/eclipse-workspace/wealthmgr/img/VIDA_color.jpg";
          
          // datos empresa a quien factura
          $this->datos_aquien=['nombre'=>'JOSE BLAS VILATA TAMARIT','nif'=>'24344350T','direccion'=>'C/Gran Vía Marqués de Turia, 49, D705','cpprov'=>'46005 - Valencia'];
          //datos cab factura
          $this->datos_fichajesTot=[['tipoJornada'=>'ORDINARIA','estado'=>'APROBADA','dias'=>'0','horas'=>'8','minutos'=>'0'],['tipoJornada'=>'ORDINARIA','estado'=>'APROBADA','dias'=>'0','horas'=>'8','minutos'=>'0']];
          //datos lineas
          $this->datos_fichajes=[['tipoJornada'=>'ORDINARIA','fechaInicio'=>'01-01-2019 09:00:00','fechaFin'=>'01-01-2019 14:00:00','difDias'=>'0','difHoras'=>'8','difMinutos'=>'0','estado'=>'APROBADO'],
          ['tipoJornada'=>'ORDINARIA','fechaInicio'=>'02-01-2019 09:00:00','fechaFin'=>'02-01-2019 14:00:00','difDias'=>'0','difHoras'=>'8','difMinutos'=>'0','estado'=>'APROBADO']];
          
      }
      // cargar datos de la factura
      private function cargarDatosFichajes(){
          //php/fichajes/pdf_fichajes.php/?idpersonal=4&fechaD=2019-01-01T00:00:00&fechaH=2019-06-01T00:00:00
          // CARGAR DATOS
          $idpersonal=( isset($_REQUEST['idpersonal'])?$_REQUEST['idpersonal']:NULL);
          $fechaD=( isset($_REQUEST['fechaD'])?$_REQUEST['fechaD']:NULL);
          $fechaH=( isset($_REQUEST['fechaH'])?$_REQUEST['fechaH']:NULL);
          
          $api=new bd_fichajes();
          $cfichajes=$api->findFichajesFilter(1); // devuelve array con fichajes seleccionados
          $this->datos_fichajesTot=$api->comprobarHoras(-1,$idpersonal,$fechaD,$fechaH);

          // datos empleado
          $sql="select * from personal where id=:idpersonal";
          $stmt = ($this->link)->prepare($sql);
          $stmt->bindValue(':idpersonal',  $idpersonal);
          if  ($stmt->execute()) {
              $recpersona= $stmt->fetchAll(PDO::FETCH_ASSOC);
              $this->datos_aquien=$recpersona[0];
          }
          
          // datos empresa 
            $sql="select * from entidades where codEmpresa=:codEmpresa and tipoentidad='SELF'";
            $stmt = ($this->link)->prepare($sql);
            $stmt->bindValue(':codEmpresa',  $recpersona[0]['codEmpresa']);
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
            $stmt->bindValue(':codEmpresa',   $recpersona[0]['codEmpresa']);
            if  ($stmt->execute()) {
                $ctabAux= $stmt->fetchAll(PDO::FETCH_ASSOC);
                $this->datos_vida['nombreCorto']=$ctabAux[0]['valor1'];
            }
            
          $this->logo='';
          if ($this->datos_vida['logo']) $this->logo=$this->dirbase."img/".$this->datos_vida['logo'];// $logo="C:/eclipse-workspace/wealthmgr/img/VIDA_color.jpg";
          
         //datos fichajes
          $this->datos_fichajes=$cfichajes; //[['descripcion'=>'ESTO ES UN CONCEPTO','unidades'=>'10','precio'=>'13.567,45','pdescuento'=>'0','neto'=>'135.345,67','piva'=>'21'],
//              ['descripcion'=>'ESTO ES UN CONCEPTO','unidades'=>'10','precio'=>'13.567,45','pdescuento'=>'0','neto'=>'135.345,67','piva'=>'21']];
          
      }
      
      public function imprimeFichajes($aDisco,$to,$cc) {
          // DIBUJAR LISTADO
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
          $pdf->Cell(10,3,'NIF: '.$this->datos_vida['cif']);// B97830426');
          $pdf->Ln();// Line break
          $pdf->Cell(10,3,utf8_decode($this->datos_vida['direccion']));//'C/Gran Vía Marqués de Turia, 49, D705');
          $pdf->Ln();// Line break
          $pdf->Cell(10,3,$this->datos_vida['cpostal'].' - '.utf8_decode($this->datos_vida['poblacion']));//'46005 - Valencia');
          $pdf->Ln();// Line break
          if ($this->datos_vida['poblacion']!=$this->datos_vida['provincia']) $pdf->Cell(10,3,utf8_decode($this->datos_vida['provincia']));//'Valencia');
          
          $pdf->SetXY(110,35);
          $pdf->SetFont('Arial','B',16); //set the font-related propert
          $pdf->SetTextColor(130,127,126); // gris
          $pdf->Cell(40,0,'REGISTRO DE JORNADAS');
          $pdf->SetTextColor(0,0,0);
          
          $pdf->SetAlpha(0.1);
          if ($this->logo) { $pdf->Image($this->logo,30,105,150); // adding the logo to the pdf
          }
          $pdf->SetAlpha(1);
          
          // apartados de FECHA y NRO FACTURA
          $pdf->SetY(65);
          $pdf->SetFont('Arial','',8); //set the font-related propert
          $pdf->Cell(40,8,'Listado generado a fecha: '.date('d/m/Y')); //("d/m/Y", strtotime($this->datos_fichaje['fecha'])),1);
           $pdf->Ln();// Line break
  
        
          // DATOS DEL CLIENTE
          $x=110;
          $pdf->SetXY($x,50);
          $pdf->SetFont('Arial','B',10); //set the font-related propert
          $pdf->setX($x);
          $pdf->Cell(10,3,'EMPLEADO:',0,2);
          $pdf->Ln();// Line break
          $pdf->setX($x);
          $pdf->Cell(10,3,utf8_decode($this->datos_aquien['nombre']),0,2);
          $pdf->Ln();// Line break
          $pdf->SetFont('Arial','',10); //set the font-related propert
          $pdf->setX($x);
          $pdf->Cell(10,3,'NIF: '.$this->datos_aquien['nif'],0,2);
          $pdf->Ln();// Line break
          $pdf->setX($x);
         
          //CUERPO DE LISTADO
          //cabecera de lineas
          $y=75;
          $pdf->SetY($y);
          $pdf->Cell(150,8,'',1);
          $pdf->setY($y); 
          $pdf->SetFont('Arial','B',8); //set the font-related propert
          $pdf->Cell(30,8,'TIPO JORNADA');
          $pdf->Cell(30,8,'FECHA INICIO',0,0,'C');
          $pdf->Cell(30,8,'FECHA FIN',0,0,'C');
          $pdf->Cell(30,8,'HORAS',0,0,'C');
          $pdf->Cell(30,8,'ESTADO',0,0,'C');
          $pdf->Ln();// Line break
          
          $pdf->SetFont('Arial','',8); //set the font-related propert
          $x=$pdf->getX();
          $y=$pdf->getY();
          $altura=150; //125;
          $pdf->Cell(30,$altura,'',1); // TIPO JORNADA
          $pdf->Cell(30,$altura,'',1); // FECHA I
          $pdf->Cell(30,$altura,'',1);//FECHA F
          $pdf->Cell(30,$altura,'',1);   //HORAS
          $pdf->Cell(30,$altura,'',1); //ESTADO
          $pdf->SetXY($x, $y+3);
          
          // bucle repeticion de lineas
          for ($i=0;$i<count($this->datos_fichajes);$i++) {
                $pdf->Cell(30,5, $this->datos_fichajes[$i]['tipoJornada'],0,0,'C');
           //       $pdf->SetXY($x+78,$pdf->getY()-5);
                  $pdf->Cell(30,5,$this->datos_fichajes[$i]['fechaInicio'],0,0,'C');
                  $pdf->Cell(30,5,$this->datos_fichajes[$i]['fechaFin'],0,0,'C');//
                  $str1="";
                  if ($this->datos_fichajes[$i]['difDias']>0) $str1=$str1. $this->datos_fichajes[$i]['difDias']." dias ";
                  if ($this->datos_fichajes[$i]['difHoras']>0) $str1=$str1. $this->datos_fichajes[$i]['difHoras']." horas ";
                  if ($this->datos_fichajes[$i]['difMinutos']>0) $str1=$str1. $this->datos_fichajes[$i]['difMinutos']." min ";
                  $pdf->Cell(30,5,$str1,0,0,'C');   //
                  $pdf->Cell(30,5,$this->datos_fichajes[$i]['estado'],0,0,'C'); //
             $pdf->Ln();// Line break
          }
          // TOTALES
          //cabecera totales
          $y=$y+$altura+5;
          $pdf->SetY($y);
          $pdf->Cell(150,8,'',1);
          $pdf->setY($y);
          $pdf->SetFont('Arial','B',8); //set the font-related propert
          $pdf->Cell(30,8,'TIPO JORNADA',0,0,'C');
          $pdf->Cell(30,8,'ESTADO',0,0,'C');
          $pdf->Cell(45,8,'TOTAL HORAS',0,0,'C');
          $pdf->Cell(45,8,'',0,0,'C');
      //   $pdf->Cell(30,8,'',0,0,'C');
          
          // bucle repeticion bases
          for ($i=0; $i<count($this->datos_fichajesTot);$i++) {
              $pdf->Ln();// Line break
              $pdf->SetFont('Arial','',8); //set the font-related propert
              $pdf->Cell(30,8,$this->datos_fichajesTot[$i]['tipoJornada'],1,0,'C');
              $pdf->Cell(30,8,$this->datos_fichajesTot[$i]['estado'],1,0,'C');
              $str1="";
              if ($this->datos_fichajesTot[$i]['dias']>0) $str1=$str1. $this->datos_fichajesTot[$i]['dias']." dias ";
              if ($this->datos_fichajesTot[$i]['horas']>0) $str1=$str1. $this->datos_fichajesTot[$i]['horas']." horas ";
              if ($this->datos_fichajesTot[$i]['minutos']>0) $str1=$str1. $this->datos_fichajesTot[$i]['minutos']." min ";
              $pdf->Cell(45,8,$str1,1,0,'C');
              $pdf->Cell(45,8,'',1,0,'C');
          }
        
        
          
          //PIE 
          $pdf->SetY(270);
          $pdf->SetFont('Arial','',8); //set the font-related propert
          $pdf->SetTextColor(130,127,126); // gris
          $pdf->Cell(0,5, 'Listado de registro de jornadas a disposición del empleado en cumplimiento de la normativa laboral al respecto',0,0,'C');
          
          $nombrePDF="";
          if ($aDisco==1) {
              $nombrePDF=substr($this->datos_aquien['nombre'],0,20)."_".$this->datos_fichaje['nroFactura']."_".date('MY').".pdf";
              $dirbase=$this->dirbase."uploads/";
              $pdf->Output($dirbase.$nombrePDF,'F');
              
              $fechaD=date_format(date_create_from_format('Y-m-d\TH:i:s', $_REQUEST['fechaD']),"d-m-Y H:i:s");
              $fechaH=date_format(date_create_from_format('Y-m-d\TH:i:s', $_REQUEST['fechaH']),"d-m-Y H:i:s");
              
              $asunto="Listado de Registro de Jornada Laboral de ".$this->datos_aquien['nombre']." entre ".$fechaD." y ".$fechaH;
              $cuerpo="Hola,<br>Le adjuntamos el ".$asunto.".<br><br>Atentamente, <br>VILATA DARDER HOLDING SL<br>".
                    "<img src='http://vidawm.com/img/VIDA_color.jpg'  width='100'>";
              $api=new SendMail();
              $api->envia("send", $to,$cc,$asunto,$cuerpo, "", $dirbase.$nombrePDF);//html,attach
          } else  {
              $pdf->Output();
          }
          return $nombrePDF;
      }
  }
  

?>