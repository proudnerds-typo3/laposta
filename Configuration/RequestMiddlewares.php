<?php

declare(strict_types=1);

use Proudnerds\Laposta\Middleware\AltchaChallengeMiddleware;

return [
    'frontend' => [
        'proudnerds/laposta/altcha-challenge' => [
            'target' => AltchaChallengeMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver',
            ],
        ],
    ],
];
