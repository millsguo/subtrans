#!/bin/bash

if [ ! -d "/app/config/log" ]; then
  mkdir /app/config/log
fi
if [ ! -d "/app/config/log/nginx" ]; then
  mkdir /app/config/log/nginx
fi

php-fpm -D
nginx -g "daemon off;"


#cd /app/public || exit

#php -S 0.0.0.0:6550