<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Laposta',
    'description' => 'Subscribe to and unsubscribe from Laposta newsletters',
    'category' => 'plugin',
    'author' => 'Jacco van der Post',
    'author_email' => 'support@proudnerds.com',
    'state' => 'stable',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];