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
   $dat[$key]=array('min'=>0,'max'=>0,'avg'=>0,'vacio'=>true,'ok'=>0,'warning'=>0,'critical'=>0,'unknown'=>0);
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
   $idbase = new mysqli(HOST, USER, PASS, 'n2graph');
   $idbase->set_charset('utf8mb4');
}
catch (mysqli_sql_exception $e){
   flog('n2graph_error',$e->getMessage());
   flog('n2graph_error',ERROROP);
   ferror(ERRORDB);
   exit;
}

$titulo=GTITNE;
$unidad=GUNIDAD;
$sql='select * from mser where idmser='.$num;
try {
   if (!$result=mysqli_query($idbase,$sql)){
      throw new Exception (ERRORRD,2);
   }
   if (mysqli_num_rows($result)==1){
      $row=mysqli_fetch_assoc($result);
      if (empty($row['service'])){
         $titulo=$row['host'].'-'.$row['metalias'];
         $tipo='h';
      }else{
         $titulo=$row['host'].'-'.$row['service'].'-'.$row['metalias'];
         $tipo='s';
      }
      if (!empty($row['unidad'])){$unidad=$row['unidad'];}
   }
   mysqli_free_result($result);
}
catch (Exception $e){
   if (isset($sql)){flog('n2graph_error',$sql);}
   flog('n2graph_error',$e->getMessage());
   ferror(ERRORDB);
   exit;
}

