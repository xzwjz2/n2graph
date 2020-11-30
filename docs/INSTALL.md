# Install Instructions

## Prerequisites

1. Nagios installed.
   This tool was tested with Nagios Core version 4. I's supposed to work with version 3 also.
   
2. LAMP stack.
   You need Apache (it's not mandatory, you can use any other server, but you must know how to configure it), a mysql type database (MariaDB or Oracle Mysql) and PHP installed.
   
## Assumptions

1. These instructions are based on an installation on a Debian 10, with Nagios in folder /usr/local/nagios. If you have something different, maybe you'll have to use some different paths or file names. User and group are 'nagios' and 'nagios'.

## Steps.

1. Create folders and give access. Use superuser (root) privileges.
```
sudo su
mkdir -m 777 /var/nagios
mkdir -m 777 /var/nagios/dat
find /var/nagios -exec chown nagios {} \;
find /var/nagios -exec chgrp nagios {} \;
```

   
