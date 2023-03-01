<?php

return [
    'cognito' => [
        "region" => env("COGNITO_REGION"),
        "user_pool_id" => env("COGNITO_USER_POOL_ID"),
        "app_token" => env("COGNITO_APP_TOKEN"),
        "login_url" => env("COGNITO_LOGIN_URL"),
    ],
    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID')
    ]
];
