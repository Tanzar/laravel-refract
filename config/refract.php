<?php

declare(strict_types=1);

return [

    /**
     * Number of decimal places to round to when calculating band values
     */

    'precision' => 10,

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
