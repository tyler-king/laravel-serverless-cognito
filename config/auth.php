<?php

return [
    'guards' => [
        'cognito' => [
            'driver' => 'cognito',
            'provider' => 'users'
        ],
        'firebase' => [
            'driver' => 'firebase',
            'provider' => 'users'
        ],
    ]
];
