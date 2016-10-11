# This line shall be added to cron table :
#   * 05 * * * /usr/local/myshells/zt_wx_by_minutes_commands.sh
# This file shall be copied to /usr/local/myshells directory.

# without time limitation, it will not stop until the files are all added to cloud.
# SAMPLE :
# /usr/local/php53/bin/php /home/www/dev/wx_core/protected/yiic.php wxApiDaemon clearExpiredOnlineUsers >> /var/log/zt_wx_by_minutes_log.log
[PathToPhpRoot]/bin/php /[PathToWebRoot]/wx_core/protected/yiic.php wxApiDaemon clearExpiredOnlineUsers >> /[PathToLogRoot]/zt_wx_by_minutes_log.log

# It will stop in 4 hours even though there are files not added to cloud.
# [PathToPhpRoot]/bin/php /[PathToWebRoot]/wx_core/protected/yiic.php wxApiDaemon clearExpiredOnlineUsers --maxHours 4 >> /[PathToLogRoot]/zt_wx_by_minutes_log.log

