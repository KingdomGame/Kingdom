#!/bin/bash

# Копирование конфигов для nginx
cp -r /kingdom/app/docker/nginx /etc
ln -s /etc/nginx/sites-available/kingdom.conf /etc/nginx/sites-enabled/

## Удаление кэша
#rm -rf /kingdom/app/cache/* /kingdom/app/logs/*

# Инициализация серверов при запуске контейнера
/etc/init.d/php5-fpm start
/etc/init.d/nginx start
/etc/init.d/redis-server start

# Обновление библиотек композера
/composer.phar install -n -d /kingdom/

# Создание БД, при ее отсутствии
/kingdom/app/console doctrine:database:create > /dev/null 2>&1

# Обновление структуры БД
/kingdom/app/console doctrine:schema:update --force

# Симфони-команды
# Загрузка игровых данных в БД
/kingdom/app/console kingdom:map:create
/kingdom/app/console kingdom:items:create

# Изменение прав на директории app/cache и app/logs
chown -R :www-data /kingdom/app/cache /kingdom/app/logs
chmod -R 777 /kingdom/app/cache /kingdom/app/logs
chown -R :www-data /kingdom/web

# Запуск node.js приложений
cd /kingdom/websocket
(node router.js &) && node gate.js