$sql='select substring(from_unixtime(fchmet),1,'.$lk.') as \'clave\',min(valor) as \'min\',max(valor) as \'max\',avg(valor) as \'avg\' from hmet where fchmet>='.$fchini.' and fchmet <'.$fchfin.' and idmser='.$num.' group by substring(from_unixtime(fchmet),1,'.$lk.')';
//flog('debug',$sql);
try {
   if (!$result=mysqli_query($idbase,$sql)){
      throw new Exception (ERRORRD,2);
   }
   while ($row=mysqli_fetch_assoc($result)){
      $dat[$row['clave']]['min']=$row['min'];
      $dat[$row['clave']]['max']=$row['max'];
      $dat[$row['clave']]['avg']=$row['avg'];
      $dat[$row['clave']]['vacio']=false;
   }
   mysqli_free_result($result);
}
catch (Exception $e){
   if (isset($sql)){flog('n2graph_error',$sql);}
   flog('n2graph_error',$e->getMessage());
   ferror(ERRORDB);
   exit;
}
$res=array();
foreach ($dat as $key=>$val){
   switch ($frac) {
   case 1:
      $res['rotulos'][]=substr($key,-5,5);
      $res['ejex']=GFOOT1.date('Y-m-d H:i',$fchini).GFOOTTO.date('Y-m-d H:i',$_SESSION['n2graph']['fchfin']);
      break;
   case 2:
      $res['rotulos'][]=substr($key,-4,4).'0';
      $res['ejex']=GFOOT2.substr(date('Y-m-d H:i',$fchini),0,15).GFOOTTO.substr(date('Y-m-d H:i',$_SESSION['n2graph']['fchfin']),0,15).'0';
      break;
   case 3:
      $res['rotulos'][]=substr($key,-2,2).':00';
      $res['ejex']=GFOOT3.date('Y-m-d H:00',$fchini).GFOOTTO.date('Y-m-d H:00',$_SESSION['n2graph']['fchfin']);
      break;
   case 4:
      $res['rotulos'][]=substr($key,-5,5);
      $res['ejex']=GFOOT4.date('Y-m-d',$fchini).GFOOTTO.date('Y-m-d',$_SESSION['n2graph']['fchfin']);
      break;
   case 5:
      $res['rotulos'][]=substr($key,2,5);
      $res['ejex']=GFOOT5.date('Y-m',$fchini).GFOOTTO.date('Y-m',$_SESSION['n2graph']['fchfin']);
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

$sql='select substring(from_unixtime(fchmet),1,'.$lk.') as \'clave\',estado,count(*) as \'cant\' from hmet where fchmet>='.$fchini.' and fchmet <'.$fchfin.' and idmser='.$num.' group by substring(from_unixtime(fchmet),1,'.$lk.'),estado';
//flog('debug',$sql);
try {
   if (!$result=mysqli_query($idbase,$sql)){
      throw new Exception (ERRORRD,2);
   }
   while ($row=mysqli_fetch_assoc($result)){
      switch ($row['estado']) {
         case 'OK':
            $dat[$row['clave']]['ok']=1;
            break;
         case 'WARNING':
            $dat[$row['clave']]['warning']=2;
            break;
         case 'CRITICAL':
            $dat[$row['clave']]['critical']=4;
            break;
         case 'UNKNOWN':
            $dat[$row['clave']]['unknown']=8;
            break;
         case 'UP':
            $dat[$row['clave']]['ok']=1;
            break;
         case 'DOWN':
            $dat[$row['clave']]['critical']=2;
            break;
         case 'UNREACHABLE':
            $dat[$row['clave']]['unknown']=4;
            break;
      }
   }
   mysqli_free_result($result);
}
catch (Exception $e){
   if (isset($sql)){flog('n2graph_error',$sql);}
   flog('n2graph_error',$e->getMessage());
   ferror(ERRORDB);
   exit;
}
foreach ($dat as $key=>$val){
   if ($tipo=='s'){
      $suma=$val['ok']+$val['warning']+$val['critical']+$val['unknown'];
      switch ($suma){
         case 1:
            $res['ok'][]=4;$res['w'][]=0;$res['c'][]=0;$res['u'][]=0;
            break;
         case 2:
            $res['ok'][]=0;$res['w'][]=3;$res['c'][]=0;$res['u'][]=0;
            break;
         case 4:
            $res['ok'][]=0;$res['w'][]=0;$res['c'][]=2;$res['u'][]=0;
            break;
         case 8:
            $res['ok'][]=0;$res['w'][]=0;$res['c'][]=0;$res['u'][]=1;
            break;
         case 3:
            $res['ok'][]=1;$res['w'][]=3;$res['c'][]=0;$res['u'][]=0;
            break;
         case 5:
            $res['ok'][]=2;$res['w'][]=0;$res['c'][]=2;$res['u'][]=0;
            break;
         case 6:
            $res['ok'][]=0;$res['w'][]=1;$res['c'][]=2;$res['u'][]=0;
            break;
         case 7:
            $res['ok'][]=1;$res['w'][]=1;$res['c'][]=2;$res['u'][]=0;
            break;
         case 9:
            $res['ok'][]=3;$res['w'][]=0;$res['c'][]=0;$res['u'][]=1;
            break;
         case 10:
            $res['ok'][]=0;$res['w'][]=2;$res['c'][]=0;$res['u'][]=1;
            break;
         case 11:
            $res['ok'][]=1;$res['w'][]=2;$res['c'][]=0;$res['u'][]=1;
            break;
         case 12:
            $res['ok'][]=0;$res['w'][]=0;$res['c'][]=1;$res['u'][]=1;
            break;
         case 13:
            $res['ok'][]=2;$res['w'][]=0;$res['c'][]=1;$res['u'][]=1;
            break;
         case 14:
            $res['ok'][]=0;$res['w'][]=1;$res['c'][]=1;$res['u'][]=1;
            break;
         case 15:
            $res['ok'][]=1;$res['w'][]=1;$res['c'][]=1;$res['u'][]=1;
            break;
         default:
            $res['ok'][]=0;$res['w'][]=0;$res['c'][]=0;$res['u'][]=0;
            break;
      }
   }else{
      $suma=$val['ok']+$val['critical']+$val['unknown'];
      switch ($suma){
         case 1:
            $res['ok'][]=3;$res['w'][]=0;$res['c'][]=0;$res['u'][]=0;
            break;
         case 2:
            $res['ok'][]=0;$res['w'][]=0;$res['c'][]=2;$res['u'][]=0;
            break;
         case 4:
            $res['ok'][]=0;$res['w'][]=0;$res['c'][]=0;$res['u'][]=1;
            break;
         case 3:
            $res['ok'][]=1;$res['w'][]=0;$res['c'][]=2;$res['u'][]=0;
            break;
         case 5:
            $res['ok'][]=2;$res['w'][]=0;$res['c'][]=0;$res['u'][]=1;
            break;
         case 6:
            $res['ok'][]=0;$res['w'][]=0;$res['c'][]=2;$res['u'][]=1;
            break;
         case 7:
            $res['ok'][]=1;$res['w'][]=0;$res['c'][]=2;$res['u'][]=1;
            break;
         default:
            $res['ok'][]=0;$res['w'][]=0;$res['c'][]=0;$res['u'][]=0;
            break;
      }
   }
}
$res['tipo']=$tipo;
if ($tipo=='s'){
   $res['to']='Ok';
   $res['tw']='Warning';
   $res['tc']='Critical';
   $res['tu']='Unknown';
}else{
   $res['to']='Up';
   $res['tw']='';
   $res['tc']='Down';
   $res['tu']='Unreachable';
}
$res['tmin']=GTITMIN;
$res['tmax']=GTITMAX;
$res['tavg']=GTITAVG;
$res['stat']=GSTATUS;
$res['unidad']=$unidad;
$res['titulo']=$titulo;
//flog('debug2',print_r($dat,true));

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
   $res['unidad']='';
   $res['titulo']=$ep;
   $res['tipo']='h';
   $res['ok'][]=0;
   $res['w'][]=0;
   $res['c'][]=0;
   $res['u'][]=0;
   $res['to']=' ';
   $res['tw']=' ';
   $res['tc']=' ';
   $res['tu']=' ';
   $res['tmin']=' ';
   $res['tmax']=' ';
   $res['tavg']=' ';
   $res['stat']=' ';
   echo json_encode($res);
}
?>
