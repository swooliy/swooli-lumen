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
            'daemonize' => env('', 1),

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
            'max_request' => 1000,

            /**
             * The model of dispatch
             */
            'dispatch_mode' => env('', 2),
            
            /**
             * Static File Directory
             */
            'document_root' => base_path("public"),

            /**
             * Whether use swoole to handle static files
             */
            'enable_static_handler' => true,
        ], 

    ],

    /**
     * Cache Setting
     */
    'cache' => [
        'switch' => 1,
        'apis' => [
            '/test' => [
                'tags' => ['tag'],
                'fields' => ['field'],
            ],
        ],
    ],

    /**
     * Wacher Setting
     */
    'watcher' => [
        /**
         * Whether open the auto-watch or not
         */
        'enable' => 0,

        /**
         * The files where watch.
         */
        'files' => [
            base_path(),
        ],
    ],
];
  