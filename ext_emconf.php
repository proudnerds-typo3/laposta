<?php

$EM_CONF['laposta'] = [
    'title' => 'Laposta',
    'description' => 'Subscribe to and unsubscribe from Laposta newsletters',
    'category' => 'plugin',
    'author' => 'Jacco van der Post',
    'author_email' => 'support@proudnerds.com',
    'state' => 'stable',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '10.4.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.21-10.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];