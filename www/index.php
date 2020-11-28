<?php
/*****************************************************
* N2GRAPH: Gráficos de Nagios
*****************************************************/
//Establecimiento de sesion
session_start();
//Establezco nombre del programa
define('MODULO','n2graph');

//Abro conexión a la base de datos
$idbase=fabremy('n2graph');

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
      $sql='select * from mser order by host,service,metrica';
      if (!$result=mysqli_query($idbase,$sql)){
         throw new Exception ('Error en la lectura de datos',2);
      }
      while ($row=mysqli_fetch_assoc($result)){
         if (!array_key_exists($row['host'],$hosts)){$hosts[$row['host']]=1;$row['mh']=true;}else{$hosts[$row['host']]++;$row['mh']=false;}
         if (!array_key_exists($row['host'].$row['service'],$services)){$services[$row['host'].$row['service']]=1;$row['ms']=true;}else{$services[$row['host'].$row['service']]++; $row['ms']=false;}   
         $metricas[]=$row;
      }
      mysqli_free_result($result);
   }
   catch (Exception $e){
      if (isset($sql)){flog('n2graph_error',$sql);}
      flog('n2graph_error',$e->getMessage());
      ferror('Hubo errores al buscar los datos');
   }
   include ('frm/n2graph_pri.htm');
   //print_r($datos);
   //print_r($dg);
}
/* Abre la conexión a la base de datos */
function fabremy($base){
   include ('../cfg/config.php');
mysqli_report(MYSQLI_REPORT_STRICT); 
try{
$sqltemp = new mysqli($host, $user, $pass, $base);
$sqltemp->set_charset('utf8mb4');
return $sqltemp;
}
catch (mysqli_sql_exception $e){
flog('n2graph_error',$e->getMessage());
flog('n2graph_error','Error al abrir la base de datos');
die('Ha ocurrido un error grave y no puedo continuar con la ejecución del programa');
}   
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
function ferror($ep){$_SESSION[MODULO]['msgerr']=$ep;}
?>