FROM php:8.4-cli-alpine

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
    oniguruma-dev \
    supervisor \
    && rm -rf /var/cache/apk/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) \
        intl \
        pdo_pgsql \
        mbstring \
        zip \
        gd \
        bcmath \
        pcntl \
        sockets

# Install PECL extensions with specific versions to ensure compatibility
RUN apk add --no-cache \
    && pecl channel-update pecl.php.net \
    && pecl install redis \
    && pecl install amqp \
    && pecl install memcached \
    && pecl install event \
    && rm -rf /tmp/pear \
    && echo "extension=amqp.so" > /usr/local/etc/php/conf.d/amqp.ini \
    && echo "extension=redis.so" > /usr/local/etc/php/conf.d/redis.ini \
    && echo "extension=event.so" > /usr/local/etc/php/conf.d/event.ini \
    && echo "extension=memcached.so" > /usr/local/etc/php/conf.d/memcached.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Настройка supervisor
RUN mkdir -p /var/log/supervisor /var/run/supervisor \
    && touch /var/run/supervisor/supervisord.pid \
    && touch /var/run/supervisord.pid

# Копируем конфиг supervisor
COPY supervisord.conf /etc/supervisor/supervisord.conf

# Создание директории для логов
RUN mkdir -p /app/var/log/supervisor

# Создаем скрипт для запуска
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Установка прав для пользователя otus
RUN chown -R otus:otus \
    /var/log/supervisor \
    /app/var/log/supervisor \
    /var/run/supervisor \
    /var/run/supervisord.pid \
    /usr/local/bin/docker-entrypoint.sh

USER otus

WORKDIR /app

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
