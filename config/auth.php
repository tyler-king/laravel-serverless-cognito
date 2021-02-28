<?php

return [
    'guards' => [
        'cognito' => [
            'driver' => 'cognito',
            'provider' => 'cognito'
        ],
    ],
    'providers' => [
        'cognito' => [
            'driver' => 'eloquent',
            'model' => TKing\ServerlessCognito\Cognito::class,
        ],
    ],
];
