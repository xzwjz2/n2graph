<?php
session_start();
include ('../cfg/config.php');
mysqli_report(MYSQLI_REPORT_STRICT); 
try{
   $idbase = new mysqli(HOST, USER, PASS, 'n2graph');
   $idbase->set_charset('utf8mb4');
}
catch (mysqli_sql_exception $e){
   flog('n2gconfig_error',$e->getMessage());
   flog('n2gconfig_error',ERROROP);
   ferror(ERRORDB);
   exit;
}   
if (isset($_REQUEST['tipo'])){
   if ($_REQUEST['tipo']=='metalias'){
      $sql='update mser set metalias=\''.$_REQUEST['valor'].'\' where idmser='.$_REQUEST['num'];
   }else{
      $sql='update mser set unidad=\''.$_REQUEST['valor'].'\' where idmser='.$_REQUEST['num'];
   }
   try {
      if (!$result=mysqli_query($idbase,$sql)){
         throw new Exception (ERRORWD,2);
      }
      mysqli_free_result($result);
   }
   catch (Exception $e){
      if (isset($sql)){flog('n2gconfig_error',$sql);}
      flog('n2gconfig_error',$e->getMessage());
      ferror(ERRORDB);
      exit;
   }
}

function flog($slog,$tlog){$log='/var/nagios/'.$slog.'_'.date('Y_m_d').'.log';
error_log(PHP_EOL.date('Y-m-d H:i:s').';'.$tlog,3,$log);}

function ferror($ep){
   http_response_code(401);
   echo '<h3 class="w3-text-red">',$ep,'</h3>';
   exit;
}
?>
