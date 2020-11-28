<?php
/* Procesa los registros del log de Nagios */
//Busca los archivos sin procesar
include ('/usr/local/n2graph/cfg/config.php');
$archivos=ver_dir($ruta);
mysqli_report(MYSQLI_REPORT_STRICT); 
try{
   $idbase = new mysqli($host, $user, $pass, 'n2graph');
   $idbase->set_charset('utf8mb4');
}
catch (mysqli_sql_exception $e){
   flog('n2gproc_error',$e->getMessage());
   flog('n2gproc_error','Error al abrir la base de datos');
   exit;
}   

foreach ($archivos as $key=>$val){
   $hn=fopen($ruta.'/'.$val,'r');
   while ($lin=fgetcsv($hn,0,'|')){
      busca_met($idbase,$lin,'chktime',$lin[5]);
      //Busco las otras métricas
      if (!empty($lin[8])){
         $met=explode(' ',trim($lin[8]));
         foreach($met as $key2=>$val2){
            $nommet=substr($val2,0,stripos($val2,'='));
            $resmet=substr($val2,stripos($val2,'=')+1,1000);
            $valores=explode(';',$resmet);
            if(substr_count($valores[0],',')>0){$valores[0]=str_replace(',','.',$valores[0]);}
            busca_met($idbase,$lin,$nommet,filter_var($valores[0],FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION));
         }
      }
   }
   fclose($hn);
   rename($ruta.'/'.$val,$ruta.'/'.$val.'.procesado');
   flog('n2gproc','Processed:'.$val);
}
/* End of proc */

/* Function to process each metrics */
function busca_met(&$idbase,&$lin,$metrica,$valor){
   try{
      $sql='select idmser from mser where host=\''.$lin[1].'\' and service=\''.$lin[2].'\' and metrica=\''.$metrica.'\'';
      if (!$result=mysqli_query($idbase,$sql)){
         throw new Exception ('Error en lectura de mser',2);
      }
      if (mysqli_num_rows($result)==0){
         //la métrica no existe, la agrego
         mysqli_free_result($result);
         $sql='insert into mser (host,service,metrica) values (\''.$lin[1].'\',\''.$lin[2].'\',\''.$metrica.'\')';
         if (!$result2=mysqli_query($idbase,$sql)){
            throw new Exception ('Error en escritura de mser',2);
         }
         $idmser=mysqli_insert_id($idbase);
      }else{
         $row=mysqli_fetch_assoc($result);
         $idmser=$row['idmser'];
         mysqli_free_result($result);
      }
      $sql='select * from hmet where idmser='.$idmser.' and fchmet='.$lin[0];
      if (!$result2=mysqli_query($idbase,$sql)){
         throw new Exception ('Error en lectura de hmet',2);
      }
      if (mysqli_num_rows($result2)==0){
         mysqli_free_result($result2);
         $sql='insert into hmet (idmser,fchmet,estado,nroint,valor) values ('.$idmser.','.$lin[0].',\''.$lin[3].'\','.$lin[4].','.$valor.')';
         if (!$result2=mysqli_query($idbase,$sql)){
            throw new Exception ('Error en escritura de hmet',2);
         }
      }else{
         mysqli_free_result($result2);
      }   
   }
   catch (Exception $e){
      if (isset($sql)){ flog('n2gproc_error',$sql);}
      flog('n2gproc_error',$e->getMessage());
   }
}

/* Function to look for files in folder */
function ver_dir(&$ruta){
   // Se comprueba que realmente sea la ruta de un directorio
   $arc=array();
   if (is_dir($ruta)){
      // Abre un gestor de directorios para la ruta indicada
      $gestor = opendir($ruta);
      // Recorre todos los elementos del directorio
      while (($archivo = readdir($gestor)) !== false)  {
         if (substr_compare ($archivo , '.dat' , -4, 4, true)===0){
            $arc[]=$archivo;
         }
      }
      // Cierra el gestor de directorios
      closedir($gestor);
   }
   return $arc;
}

/* Function to log errors and messages */
function flog($slog,$tlog){
   $log='/var/nagios/'.$slog.'_'.date('Y_m_d').'.log';
   error_log(PHP_EOL.date('Y-m-d H:i:s').';'.$tlog,3,$log);
}
?>