<?php

declare(strict_types=1);

return [

    'discovery' => [
        'namespace' => 'App\\',
        'path' => app_path(),
    ],


    /**
     * Number of decimal places to round to when calculating band values
     */

    'precision' => 10,

    /**
     * Delay in seconds before processing the buffered splitter updates.
     * This allows for batching multiple updates together to reduce the number of jobs dispatched.
     * 
     * It is recommended to adjust this value based on the expected frequency of model updates in your application.
     * A lower value will result in more frequent job dispatches,
     * while a higher value will allow for more batching but may introduce a delay in processing updates.
     */
    'buffer_delay' => 10,

    /**
     * General splitters configuration
     */

    'splitters' => [

        /**
         * Default queue name for splitter jobs.
         * Can be overridden by individual splitter classes using the `queue()` method.
         */

        'queue' => 'default',
        
        /**
         * Aliases for splitters
         * 
         * You can define aliases for your splitters here, so you can refer to them by a shorter name.
         * Example:
         * 'aliases' => [
         *     'my_splitter' => \App\Splitters\MySplitter::class,
         * ],
         */

        'aliases' => [
            
        ],
    ],

];
