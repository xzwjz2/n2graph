# UPDATE INSTRUCTIONS

## From versions previous to 2.0.0

This instructions apply if you have a running installation previous to versions 2.0.0

1. Stop background process:
```
sudo su
crontab -u nagios -e
```
and comment (add a # at the beggining) the line: `# */10 * * * * /usr/bin/php -f /usr/local/n2graph/n2gproc.php >/dev/null 2>&1`

2. Download new version package to a temporary location
```
cd /tmp
wget  https://github.com/xzwjz2/n2graph/archive/v2.0.0.tar.gz
tar -xvf v2.0.0.tar.gz
cd n2graph-v2.0.0
```
3. Update database (same script than creation):

`mysql <dbf/create_n2graph.sql` or `mysql -u <root or other admin user> -p <dbf/create_n2graphsql`

4. Customize `config.php` file with your current user, password and language

`nano cfg/config.php`

5. Install files (
```
cp -r -f cfg/* /usr/local/n2graph/cfg
cp -r -f www /usr/local/n2graph
cp -f n2gproc.php /usr/local/n2graph
find /usr/local/n2graph -exec chmod 755 {} \;
find /usr/local/n2graph -exec chown nagios {} \;
find /usr/local/n2graph -exec chgrp nagios {} \;
```
5. Restart background process:

`crontab -u nagios -e` and uncomment the line commented before.

6. Delete deprecated files
```
rm -f /usr/local/n2graph/www/js/n2graph_en.js
rm -f /usr/local/n2graph/www/js/n2graph_es.js

