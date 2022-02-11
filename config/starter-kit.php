<?php

return [
    'override_exception_handler' => env('SK_OVERRIDE_EXCEPTION_HANDLER', false),
    'web_guard' => env('SK_WEB_GUARD', []),
    'api_guard' => env('SK_API_GUARD', []),
    'enforce_morph_map' => env('SK_ENFORCE_MORPH_MAP', false),
    'dynamic_relationships_enabled' => env('SK_DYNAMIC_RELATIONSHIP_ENABLED', true),
    'routes_enabled' => env('SK_ROUTES_ENABLED', false),
    'verify_ssl' => env('SK_VERIFY_SSL', true),
];
