<?php

use UonSoftware\LaraAuth\Notifications\PasswordChangeNotification;
use UonSoftware\LaraAuth\Notifications\PasswordChangedNotification;
use UonSoftware\LaraAuth\Http\Resources\User as UserResource;

return [
    'register'             => false,
    'users_table'          => 'users',
    'user_model'           => 'App\User',
    'login_validation'     => [
        'email'    => 'required|email|exists:users,email',
        'password' => 'required|min:8',
    ],
    'user'                 => [
        'email' => [
            'field_on_model'     => 'email',
            'field_from_request' => 'email',
        ],

        'password'           => [
            'field_on_model'     => 'password',
            'field_from_request' => 'password',
        ],
        'email_verification' => [
            'field' => 'email_activated_at',
            'check' => true,
        ],
        'search'             => [
            [
                'field'    => 'email',
                'operator' => '=',
            ],
        ],
    ],
    'serialization_fields' => [
        'name'    => 'name',
        'surname' => 'surname',
        'email'   => 'email',
    ],
    'user_resource'        => UserResource::class,
    'password_reset'       => [
        'frontend_url'                  => [
            'base'            => env('FRONTEND_URL', 'http://localhost:3000'),
            'change_password' => env('FRONTEND_CHANGE_PASSWORD_ROUTE', '/change-password'),
        ],
        'change_password_validation'    => [
            'required',
            'min:8',
            'confirmed',
        ],
        'ttl'                           => env('PASSWORD_RESET_TTL', 15),
        'request_notification'          => PasswordChangeNotification::class,
        'password_changed_notification' => PasswordChangedNotification::class,
    ],
];
