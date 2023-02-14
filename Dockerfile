FROM php:8.1.15-fpm

ENV TZ=Asia/Shanghai PERMS=true \
    PUID=1026 PGID=100

#COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN sed -i -E 's/(deb|security).debian.org/mirrors.aliyun.com/g' /etc/apt/sources.list \
    && apt-get update \
    && apt-get install --no-install-recommends -y apt-utils yasm ffmpeg nginx\
    # 通用
    ca-certificates \
    wget vim procps\
    libzip-dev zlib1g-dev libfreetype6 libwebp-dev libjpeg-dev libpng-dev\
    && /usr/local/bin/docker-php-ext-install zip gd pcntl\
    # cleanup
    && apt-get clean \
    && rm -rf \
       /tmp/* \
       /var/lib/apt/lists/* \
       /var/tmp/*
COPY . /app
COPY ./nginx.conf /etc/nginx/nginx.conf
#COPY ./config/version.ini /app/subtrans/config/version.ini

WORKDIR /app
RUN rm /app/database/subtrans \
    && mv /app/database/subtrans-init /app/database/subtrans \
    && chmod +x /app/run.sh \
    && chown -R www-data:www-data /app

EXPOSE 6550
STOPSIGNAL SIGQUIT

ENTRYPOINT ["/app/run.sh"]