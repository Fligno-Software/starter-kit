<?php

use Illuminate\Support\Facades\Route;

if (starterKit()->isSentryTestApiEnabled()) {
    Route::get('sentry-test', function () {
        throw new Exception('This is a Sentry test using a test route.');
    });
}
