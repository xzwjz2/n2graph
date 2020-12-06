# TROUBLESHOOTING

### No data is shown in graphics.

Check if nagios is generating data properly: look for files in folder /var/nagios/dat. There must be files with extensions `.procesado` with datetimes no older than the period you set up in nagios configuration during install.

Check for file /var/nagios/n2gproc_AAAA_MM_DD.log. It must contain lines like:
```
2020-12-05 02:45:01;Processed:2020-12-05-02:43:46-service.dat - Lines:40 - Metrics:106
2020-12-05 02:45:01;Processed:2020-12-05-02:43:46-host.dat - Lines:30 - Metrics:30
```
Check for errors in file /var/nagios/n2gproc_error_AAAA_MM_DD.log

Check if records are being writen to the table hmet in the database (Use mysql command line utility or other tool like phpMyAdmin or Adminer).

### No Hosts or Services are listed in main or config screens. 

Check for errors files /var/nagios/n2graph_error_AAAA_MM_DD.log or /var/nagios/n2gconfig_error_AAAA_MM_DD.log


 
