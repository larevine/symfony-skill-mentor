FROM php:8.3-fpm-alpine

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN addgroup -g ${GROUP_ID} otus && \
    adduser -D -u ${USER_ID} -G otus otus

USER root
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    autoconf \
    build-base \
    icu-dev \
    libevent-dev \
    openssl-dev \
    zlib-dev \
    libzip \
    libzip-dev \
    bzip2 \
    git \
    libpng \
    libpng-dev \
    libjpeg \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype \
    freetype-dev \
    postgresql-dev \
    linux-headers \
    libmemcached-dev \
    rabbitmq-c \
    rabbitmq-c-dev \
    curl \
    wget \
    bash \
    && rm -rf /var/cache/apk/*

# Устанавливаем расширения PHP через docker-php-ext
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) \
        intl \
        gd \
        bcmath \
        pcntl \
        pdo_pgsql \
        sockets \
        zip

# Устанавливаем PECL расширения с конкретными версиями
RUN pecl channel-update pecl.php.net \
    && pecl install xdebug \
    && pecl install redis \
    && pecl install amqp \
    && pecl install memcached \
    && pecl install event \
    && rm -rf /tmp/pear \
    && echo "extension=amqp.so" > /usr/local/etc/php/conf.d/amqp.ini \
    && echo "extension=redis.so" > /usr/local/etc/php/conf.d/redis.ini \
    && echo "extension=event.so" > /usr/local/etc/php/conf.d/event.ini \
    && echo "extension=memcached.so" > /usr/local/etc/php/conf.d/memcached.ini \
    && echo "zend_extension=xdebug.so" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Настраиваем рабочую директорию и права для пользователя
RUN mkdir -p /app && \
    chown -R otus:otus /app /usr/local/lib/php /usr/local/etc/php/conf.d

USER otus

WORKDIR /app

COPY ./conf.d/php.ini /usr/local/etc/php/php.ini
COPY ./conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini