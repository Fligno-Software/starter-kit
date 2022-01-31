<?php

return [
    'override_exception_handler' => env('SK_OVERRIDE_EXCEPTION_HANDLER', FALSE),
    'web_guard' => env('SK_WEB_GUARD', []),
    'api_guard' => env('SK_API_GUARD', []),
    'enforce_morph_map' => env('SK_ENFORCE_MORPH_MAP', false),
    'routes_enabled' => env('SK_ROUTES_ENABLED', false),
];
