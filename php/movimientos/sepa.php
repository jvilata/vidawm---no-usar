<?php

require_once("../lib/bd_api_pdo.php");
require_once ("../lib/sendmail_class.php");

class sepa extends BD_API {
    protected $datos_facturas=[[]];//datos facturas a pagar
    protected $datos_ordenante=[]; //datos ordenante pago
    
    public function generaStringPago() {

      $strInicio="<?xml version='1.0' encoding='UTF-8' standalone='yes'?>
                <Document xmlns='urn:iso:std:iso:20022:tech:xsd:pain.001.001.03'>
                    <CstmrCdtTrfInitn>
                        <GrpHdr>
                            <MsgId>%msgid%</MsgId>
                            <CreDtTm>%fechaHora%</CreDtTm>
                            <NbOfTxs>%numtran%</NbOfTxs>
                            <CtrlSum>%tottran%</CtrlSum>
                            <InitgPty>
                                <Nm>%nomordenante%</Nm>
                                <Id>
                                    <OrgId>
                                        <Othr>
                                            <Id>%idordenante%</Id>
                                        </Othr>
                                    </OrgId>
                                </Id>
                            </InitgPty>
                        </GrpHdr>
                        <PmtInf>
                            <PmtInfId>%msgid%</PmtInfId>
                            <PmtMtd>TRF</PmtMtd>
                            <BtchBookg>true</BtchBookg>
                            <PmtTpInf>
                                <SvcLvl>
                                    <Cd>SEPA</Cd>
                                </SvcLvl>
                            </PmtTpInf>
                            <ReqdExctnDt>%fecha%</ReqdExctnDt>
                            <Dbtr>
                                <Nm>%nomordenante%</Nm>
                                <PstlAdr>
                                    <Ctry>ES</Ctry>
                                    <AdrLine>%dirordenante%</AdrLine>
                                    <AdrLine>%pobordenante%</AdrLine>
                                </PstlAdr>
                            </Dbtr>
                            <DbtrAcct>
                                <Id>
                                    <IBAN>%cuentaordenante%</IBAN>
                                </Id>
                            </DbtrAcct>
                            <DbtrAgt>
                                <FinInstnId>
                                    <BIC>BSCHESMMXXX</BIC>
                                </FinInstnId>
                            </DbtrAgt>
                            <ChrgBr>SLEV</ChrgBr>";
       
      // bucle repeticion de lineas
      $tottran=0;$strCuerpo="";
      for ($i=0;$i<count($this->datos_facturas);$i++) {
          $strpago="       <CdtTrfTxInf>
                                <PmtId>
                                    <EndToEndId>NOTPROVIDED</EndToEndId>
                                </PmtId>
                                <Amt>
                                    <InstdAmt Ccy='EUR'>%totfac%</InstdAmt>
                                </Amt>
                                <Cdtr>
                                    <Nm>%nomempresa%</Nm>
                                </Cdtr>
                                <CdtrAcct>
                                    <Id>
                                        <IBAN>%cuenta%</IBAN>
                                    </Id>
                                </CdtrAcct>
                                <RmtInf>
                                    <Ustrd>%concepto%</Ustrd>
                                </RmtInf>
                            </CdtTrfTxInf>";
          $strpago=str_replace('%totfac%',$this->datos_facturas[$i]['importe'],$strpago);
          $strpago=str_replace('%nomempresa%',$this->datos_facturas[$i]['nombre'],$strpago);
          $strpago=str_replace('%cuenta%',$this->datos_facturas[$i]['cuentaCorriente'],$strpago);
          if(strpos($this->datos_facturas[$i]['descripcion'], "\n") !== FALSE) {
              $strpago=str_replace('%concepto%',substr($this->datos_facturas[$i]['descripcion'],0,strpos($this->datos_facturas[$i]['descripcion'], "\n")),$strpago);
          }
          else {//not found
              $strpago=str_replace('%concepto%',substr($this->datos_facturas[$i]['descripcion'],0,50),$strpago);
          }
          
          $tottran=$tottran+$this->datos_facturas[$i]['importe'];
          $strCuerpo=$strCuerpo.$strpago;
      }
      
      $strFin="          </PmtInf>
                    </CstmrCdtTrfInitn>
                </Document>";
      
      $strInicio=str_replace('%nomordenante%',$this->datos_ordenante['nombre'],$strInicio);
      $strInicio=str_replace('%dirordenante%',$this->datos_ordenante['direccion'],$strInicio);
      $strInicio=str_replace('%pobordenante%',$this->datos_ordenante['poblacion'],$strInicio);
      $strInicio=str_replace('%cuentaordenante%',$this->datos_ordenante['cuentaCorriente'],$strInicio);
      $strInicio=str_replace('%idordenante%',$this->datos_ordenante['cif'].'VID',$strInicio);
      
      $fechaHora=date("Y-m-d\TH:i:s");
      $fecha=date("Y-m-d");
      $msgid=$this->datos_ordenante['cif'].date("Ymd").date("Ymd\THis");
      $strInicio=str_replace('%msgid%',$msgid,$strInicio);
      $strInicio=str_replace('%fechaHora%',$fechaHora,$strInicio);
      $strInicio=str_replace('%fecha%',$fecha,$strInicio);
      $strInicio=str_replace('%numtran%',count($this->datos_facturas),$strInicio);
      $strInicio=str_replace('%tottran%',$tottran,$strInicio);
      
      return $strInicio.$strCuerpo.$strFin;
    }

