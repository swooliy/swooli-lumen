Swooliy-Lumen
==============

Swoole CLI Tool for Lumen Server.

### Deploy 

Using [Supervisor](http://www.supervisord.org):

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
Notice: you must set the `swooliy.options.daemon = 0`.