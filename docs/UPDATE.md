# UPDATE INSTRUCTIONS

## From versions previous to 1.0.0

This instructions apply if you have a running installation previous to versions 1.0.0

1. Stop background process:
```
sudo su
crontab -u nagios -e
```
and comment (add a # at the beggining) the line: `# */5 * * * * /usr/bin/php -f /usr/local/n2graph/n2gproc.php >/dev/null 2>&1`

2. Download new version package to a temporary location (replace X.X.X with version number)
```
cd /tmp
wget  https://github.com/xzwjz2/n2graph/archive/vX.X.X.tar.gz
tar -xvf vX.X.X.tar.gz
cd n2graph-vX.X.X
```

(under working)


