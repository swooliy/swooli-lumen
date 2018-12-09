<?php 

return [
    'server' => [
        'name' => env('SWOOLIY_SERVER_NAME', 'swooliy'),
        'host' => env('', '0.0.0.0'),
        'port' => env('', '13140'),
        'options' => [
            'worker_num' => env('', 100),
            'task_worker_num' => env('', 0),
            'daemonize' => env('', 0),
            'log_file' => env('', base_path("storage/logs/swoole.log")),
            'pid_file' => env('', base_path("storage/logs/pid")),
             // if the system open files setting is 65536(show using `ulimit -a`), can reseting it by `ulimit -n 10000000`
            'max_conn' => env('', 10000),
            // 'package_max_length' => 2,
            // 'max_request' => 0,
            // 'open_cpu_affinity' => 1,
            // 'dispatch_mode' => env('', 2),
        ],

    ],
    'cache' => [
        'ingore_apis' => ['/'],
        'ingore_fields' => ['timestamp', 'sign'],
    ],
];