<?php

return [
    'override_exception_handler' => env('SK_OVERRIDE_EXCEPTION_HANDLER', FALSE),
    'web_guard' => env('SK_WEB_GUARD', []),
    'api_guard' => env('SK_API_GUARD', []),
];
