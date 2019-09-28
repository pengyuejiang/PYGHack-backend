# Zuggr Lumen App
Start writing your awesome app here

# Git Init
1. Create a new git repo. If remote, please clone it to your local machine
2. Clone Zuggr-Cloud-Lumen-Init
```
git clone https://github.com/zimo-xiao/zuggr-cloud-lumen-init.git
```
3. Init Zuggr-Cloud-Lumen-Init
```
composer install
```
4. Init your new git repo
```
php artisan init:git ~/Documents/Project/your-awesome-project
```

# Laradock Install
1. Install Laradock into the same folder as your other projects
```
git clone https://github.com/laradock/laradock.git
```

2. Config .env file
```
cd laradock
cp env-example .env

>> .env

# config MySQL pass & account
# config swoole
PHP_WORKER_INSTALL_SWOOLE=true
```

3. Config NGINX
```
cd nginx/sites
vim your.app.zuggr.com.conf

>> your.app.zuggr.com.conf

upstream swoole {
    server php-worker:5200 weight=5 max_fails=3 fail_timeout=30s;
    keepalive 16;
}

server {
    listen         80;
    server_name    your.app.zuggr.com;
    return         301 https://$server_name$request_uri;
}

server {

    listen 443 ssl;
    listen [::]:443 ssl ipv6only=on;
    ssl_certificate /etc/nginx/ssl/your.app.cloud.crt;
    ssl_certificate_key /etc/nginx/ssl/your.app.cloud.key;

    server_name your.app.zuggr.com;
    root /var/www/zuggr-cloud-core/public;
    autoindex off;
    index index.html index.htm;

    location / {
        try_files $uri @laravels;
    }

    location @laravels {
        proxy_http_version 1.1;
        proxy_set_header Connection "";
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Real-PORT $remote_port;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header Server-Protocol $server_protocol;
        proxy_set_header Server-Name $server_name;
        proxy_set_header Server-Addr $server_addr;
        proxy_set_header Server-Port $server_port;
        proxy_pass http://swoole;
    }

    error_log /var/log/nginx/your.app_error.log;
    access_log /var/log/nginx/your.app_access.log;
}
```

4. Config MySQL
```
>> mysql/my.conf

character-set-server=utf8mb4
default-authentication-plugin=mysql_native_password

>> dmysql/ocker-entrypoint-initdb.d/createdb.sql

alter user 'root'@'localhost' identified with mysql_native_password by 'password';
alter user 'your.app'@'localhost' identified with mysql_native_password by 'password';
```

5. Config Swoole
```
>> docker-compose.yml
>> add this under php-worker -> ports
- "5200:5200"
>> add this under php-worker, nginx, mysql, redis -> restart
on-failure
```

6. Supervisor
```
cd laradock/php-work/supervisord.d

>> queue-foo-worker.conf

[program:queue-foo-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/zuggr-cloud-core/artisan queue:work redis --queue=foo --tries=3 --timeout=100
autostart=true
autorestart=true
user=laradock
numprocs=2
redirect_stderr=true

>> laravel-s-worker.conf

[program:laravel-s]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/zuggr-cloud-core/bin/laravels start -i
autostart=true
autorestart=true
startretries=3
user=laradock
numprocs=1
redirect_stderr=true
```

7. Build
```
docker-compose up -d php-worker nginx redis mysql

docker-compose exec workspace bash

>> bash

cd zuggr-cloud-core
composer install
cp .env.example .env
vim .env
php artisan cloud:init
```

# Laradock Maintaining
1. check running docker containers
```
docker container ls -a
```
2. shutdown containers
```
docker-compose down
```
3. rebuild containers
```
docker-compose build nginx
```
4. start containers
```
docker-compose up -d nginx
```
5. restart running containers
```
docker-compose restart nginx
```
6. restart running containers
```
docker-compose restart nginx
```
7. enter exec mode
```
docker-compose exec mysql bash
or
docker-compose exec mysql /bin/sh
```
8. Log
```
cd laradock/logs
```