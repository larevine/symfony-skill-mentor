#!/bin/sh

# Создаем директорию для логов если она не существует
mkdir -p /app/var/log/supervisor

# Запускаем supervisor
exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
