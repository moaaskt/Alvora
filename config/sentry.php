<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sentry DSN & Environment
    |--------------------------------------------------------------------------
    |
    | The DSN tells Sentry where to send the events. If this value is not set,
    | the SDK will be disabled.
    |
    */

    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    'environment' => env('APP_ENV', 'production'),

    'release' => env('APP_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring & Tracing
    |--------------------------------------------------------------------------
    |
    | The percentage of transactions that should be sent to Sentry (0.0 - 1.0).
    |
    */

    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.2),

    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0.1),

    /*
    |--------------------------------------------------------------------------
    | Data Privacy & LGPD Compliance
    |--------------------------------------------------------------------------
    |
    | By default, Sentry does not send PII (Personally Identifiable Information).
    |
    */

    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),

    /*
    |--------------------------------------------------------------------------
    | Ignored Exceptions
    |--------------------------------------------------------------------------
    |
    | Standard exceptions that do not require Sentry logging.
    |
    */

    'ignore_exceptions' => [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Validation\ValidationException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Context Tracking
    |--------------------------------------------------------------------------
    */

    'controllers_base_namespace' => env('SENTRY_CONTROLLERS_BASE_NAMESPACE', 'App\\Http\\Controllers'),

];
