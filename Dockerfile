FROM php:8.1-cli

ENV TZ=Asia/Shanghai PERMS=true \
    PUID=1026 PGID=100

#COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN sed -i -E 's/(deb|security).debian.org/mirrors.aliyun.com/g' /etc/apt/sources.list \
    && apt-get update \
    && apt-get install --no-install-recommends -y yasm ffmpeg\
    # 通用
    ca-certificates \
    wget \
    libzip-dev zlib1g-dev\
    && /usr/local/bin/docker-php-ext-install zip\
    # cleanup
    && apt-get clean \
    && rm -rf \
       /tmp/* \
       /var/lib/apt/lists/* \
       /var/tmp/*
COPY . /app/subtrans
WORKDIR /app/subtrans
RUN rm /app/subtrans/database/subtrans \
    && mv /app/subtrans/database/subtrans-init /app/subtrans/database/subtrans
CMD ["php", "./cli/subtrans.php"]
