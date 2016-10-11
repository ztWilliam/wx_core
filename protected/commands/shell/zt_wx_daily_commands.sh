# This line shall be added to cron table :
#   30 00 * * * /usr/local/myshells/zt_wx_daily_commands.sh
# This file shall be copied to /usr/local/myshells directory.

# without time limitation, it will not stop until the files are all added to cloud.
# SAMPLE :
# /usr/local/php53/bin/php /home/www/dev/wx_core/protected/yiic.php wxApiDaemon transferFiles >> /var/log/zt_wx_transferFile_log.log
[PathToPhpRoot]/bin/php /[PathToWebRoot]/wx_core/protected/yiic.php wxApiDaemon transferFiles >> /[PathToLogRoot]/zt_wx_transferFile_log.log

# It will stop in 4 hours even though there are files not added to cloud.
# [PathToPhpRoot]/bin/php /[PathToWebRoot]/wx_core/protected/yiic.php wxApiDaemon transferFiles --maxHours 4 >> /[PathToLogRoot]/zt_wx_transferFile_log.log

