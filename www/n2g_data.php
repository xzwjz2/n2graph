<?php
session_start();
//flog('debug',print_r($_REQUEST,true));
if (!isset($_SESSION['n2graph']['frac'])){
   $_SESSION['n2graph']['frac']=1;
   $_SESSION['n2graph']['fchfin']=strtotime(date('Y-m-d H:i:00'));
   $_SESSION['n2graph']['num']=0;
}
$frac=$_SESSION['n2graph']['frac'];$fchfin=$_SESSION['n2graph']['fchfin'];$num=$_SESSION['n2graph']['num'];
if (isset($_REQUEST['botmas'])){
   $frac=($frac<=1?1:($frac-1));
   $_SESSION['n2graph']['frac']=$frac;
}elseif (isset($_REQUEST['botmenos'])){
   $frac=($frac>=5?5:($frac+1));
   $_SESSION['n2graph']['frac']=$frac;
}elseif (isset($_REQUEST['botfin'])){
   $fchfin=strtotime(date('Y-m-d H:i:00'));
   $_SESSION['n2graph']['fchfin']=$fchfin;
}elseif (isset($_REQUEST['botatras'])){
   switch ($frac) {
      case 5:
         $fchfin=strtotime(date('Y-m-01 00:00:00',$fchfin).' -12 months');
         break;
      case 4:
         $fchfin=strtotime(date('Y-m-d 00:00:00',$fchfin).' -24 days');
         break;
      case 3:
         $fchfin=strtotime(date('Y-m-d H:00:00',$fchfin).' -24 hours');
         break;
      case 2:
         $fchfin=strtotime(substr(date('Y-m-d H:i',$fchfin),0,15).'0:00 -300 minutes');
         break;
      case 1:
         $fchfin=strtotime(date('Y-m-d H:i:00',$fchfin).' -30 minutes');
         break;
   }
   $_SESSION['n2graph']['fchfin']=$fchfin;
}elseif (isset($_REQUEST['botadelante'])){
   switch ($frac) {
      case 5:
         $fchfin=strtotime(date('Y-m-01 00:00:00',$fchfin).' +12 months');
         break;
      case 4:
         $fchfin=strtotime(date('Y-m-d 00:00:00',$fchfin).' +24 days');
         break;
      case 3:
         $fchfin=strtotime(date('Y-m-d H:00:00',$fchfin).' +24 hours');
         break;
      case 2:
         $fchfin=strtotime(substr(date('Y-m-d H:i',$fchfin),0,15).'0:00 +300 minutes');
         break;
      case 1:
         $fchfin=strtotime(date('Y-m-d H:i:00',$fchfin).' +30 minutes');
         break;
   }
   if ($fchfin>strtotime(date('Y-m-d H:i:00'))){$fchfin=strtotime(date('Y-m-d H:i:00'));}
   $_SESSION['n2graph']['fchfin']=$fchfin;

}else{
   $num=$_REQUEST['num'];
   $_SESSION['n2graph']['num']=$_REQUEST['num'];
}
switch ($frac) {
   case 5: //1 mes
      $fchini=strtotime(date('Y-m-01 00:00:00',$fchfin).' -24 months');
      $lk=7;$cs=1;$mdiv=25;
      break;
   case 4: //1 dia
      $fchini=strtotime(date('Y-m-d',$fchfin).' -48 days');
      $lk=10;$cs=1;$mdiv=49;
      break;
   case 3: //1 hora
      $fchini=strtotime(date('Y-m-d H:00:00',$fchfin).' -48 hours');
      $lk=13;$cs=3600;$mdiv=49;
      break;
   case 2: //10 minutos
      $fchini=strtotime(substr(date('Y-m-d H:i',$fchfin),0,15).'0:00 -600 minutes');
      $lk=15;$cs=600;$mdiv=61;
      break;
   case 1: //1 minuto
      $fchini=strtotime(date('Y-m-d H:i:00',$fchfin).' -60 minutes');
      $lk=16;$cs=60;$mdiv=61;
      break;
}

$dat=array();
$fchfin=$fchini;
for ($ii=0; $ii<$mdiv;$ii++){
   $key=substr(date('Y-m-d H:i',$fchfin),0,$lk);
   $dat[$key]=array('min'=>0,'max'=>0,'avg'=>0,'vacio'=>true);
   switch ($frac) {
      case 5:
         $fchfin=strtotime(date('Y-m-d',$fchfin).' +1 month');
         break;
      case 4:
         $fchfin=strtotime(date('Y-m-d',$fchfin).' +1 day');
         break;
      default:
         $fchfin+=$cs;
         break;
   }
}
include ('../cfg/config.php');
mysqli_report(MYSQLI_REPORT_STRICT); 
try{
   $idbase = new mysqli($host, $user, $pass, 'n2graph');
   $idbase->set_charset('utf8mb4');
}
catch (mysqli_sql_exception $e){
   flog('n2graph_error',$e->getMessage());
   flog('n2graph_error','Error al abrir la base de datos');
   ferror('Error en la base de datos');
   exit;
}   


