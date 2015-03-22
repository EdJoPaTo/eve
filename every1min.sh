#!/bin/bash
user=`whoami`
logpath=/var/log/$user/cron/
logfile=`basename $0`
logfile=${logfile/.sh/.log}
logfullpath=$logpath$logfile

mkdir -p $logpath

echo >> $logfullpath
date >> $logfullpath
php -r "require '/usr/share/nginx/eve/classes/updateEVEdb.php'; updateall();" >> $logfullpath

