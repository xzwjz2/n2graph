# Install Instructions

## Prerequisites

1. Nagios installed.
   This tool was tested with Nagios Core version 4. I's supposed to work with version 3 also.
   
2. LAMP stack.
   You need Apache (it's not mandatory, you can use any other server, but you must know how to configure it), a mysql type database (MariaDB or Oracle Mysql) and PHP installed.
   
## Assumptions

1. These instructions are based on an installation on a Debian 10, with Nagios in folder /usr/local/nagios. If you have something different, maybe you'll have to use some different paths or file names. User and group are 'nagios' and 'nagios'.

## Steps

1. Create folders and give access. Use superuser (root) privileges.
```
sudo su
mkdir -m 777 /var/nagios
mkdir -m 777 /var/nagios/dat
find /var/nagios -exec chown nagios {} \;
find /var/nagios -exec chgrp nagios {} \;
```
2. Modify Nagios configuration files. First commands file:
```
nano /usr/local/nagios/etc/objects/commands.cfg
```
Locate the performance data commands and update (or add if they not exists) them. We'll setup simple commands to move and rename the performance log files (Nagios will create them again when new data is generated):
```
define command {
    command_name    process-host-perfdata
    command_line    /bin/mv /var/nagios/host-perfdata.log /var/nagios/dat/$DATE$-$TIME$-host.dat
}
define command {
    command_name    process-service-perfdata
    command_line    /bin/mv /var/nagios/service-perfdata.log /var/nagios/dat/$DATE$-$TIME$-service.dat
}
```
Then setup nagios.cfg
```
nano /usr/local/nagios/etc/nagios.cfg
```
Update the following params (usually they are preceded with comments, I don't show them here):
```
process_performance_data=1

#host_perfdata_command=process-host-perfdata
#service_perfdata_command=process-service-perfdata

host_perfdata_file=/var/nagios/host-perfdata.log
service_perfdata_file=/var/nagios/service-perfdata.log

host_perfdata_file_template=$LASTHOSTCHECK$|$HOSTNAME$||$HOSTSTATE$|$HOSTATTEMPT$|$HOSTEXECUTIONTIME$|$HOSTLATENCY$|$HOSTOUTPUT$|$HOSTPERFDATA$
service_perfdata_file_template=$LASTSERVICECHECK$|$HOSTNAME$|$SERVICEDESC$|$SERVICESTATE$|$SERVICEATTEMPT$|$SERVICEEXECUTIONTIME$|$SERVICELATENCY$|$SERVICEOUTPUT$|$SERVICEPERFDATA$

host_perfdata_file_mode=a
service_perfdata_file_mode=a
```
These params will setup Nagios to write performance data to disk files (in folders we previously created). Now we have to instruct Nagios to do some processing to those files and how often. You should adjust the frequency to your needs. Since usually Nagios minimum check period is 1 minute, it has no sense to setup processing frequency more than that. In this example I set it up to 10 minutes:
```
host_perfdata_file_processing_interval=600
service_perfdata_file_processing_interval=600

host_perfdata_file_processing_command=process-host-perfdata
service_perfdata_file_processing_command=process-service-perfdata
```
Finally, reload Nagios:
```
systemctl reload nagios.service
```
3. Download N2Graph package to a temporary location (if you wish to install a different version, replace 2.0.0)
```
cd /tmp
wget  https://github.com/xzwjz2/n2graph/archive/v2.0.0.tar.gz
tar -xvf v2.0.0.tar.gz
cd n2graph-v2.0.0
```
4. We have to customize some files before to install them. First, we setup the user and password to access the database:
```
nano dbf/create_n2graph.sql
```
Look for at the end of the file the line `CREATE USER IF NOT EXISTS 'n2guser'@'localhost' IDENTIFIED BY 'password';` and setup the password (please use a "strong" password). You can also change the name of the user if you like. Now put the same user and password information in php code:
```
nano cfg/config.php
```
Replace the content of constants `USER` and `PASS` with appropiate information. Also select the language to use in legends and messages setting the constant `LANG`. Currently only two languages are supported: English (`en`) and Spanish (`es`).

Now, create the database:

`mysql <dbf/create_n2graph.sql` (maybe you should use `mysql -u root -p <dbf/create_n2graph.sql`, also maybe you should use a user other than `root`, depending on how you configured your mysql). 

Now install files:
```
mkdir -m 755 /usr/local/n2graph
cp -r cfg /usr/local/n2graph
cp -r www /usr/local/n2graph
cp n2gproc.php /usr/local/n2graph
find /usr/local/n2graph -exec chmod 755 {} \;
find /usr/local/n2graph -exec chown nagios {} \;
find /usr/local/n2graph -exec chgrp nagios {} \;
```
5. Setup a cron task (for nagios user). This task will process performance data files and write that information to mysql database.
```
crontab -u nagios -e
```
and add the line:
```
*/10 * * * * /usr/bin/php -f /usr/local/n2graph/n2gproc.php >/dev/null 2>&1
```
In this case, files will be processed every 10 minutes (as we set up the same for Nagios). It's no mandatory to be the same period, you can setup cron to process to a different period, but it has no much sense. 

6. Setup Apache. First customize config file:
```
nano apache2/n2graph_ap2.conf
```
You can setup TZ data according to your location. In this example I use Basic Authorizacion access with the same credentials as Nagios, but you can use a different credential's file or even disable access control, updating or deleting lines:
 ```
AuthName "N2Graph Access"
AuthType Basic
AuthUserFile /usr/local/nagios/etc/htpasswd.users
Require valid-user
```
Install and enable site:
```
cp apache2/n2graph_ap2.conf /etc/apache2/sites-available
a2ensite n2graph_ap2.conf
systemctl reload apache2
```
7. Garbage clean-up. 
Many files are left behind during normal operation:
```
/var/nagios/dat/AAA-MM-DD-HH:MM:SS-service.dat.procesado
/var/nagios/dat/AAA-MM-DD-HH:MM:SS-host.dat.procesado
/var/nagios/XXXXXX_AAAA_MM_DD.log
/var/nagios/XXXXXX_error_AAAA_MM_DD.log
```
You should setup your own cleaning process of these files according with your own retention period criteria. You can use cron tasks or logrotate tool. For example, you can set it up to:
```
crontab -u root -e
```
And add following lines:
```
1 0 * * * /usr/bin/find /var/nagios -type f -name "*.log" -mtime +7 -exec rm -f {} \;
2 0 * * * /usr/bin/find /var/nagios/dat -type f -name "*.procesado" -mtime +0 -exec rm -f {} \;
```
## Troubleshooting.

You can find some guidelines [here](TROUBLESHOOT.md), or you can write me or load a bug case for any doubt or problem you have.
