    public function generaStringCobro() {
        $fechaHora=date("Y-m-d\TH:i:s");
        $fecha=date("Y-m-d");
        $msgid=$this->datos_ordenante['cif'].date("Ymd").date("Ymd\THis");
        
        $strInicio="<?xml version='1.0' encoding='UTF-8' standalone='yes'?>
<Document xmlns='urn:iso:std:iso:20022:tech:xsd:pain.008.001.02'>
    <CstmrDrctDbtInitn>
        <GrpHdr>
            <MsgId>%msgid%</MsgId>
            <CreDtTm>%fechaHora%</CreDtTm>
            <NbOfTxs>%numtran%</NbOfTxs>
            <CtrlSum>%tottran%</CtrlSum>
            <InitgPty>
                <Nm>%nomordenante%</Nm>
                <Id>
                    <PrvtId>
                        <Othr>
                            <Id>ES17001%idordenante%</Id>
                        </Othr>
                    </PrvtId>
                </Id>
            </InitgPty>
        </GrpHdr>
        <PmtInf>
            <PmtInfId>%msgid%</PmtInfId>
            <PmtMtd>DD</PmtMtd>
            <NbOfTxs>%numtran%</NbOfTxs>
            <CtrlSum>%tottran%</CtrlSum>
            <PmtTpInf>
                <SvcLvl>
                    <Cd>SEPA</Cd>
                </SvcLvl>
                <LclInstrm>
                    <Cd>CORE</Cd>
                </LclInstrm>
                <SeqTp>FRST</SeqTp>
            </PmtTpInf>
            <ReqdColltnDt>%fecha%</ReqdColltnDt>
            <Cdtr>
                <Nm>%nomordenante%</Nm>
                <Id>
                    <PrvtId>
                        <Othr>
                            <Id>ES17001%idordenante%</Id>
                        </Othr>
                    </PrvtId>
                </Id>
            </Cdtr>
            <CdtrAcct>
                <Id>
                    <IBAN>%cuentaordenante%</IBAN>
                </Id>
                <Ccy>EUR</Ccy>
            </CdtrAcct>
            <CdtrAgt>
                <FinInstnId>
                    <BIC>BSCHESMMXXX</BIC>
                </FinInstnId>
            </CdtrAgt>
            <ChrgBr>SLEV</ChrgBr>
            <CdtrSchmeId>
                <Id>
                    <PrvtId>
                        <Othr>
                            <Id>ES17001%idordenante%</Id>
                            <SchmeNm>
                                <Prtry>SEPA</Prtry>
                            </SchmeNm>
                        </Othr>
                    </PrvtId>
                </Id>
            </CdtrSchmeId>";
        
        // bucle repeticion de lineas
        $tottran=0;$strCuerpo="";
        for ($i=0;$i<count($this->datos_facturas);$i++) {
            $strpago="            <DrctDbtTxInf>
                <PmtId>
                    <EndToEndId>".($i+1)."</EndToEndId>
                </PmtId>
                <InstdAmt Ccy='EUR'>%totfac%</InstdAmt>
                <DrctDbtTx>
                    <MndtRltdInf>
                        <MndtId>%nomempresa%</MndtId>
                        <DtOfSgntr>%fecha%</DtOfSgntr>
                        <AmdmntInd>false</AmdmntInd>
                    </MndtRltdInf>
                </DrctDbtTx>
                <DbtrAgt>
                    <FinInstnId>
                        <Othr>
                            <Id>NOTPROVIDED</Id>
                        </Othr>
                    </FinInstnId>
                </DbtrAgt>
                <Dbtr>
                    <Nm>%nomempresa%</Nm>
                    <PstlAdr>
                        <Ctry>ES</Ctry>
                    </PstlAdr>
                </Dbtr>
                <DbtrAcct>
                    <Id>
                        <IBAN>%cuenta%</IBAN>
                    </Id>
                    <Ccy>EUR</Ccy>
                </DbtrAcct>
                <RmtInf>
                    <Ustrd>%concepto%</Ustrd>
                </RmtInf>
            </DrctDbtTxInf>
        ";
            $strpago=str_replace('%totfac%',$this->datos_facturas[$i]['importe'],$strpago);
            $strpago=str_replace('%nomempresa%',$this->datos_facturas[$i]['nombre'],$strpago);
            $strpago=str_replace('%cuenta%',$this->datos_facturas[$i]['cuentaCorriente'],$strpago);
            $strpago=str_replace('%concepto%',$this->datos_facturas[$i]['descripcion'],$strpago);
            $strpago=str_replace('%fecha%',$fecha,$strpago);
            
            $tottran=$tottran+$this->datos_facturas[$i]['importe'];
            $strCuerpo=$strCuerpo.$strpago;
        }
        
        $strFin="        </PmtInf>
                    </CstmrDrctDbtInitn>
                </Document>
            ";
        
        $strInicio=str_replace('%nomordenante%',$this->datos_ordenante['nombre'],$strInicio);
        $strInicio=str_replace('%dirordenante%',$this->datos_ordenante['direccion'],$strInicio);
        $strInicio=str_replace('%pobordenante%',$this->datos_ordenante['poblacion'],$strInicio);
        $strInicio=str_replace('%cuentaordenante%',$this->datos_ordenante['cuentaCorriente'],$strInicio);
        $strInicio=str_replace('%idordenante%',$this->datos_ordenante['cif'],$strInicio);
        
        $strInicio=str_replace('%msgid%',$msgid,$strInicio);
        $strInicio=str_replace('%fechaHora%',$fechaHora,$strInicio);
        $strInicio=str_replace('%fecha%',$fecha,$strInicio);
        $strInicio=str_replace('%numtran%',count($this->datos_facturas),$strInicio);
        $strInicio=str_replace('%tottran%',$tottran,$strInicio);
        
        return $strInicio.$strCuerpo.$strFin;
    }
    
