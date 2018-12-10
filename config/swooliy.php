<?php

return [
    /**
     * Server'config
     */
    'server' => [
        /**
         * Server'name, will set process name by it
         */
        'name' => env('SWOOLIY_SERVER_NAME', 'swooliy'),
        
        /**
         * Server's host
         */
        'host' => env('', '0.0.0.0'),

        /**
         * Server's port
         */
        'port' => env('', '13140'),

        /**
         * Server's options
         */
        'options' => [
            /**
             * Worker process's number
             */
            'worker_num' => env('', 100),

            /**
             * Task process's number
             */
            'task_worker_num' => env('', 0),

            /**
             * Whether the server running in daemon mode or not
             */
            'daemonize' => env('', 0),

            /**
             * Which file the log put in
             */
            'log_file' => env('', base_path("storage/logs/swoole.log")),

            /**
             * Which file the master process's pid put in 
             */
            'pid_file' => env('', base_path("storage/logs/pid")),

            /**
             * Maximum number of concurrent connections
             * if the system open files setting is 65536(show using `ulimit -a`), can reseting it by `ulimit -n 10000000`
             */
            'max_conn' => env('', 10000),

            /**
             * Maximum size of POST request data
             */
            'package_max_length' => 2,

            /**
             * Maximun count when the worker process will restart
             * Default:0, never restart
             */
            'max_request' => 0,
            // 'open_cpu_affinity' => 1,
            // 'dispatch_mode' => env('', 2),
        ],

    ],
    'cache' => [
        'ingnore_apis' => [
            '/',
            '/favicon.ico',
        ],
        'ingnore_fields' => ['timestamp', 'sign', 'app_id'],
        'columns' => [
            'status_code' => [
                'type' => \Swoole\Table::TYPE_INT,
                'size' => 4,
            ],
            'content_type' => [
                'type' => \Swoole\Table::TYPE_STRING,
                'size' => 30,
            ],
            'content' => [
                'type' => \Swoole\Table::TYPE_STRING,
                'size' => 5000,
            ],
        ],
    ],
];
  