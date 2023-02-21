#!/bin/bash

if [ ! -d "/app/config/log" ]; then
  mkdir /app/config/log
fi
if [ ! -d "/app/config/log/nginx" ]; then
  mkdir /app/config/log/nginx
fi

if [ ! -f "/app/config/subtrans" ]; then
  cp /app/insideConfig/subtrans-init /app/config/subtrans
fi

php-fpm -D

php /app/cli/scanTask.php --start &

nginx -g "daemon off;"


#cd /app/public || exit

#php -S 0.0.0.0:6550