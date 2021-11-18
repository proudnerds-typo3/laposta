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
    'version' => '11.5.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.3-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];