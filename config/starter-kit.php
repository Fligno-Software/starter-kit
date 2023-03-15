<?php

return [
    'user_model' => env('SK_USER_MODEL', config('auth.providers.users.model')),
    'web_middleware' => env('SK_WEB_MIDDLEWARE', []),
    'api_middleware' => env('SK_API_MIDDLEWARE', []),
    'override_exception_handler' => env('SK_OVERRIDE_EXCEPTION_HANDLER', false),
    'enforce_morph_map' => env('SK_ENFORCE_MORPH_MAP', true),
    'verify_ssl' => env('SK_VERIFY_SSL', true),
    'sentry_enabled' => env('SK_SENTRY_ENABLED', false),
    'sentry_test_api_enabled' => env('SK_SENTRY_TEST_API_ENABLED', false),
    'change_locale' => [
        'enabled' => env('SK_CHANGE_LOCALE_ENABLED', true),
        'key' => env('SK_CHANGE_LOCALE_KEY', 'lang'),
    ],
];