$titulo='SERVICIO NO EXISTE';
$sql='select * from mser where idmser='.$num;
try {
   if (!$result=mysqli_query($idbase,$sql)){
      throw new Exception ('Error en la lectura de datos',2);
   }
   if (mysqli_num_rows($result)==1){
      $row=mysqli_fetch_assoc($result);
      $titulo=$row['host'].'-'.$row['service'].'-'.$row['metrica'];
   }
//   while ($row=mysqli_fetch_assoc($result)){
//      $dat[$row['clave']]=array('min'=>$row['min'],'max'=>$row['max'],'avg'=>$row['avg'],'vacio'=>false);
//   }
   mysqli_free_result($result);
}
catch (Exception $e){
   if (isset($sql)){flog('n2graph_error',$sql);}
   flog('n2graph_error',$e->getMessage());
   ferror('Error en la base de datos');
   exit;
}

$sql='select substring(from_unixtime(fchmet),1,'.$lk.') as \'clave\',min(valor) as \'min\',max(valor) as \'max\',avg(valor) as \'avg\' from hmet where fchmet>='.$fchini.' and fchmet <'.$fchfin.' and idmser='.$num.' group by substring(from_unixtime(fchmet),1,'.$lk.')';
//flog('debug',$sql);
try {
   if (!$result=mysqli_query($idbase,$sql)){
      throw new Exception ('Error en la lectura de datos',2);
   }
   while ($row=mysqli_fetch_assoc($result)){
      $dat[$row['clave']]=array('min'=>$row['min'],'max'=>$row['max'],'avg'=>$row['avg'],'vacio'=>false);
   }
   mysqli_free_result($result);
}
catch (Exception $e){
   if (isset($sql)){flog('n2graph_error',$sql);}
   flog('n2graph_error',$e->getMessage());
   ferror('Error en la base de datos');
   exit;
}
$res=array();
foreach ($dat as $key=>$val){
   switch ($frac) {
   case 1:
      $res['rotulos'][]=substr($key,-5,5);
      $res['ejex']='Cada 1 minuto, desde: '.date('Y-m-d H:i',$fchini).'  a  '.date('Y-m-d H:i',$_SESSION['n2graph']['fchfin']);
      break;
   case 2:
     $res['rotulos'][]=substr($key,-4,4).'0';
     $res['ejex']='Cada 10 minutos, desde: '.substr(date('Y-m-d H:i',$fchini),0,15).'0  a  '.substr(date('Y-m-d H:i',$_SESSION['n2graph']['fchfin']),0,15).'0';
     break;
   case 3:
      $res['rotulos'][]=substr($key,-2,2).':00';
      $res['ejex']='Cada 1 hora, desde: '.date('Y-m-d H:00',$fchini).'  a  '.date('Y-m-d H:00',$_SESSION['n2graph']['fchfin']);
      break;
   case 4:
      $res['rotulos'][]=substr($key,-5,5);
      $res['ejex']='Cada 1 día, desde: '.date('Y-m-d',$fchini).'  a  '.date('Y-m-d',$_SESSION['n2graph']['fchfin']);
      break;
   case 5:
      $res['rotulos'][]=substr($key,2,5);
      $res['ejex']='Cada 1 mes, desde: '.date('Y-m',$fchini).'  a  '.date('Y-m',$_SESSION['n2graph']['fchfin']);
      break;   
   }
   if ($val['vacio']==false){
      $res['avg'][]=$val['avg'];
      $res['min'][]=$val['min'];
      $res['max'][]=$val['max'];
   }else{
      $res['avg'][]='NaN';
      $res['min'][]='NaN';
      $res['max'][]='NaN';
   }   
}
$res['titulo']=$titulo;
echo json_encode($res);


function flog($slog,$tlog){$log='/var/nagios/'.$slog.'_'.date('Y_m_d').'.log';
error_log(PHP_EOL.date('Y-m-d H:i:s').';'.$tlog,3,$log);}
function ferror($ep){
   $res=array();
   $res['rotulos'][]=1;
   $res['ejex']=' ';
   $res['avg'][]=1;
   $res['min'][]=1;
   $res['max'][]=1;
   $res['titulo']=$ep;
   echo json_encode($res);
}
?>