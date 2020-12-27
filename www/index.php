<?php
/*****************************************************
* N2GRAPH: Gráficos de Nagios
*****************************************************/
//Establecimiento de sesion
session_start();
//Establezco nombre del programa
define('MODULO','n2graph');
include ('../cfg/config.php');
//Abro conexión a la base de datos
mysqli_report(MYSQLI_REPORT_STRICT); 
try{
   $idbase = new mysqli(HOST, USER, PASS, 'n2graph');
   $idbase->set_charset('utf8mb4');
}
catch (mysqli_sql_exception $e){
   flog('n2graph_error',$e->getMessage());
   flog('n2graph_error',ERROROP);
   ferror(ERRORDB);
   exit;
}   

//limpiar requerimientos por el code-injection
limpiarequest();

//Ver de dónde viene y que acción corresponde
$ultpan='*';$ultpes='*';
fverdedonde($ultpan,$ultpes);
//Verifico el token
if (isset($_SESSION[MODULO]['ftoken']) and isset($_REQUEST['ftoken'])
    and $_SESSION[MODULO]['ftoken']==$_REQUEST['ftoken']){
   //token OK, veo de qué pantalla viene
   if (isset($_REQUEST['botsalir'])){
      //boton de salir,lo mando al menú principal
      unset($_SESSION[MODULO]);
      header('location: ./');
      exit;
   }elseif ($ultpan=='n2graph_pri'){
      if (isset($_REQUEST['botfiltrar'])){
         n2graph_pri('1');
         exit;
      }
   }
}
n2graph_pri('0',$idbase);
exit;
/***************************************************
* N2GRAPH_PRI: 
***************************************************/
function n2graph_pri($ep,&$idbase){
   $_SESSION[MODULO]['ultpan']='n2graph_pri';
   if ($ep=='0'){
      //primera vez que ingresa, inicializo el token
      $_REQUEST['ftoken']=md5(date('U'));
      $_SESSION[MODULO]['ftoken']=$_REQUEST['ftoken'];
   }
   $metricas=array();
   $hosts=array();
   $services=array();
   try {
      $sql='select * from mser order by host,service,metalias';
      if (!$result=mysqli_query($idbase,$sql)){
         throw new Exception (ERRORRD,2);
      }
      while ($row=mysqli_fetch_assoc($result)){
         if (!array_key_exists($row['host'],$hosts)){$hosts[$row['host']]=1;$row['mh']=true;}else{$hosts[$row['host']]++;$row['mh']=false;}
         if (!array_key_exists($row['host'].$row['service'],$services)){$services[$row['host'].$row['service']]=1;$row['ms']=true;}else{$services[$row['host'].$row['service']]++; $row['ms']=false;}
         $row['cest']='#000000';   
         $metricas[]=$row;
      }
      mysqli_free_result($result);
   }
   catch (Exception $e){
      if (isset($sql)){flog('n2graph_error',$sql);}
      flog('n2graph_error',$e->getMessage());
      ferror(ERRORRD);
      exit;
   }
   //Read status
   $nomarc=STTSPATH.'/status.dat';
   if (file_exists($nomarc)){
      $fh=fopen($nomarc,'r');
      if ($fh!==false){
         $hoststatus=false;
         $servicestatus=false;
         $ac=count($metricas);
         while ($lin=fgets($fh)){
            if (substr($lin,0,10)=='hoststatus'){
               $hoststatus=true;$servicestatus=false;$service='';
            }elseif (substr($lin,0,13)=='servicestatus'){
               $servicestatus=true;$hoststatus=false;
            }
            if (($pos=strpos($lin,'host_name='))!==false){
               $host=trim(substr($lin,$pos+10,100));
            }elseif (($pos=strpos($lin,'service_description='))!==false){
               $service=trim(substr($lin,$pos+20,100));
            }elseif (($pos=strpos($lin,'current_state='))!==false){
               $estado=substr($lin,$pos+14,1);
               foreach($metricas as $key=>$val){
                  if ($val['host']==$host and $val['service']==$service){
                     if ($hoststatus){
                        switch ($estado){
                           case '0':
                              $metricas[$key]['cest']='#00ff00';
                              break;
                           case '1': 
                              $metricas[$key]['cest']='#ff0000';
                              break;
                        }      
                     }elseif($servicestatus){
                        switch ($estado){
                           case '0':
                              $metricas[$key]['cest']='#00ff00';
                              break;
                           case '1': 
                              $metricas[$key]['cest']='#ffff00';
                              break;
                           case '2': 
                              $metricas[$key]['cest']='#ff0000';
                              break;
                        }      
                     }
                     break;
                  }
               }
               $servicestatus=false;$hoststatus=false;
            }
         }
         fclose($fh);
      }
   }
   include ('frm/n2graph_pri.htm');
}


function flog($slog,$tlog){$log='/var/nagios/'.$slog.'_'.date('Y_m_d').'.log';
error_log(PHP_EOL.date('Y-m-d H:i:s').';'.$tlog,3,$log);}

function limpiarequest(){
foreach ($_REQUEST as $key => $value){
if (is_array($value)){
foreach ($value as $key2=>$value2){
if (is_array($value2)){
$_REQUEST[$key][$key2]=array_map('htmlspecialchars',$value2);
}else{
$_REQUEST[$key][$key2]=htmlspecialchars($_REQUEST[$key][$key2],ENT_QUOTES);
}
}
}else{
$_REQUEST[$key]=htmlspecialchars($_REQUEST[$key],ENT_QUOTES);
}
}
}
function fverdedonde(&$pan,&$pes){
if (isset($_SESSION[MODULO]['ultpan'])){
$pan=$_SESSION[MODULO]['ultpan'];
}else{
$pan='*';
$_SESSION[MODULO]['ultpan']='*';
}
if (isset($_SESSION[MODULO]['ultpes'])){
$pes=$_SESSION[MODULO]['ultpes'];
}else{
$pes='*';
$_SESSION[MODULO]['ultpes']='*';
}
}
function ferror($ep){include ('frm/error.htm');}
?>