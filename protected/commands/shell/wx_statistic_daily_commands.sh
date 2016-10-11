# This line shall be added to cron table :
#   30 00 * * * /usr/local/myshells/wx_statistic_daily_commands.sh

# This file shall be copied to /usr/local/myshells directory.

# SAMPLE :
# /usr/local/php53/bin/php /home/www/dev/wx_core/protected/yiic.php wxStatistic gatherUserActivity >> /var/log/wx_statistic_log.log
[PathToPhpRoot]/bin/php /[PathToWebRoot]/wx_core/protected/yiic.php wxStatistic gatherUserActivity >> /[PathToLogRoot]/wx_statistic_log.log

# SAMPLE :
# /usr/local/php53/bin/php /home/www/dev/wx_core/protected/yiic.php wxStatistic gatherUserSubscribed >> /var/log/wx_statistic_log.log
[PathToPhpRoot]/bin/php /[PathToWebRoot]/wx_core/protected/yiic.php wxStatistic gatherUserSubscribed >> /[PathToLogRoot]/wx_statistic_log.log
