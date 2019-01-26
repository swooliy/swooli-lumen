Docs
======

### Artisan commands

**start server**

```shell
php artisan swooliy:start
```

**stop server**

```shell
php artisan swooliy:stop
```

**reload server**

```shell
php artisan swooliy:reload
```

**restart server**

```shell
php artisan swooliy:restart
```

**watch server when developing**

```shell
php artisan swooliy:watch
```

### Custom service

Swooliy provides many options for server to start up. The configuration is located at config/swooliy.php. In this file you may specify how many work process you would like to run your application etc. Detailed parameter settings are available in [swoole official documents](https://wiki.swoole.com/wiki/page/274.html).

### Deploy with supervisor and nginx

We recommend using [Supervisor](http://www.supervisord.org) to  monitor and control the swooliy process.

#### supervisor config:

```shell
[program:swooliy]
process_name=swooliy
command=php /project/artisan swooliy:start
autostart=true
autorestart=true
user=www
numprocs=1
stdout_logfile=/tmp/swooliy.log
```

Start the server:

```shell
supervisorctl reload 
supervisorctl start swooliy
```

Restart the server:
```
supervisorctl restart swooliy
```

**Notice:**

When use the supervisor, you must set the `swooliy.options.daemon = 0`, and when restart server, using 

```shell
supervisorctl restart swooliy
```

not using:
```shell
php artisan swooliy:restart
```

#### nginx config:

```shell
server {
    root /data/wwwroot/;
    server_name swooliy.lumen.com;

    location / {
        proxy_http_version 1.1;
        proxy_set_header Connection "keep-alive";
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header Host $http_host;
        proxy_set_header X-Forwarded-Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        if (!-e $request_filename) {
            proxy_pass http://127.0.0.1:13140;
        }
    }
}
```


