<?php

return [
    'user_model' => env('SK_USER_MODEL', config('auth.providers.users.model')),
    'web_middleware' => env('SK_WEB_MIDDLEWARE', []),
    'api_middleware' => env('SK_API_MIDDLEWARE', []),
    'override_exception_handler' => env('SK_OVERRIDE_EXCEPTION_HANDLER', false),
    'enforce_morph_map' => env('SK_ENFORCE_MORPH_MAP', true),
    'dynamic_relationships_enabled' => env('SK_DYNAMIC_RELATIONSHIP_ENABLED', true),
    'configs_enabled' => env('SK_CONFIGS_ENABLED', true),
    'migrations_enabled' => env('SK_MIGRATIONS_ENABLED', true),
    'routes_enabled' => env('SK_ROUTES_ENABLED', true),
    'translations_enabled' => env('SK_TRANSLATIONS_ENABLED', true),
    'repositories_enabled' => env('SK_REPOSITORIES_ENABLED', true),
    'policies_enabled' => env('SK_POLICIES_ENABLED', true),
    'observers_enabled' => env('SK_OBSERVERS_ENABLED', true),
    'verify_ssl' => env('SK_VERIFY_SSL', true),
    'sentry_enabled' => env('SK_SENTRY_ENABLED', false),
    'sentry_test_api_enabled' => env('SK_SENTRY_TEST_API_ENABLED', false),
];
