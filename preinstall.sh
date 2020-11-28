#!/bin/bash
# Script to create folders for N2Graph
# Must be executed with root privileges
mkdir /usr/local/n2graph
mkdir /var/nagios
mkdir /var/nagios/dat


cp apache2/* /etc/apache2/sites-available/

a2ensite n2graph_ap2.conf
systemctl reload apache2

nano dbf/create_n2graph.sql
mysql <dbf/create_n2graph.sql

cp cfg/config.php /usr/local/n2graph/cfg
cp www/index.php /usr/local/n2graph/www

nano /usr/local/n2graph/cfg/config.php

find /usr/local/n2graph -exec chmod 775 {} \;
find /usr/local/n2graph -exec chown nagios {} \;
find /usr/local/n2graph -exec chgrp nagios {} \;

find /var/nagios -exec chmod 777 {} \;
find /var/nagios -exec chown nagios {} \;
find /var/nagios -exec chgrp nagios {} \;