    public function cargarPagosCobros($codEmpresa) {
        // datos empresa que factura
        $sql="select * from entidades where codEmpresa=:codEmpresa and tipoentidad='SELF'";
        $stmt = ($this->link)->prepare($sql);
        $stmt->bindValue(':codEmpresa',  $codEmpresa);
        if  ($stmt->execute()) {
            $cdatosEntidad= $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($cdatosEntidad)<=0)
                $this->datos_ordenante=['logo'=>'VIDA_color.jpg','nombre'=>'VILATA DARDER HOLDING SL','cif'=>'B97830426',
                    'direccion'=>'C/Gran Vía Marqués de Turia, 49, D705','cpostal'=>'46005','poblacion'=>'Valencia','provincia'=>'Valencia',
                    'registro'=>'Inscrita en el Registro Mercantil de Valencia al tomo 8598, Folio 56, Hoja V118965, inscripción 1ª' ,
                    'cuentaCorriente'=>'ES2900490332272290187435'
                ];
            else
                 $this->datos_ordenante=$cdatosEntidad[0];
        }
        // CARGAR DATOS
        // datos factura actual
        $sql="select * from cpagoscobros where codEmpresa=:codEmpresa and generar=1";
        $stmt = ($this->link)->prepare($sql);
        $stmt->bindParam(':codEmpresa',  $codEmpresa);
        if  ($stmt->execute()) {
            $cfacturas= $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($cfacturas)<=0)  return;// no existe salgo
            else {
                $this->datos_facturas=$cfacturas;
                // actualizar campo fecha generacion
                $sql="update movimientos set fechaGeneracion=now() where codEmpresa=:codEmpresa and generar=1";
                $stmt = ($this->link)->prepare($sql);
                $stmt->bindParam(':codEmpresa',  $codEmpresa);
                $stmt->execute();
            }

        }
        
    }
    public function enviarMailsPagosCobros($codEmpresa,$tipoOperacion) {
        // datos empresa que factura
        $sql="select * from entidades where codEmpresa=:codEmpresa and tipoentidad='SELF'";
        $stmt = ($this->link)->prepare($sql);
        $stmt->bindValue(':codEmpresa',  $codEmpresa);
        if  ($stmt->execute()) {
            $cdatosEntidad= $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($cdatosEntidad)<=0)
                $this->datos_ordenante=['logo'=>'VIDA_color.jpg','nombre'=>'VILATA DARDER HOLDING SL','cif'=>'B97830426',
                    'direccion'=>'C/Gran Vía Marqués de Turia, 49, D705','cpostal'=>'46005','poblacion'=>'Valencia','provincia'=>'Valencia',
                    'registro'=>'Inscrita en el Registro Mercantil de Valencia al tomo 8598, Folio 56, Hoja V118965, inscripción 1ª' ,
                    'cuentaCorriente'=>'ES2900490332272290187435'
                ];
                else
                    $this->datos_ordenante=$cdatosEntidad[0];
        }
        // CARGAR DATOS
        // datos factura actual
        if ($tipoOperacion=='NOMINA') $strtabla='cpagosnominas';
        else $strtabla='cpagoscobros';
        $sql="select * from ".$strtabla." where codEmpresa=:codEmpresa and generar=1";
        $stmt = ($this->link)->prepare($sql);
        $stmt->bindParam(':codEmpresa',  $codEmpresa);
        if  ($stmt->execute()) {
            $cfacturas= $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($cfacturas)<=0)  return;// no existe salgo
            else {
                
                for ($i=0;$i<count($cfacturas);$i++){
                    if ($cfacturas[$i]['email']!=="") {
                        $api_sendmail = new SendMail();
                        $destino=$cfacturas[$i]['email'];
                        if ($cfacturas[$i]['email2']!=="") {
                            $destino.=";".$cfacturas[$i]['email2'];
                            if ($cfacturas[$i]['email3']!=="") {
                                $destino.=";".$cfacturas[$i]['email3'];
                            }
                        }
                        $cc="jvilata@edicom.es";
                        $strobjeto='factura';
                        if ($tipoOperacion=='NOMINA') $strobjeto='nómina';
                        $asunto=$this->datos_ordenante['nombre']." ha ordenado el pago de su ".$strobjeto." de importe ".number_format($cfacturas[$i]['importe'],2);
                        $str="Hola,<br>En esta fecha, ".$asunto." euros y referencia ".$cfacturas[$i]['descripcion']. " en su cuenta ".$cfacturas[$i]['cuentaCorriente']."<br>Atentamente<br>VILATA DARDER HOLDING SL<br>".
                            "<img src='http://vidawm.com/img/VIDA_color.jpg'  width='100'>";
                        $api_sendmail->envia("send", $destino,$cc,$asunto." (A/a:".$cfacturas[$i]['nombre'].")",$str, "", "");
                        echo "Enviado mail a ".$cfacturas[$i]['nombre'];
                    }
                }
                echo "<script>alert('Enviados mails');window.close();</script>";
            }
            
        }
        
    }

    public function generaStringNomina() {
        
        $strInicio="<?xml version='1.0' encoding='UTF-8' standalone='yes'?>
                <Document xmlns='urn:iso:std:iso:20022:tech:xsd:pain.001.001.03'>
                    <CstmrCdtTrfInitn>
                        <GrpHdr>
                            <MsgId>%msgid%</MsgId>
                            <CreDtTm>%fechaHora%</CreDtTm>
                            <NbOfTxs>%numtran%</NbOfTxs>
                            <CtrlSum>%tottran%</CtrlSum>
                            <InitgPty>
                                <Nm>%nomordenante%</Nm>
                                <Id>
                                    <OrgId>
                                        <Othr>
                                            <Id>%idordenante%</Id>
                                        </Othr>
                                    </OrgId>
                                </Id>
                            </InitgPty>
                        </GrpHdr>
                        <PmtInf>
                            <PmtInfId>%msgid%</PmtInfId>
                            <PmtMtd>TRF</PmtMtd>
                            <BtchBookg>true</BtchBookg>
                            <PmtTpInf>
                                <SvcLvl>
                                    <Cd>SEPA</Cd>
                                </SvcLvl>
                            </PmtTpInf>
                            <ReqdExctnDt>%fecha%</ReqdExctnDt>
                            <Dbtr>
                                <Nm>%nomordenante%</Nm>
                                <PstlAdr>
                                    <Ctry>ES</Ctry>
                                    <AdrLine>%dirordenante%</AdrLine>
                                    <AdrLine>%pobordenante%</AdrLine>
                                </PstlAdr>
                            </Dbtr>
                            <DbtrAcct>
                                <Id>
                                    <IBAN>%cuentaordenante%</IBAN>
                                </Id>
                            </DbtrAcct>
                            <DbtrAgt>
                                <FinInstnId>
                                    <BIC>BSCHESMMXXX</BIC>
                                </FinInstnId>
                            </DbtrAgt>";
                      //      <ChrgBr>SLEV</ChrgBr>";
        
        // bucle repeticion de lineas
        $tottran=0;$strCuerpo="";
        for ($i=0;$i<count($this->datos_facturas);$i++) {
            $strpago="       <CdtTrfTxInf>
                                <PmtId>
                                    <EndToEndId>NOTPROVIDED</EndToEndId>
                                </PmtId>
                                <PmtTpInf>
                                    <CtgyPurp>
                                        <Cd>SALA</Cd>
                                    </CtgyPurp>
                                </PmtTpInf>
                                <Amt>
                                    <InstdAmt Ccy='EUR'>%totfac%</InstdAmt>
                                </Amt>
                                <Cdtr>
                                    <Nm>%nomempresa%</Nm>
                                </Cdtr>
                                <CdtrAcct>
                                    <Id>
                                        <IBAN>%cuenta%</IBAN>
                                    </Id>
                                </CdtrAcct>
                                <RmtInf>
                                    <Ustrd>%concepto%</Ustrd>
                                </RmtInf>
                            </CdtTrfTxInf>";
            $strpago=str_replace('%totfac%',$this->datos_facturas[$i]['importe'],$strpago);
            $strpago=str_replace('%nomempresa%',$this->datos_facturas[$i]['nombre'],$strpago);
            $strpago=str_replace('%cuenta%',$this->datos_facturas[$i]['cuentaCorriente'],$strpago);
            $strpago=str_replace('%concepto%',$this->datos_facturas[$i]['descripcion'],$strpago);
            
            $tottran=$tottran+$this->datos_facturas[$i]['importe'];
            $strCuerpo=$strCuerpo.$strpago;
        }
        
        $strFin="          </PmtInf>
                    </CstmrCdtTrfInitn>
                </Document>";
        
        $strInicio=str_replace('%nomordenante%',$this->datos_ordenante['nombre'],$strInicio);
        $strInicio=str_replace('%dirordenante%',$this->datos_ordenante['direccion'],$strInicio);
        $strInicio=str_replace('%pobordenante%',$this->datos_ordenante['poblacion'],$strInicio);
        $strInicio=str_replace('%cuentaordenante%',$this->datos_ordenante['cuentaCorriente'],$strInicio);
        $strInicio=str_replace('%idordenante%',$this->datos_ordenante['cif'].'VID',$strInicio);
        
        $fechaHora=date("Y-m-d\TH:i:s");
        $fecha=date("Y-m-d");
        $msgid=$this->datos_ordenante['cif'].date("Ymd").date("Ymd\THis");
        $strInicio=str_replace('%msgid%',$msgid,$strInicio);
        $strInicio=str_replace('%fechaHora%',$fechaHora,$strInicio);
        $strInicio=str_replace('%fecha%',$fecha,$strInicio);
        $strInicio=str_replace('%numtran%',count($this->datos_facturas),$strInicio);
        $strInicio=str_replace('%tottran%',$tottran,$strInicio);
        
        return $strInicio.$strCuerpo.$strFin;
    }
    
    public function cargarPagosNominas($codEmpresa) {
        // datos empresa que factura
        $sql="select * from entidades where codEmpresa=:codEmpresa and tipoentidad='SELF'";
        $stmt = ($this->link)->prepare($sql);
        $stmt->bindValue(':codEmpresa',  $codEmpresa);
        if  ($stmt->execute()) {
            $cdatosEntidad= $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($cdatosEntidad)>0)
                    $this->datos_ordenante=$cdatosEntidad[0];
        }
        // CARGAR DATOS
        // datos nominas 
        $sql="select * from cpagosnominas where codEmpresa=:codEmpresa and generar=1";
        $stmt = ($this->link)->prepare($sql);
        $stmt->bindParam(':codEmpresa',  $codEmpresa);
        if  ($stmt->execute()) {
            $cfacturas= $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($cfacturas)<=0)  return;// no existe salgo
            else {
                $this->datos_facturas=$cfacturas;
                // actualizar campo fecha generacion
                $sql="update movimientos set fechaGeneracion=now() where codEmpresa=:codEmpresa and generar=1";
                $stmt = ($this->link)->prepare($sql);
                $stmt->bindParam(':codEmpresa',  $codEmpresa);
                $stmt->execute();
            }
            
        }
        
    }
    
    public function procesarPago($codEmpresa,$tipoOperacion) { 
        
        if ( $tipoOperacion=='PAGO') {
            $this->cargarPagosCobros($codEmpresa);
            $strPago=$this->generaStringPago();
           $nombre="PAGO_";
        } else if ( $tipoOperacion=='COBRO') {
            $this->cargarPagosCobros($codEmpresa);
            $strPago=$this->generaStringCobro();
            $nombre="COBRO_";
        } else if ( $tipoOperacion=='NOMINA'){
            $this->cargarPagosNominas($codEmpresa);
            $strPago=$this->generaStringNomina();
            $nombre="NOMINA_";
        } else return;
        
        $dirbase=$_SERVER['DOCUMENT_ROOT'].'/privado/';
        $dirbase=$dirbase.'uploads/';
        
        $nombre=$nombre.substr($this->datos_ordenante['nombre'],0,20)."_".date('dmYHis').'.xml';
        file_put_contents($dirbase.$nombre,$strPago);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header("Content-Length: ".filesize($dirbase.$nombre));
        header("Content-Disposition: attachment; filename=" .$nombre);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        ob_clean();
        flush();
        readfile($dirbase.$nombre);
        unlink($dirbase.$nombre);
        exit;
    }
}