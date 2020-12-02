<?php
/* Credentials to access database */
define ('HOST','localhost');
define ('USER','n2guser');
define ('PASS','password');
/* Route to Nagios performance files */
define ('RUTA','/var/nagios/dat');
/* Set language and load language file*/
define ('LANG','en');
include ('/usr/local/n2graph/cfg/'.LANG.'.php');
?>