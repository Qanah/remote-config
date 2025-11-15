<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Remote Config & Experiments
    |--------------------------------------------------------------------------
    |
    | This master switch enables or disables the entire experiment system.
    | When disabled, the API will return base configurations without any
    | experiments or winners applied.
    |
    */

    'enabled' => env('REMOTE_CONFIG_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Testing Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, allows IP-based test overrides via Redis. This lets
    | testers preview specific variants before deploying to production.
    | Requires Redis to be configured.
    |
    */

    'testing_enabled' => env('REMOTE_CONFIG_TESTING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | User Created After Date
    |--------------------------------------------------------------------------
    |
    | Only users created after this date will be included in experiments.
    | This prevents changing experience for existing users. Use format: Y-m-d
    |
    */

    'user_created_after_date' => env('REMOTE_CONFIG_USER_CREATED_AFTER', '2022-08-29'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | TTL (in seconds) for caching experiment assignments and counters.
    | Default: 604800 (7 days)
    |
    */

    'cache_ttl' => env('REMOTE_CONFIG_CACHE_TTL', 604800),

    /*
    |--------------------------------------------------------------------------
    | Database Table Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix for all package database tables. Leave empty for no prefix.
    | Default tables: flows, experiments, experiment_assignments, etc.
    |
    */

    'table_prefix' => env('REMOTE_CONFIG_TABLE_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Web Routes Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the admin panel routes (prefix, middleware, name prefix)
    |
    */

    'routes' => [
        'enabled' => true,
        'prefix' => env('REMOTE_CONFIG_ROUTE_PREFIX', 'remote-config'),
        'middleware' => ['web', 'auth'],
        'as' => 'remote-config.',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Routes Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the client-facing API routes
    |
    */

    'api_routes' => [
        'enabled' => true,
        'prefix' => env('REMOTE_CONFIG_API_PREFIX', 'api/config'),
        'middleware' => ['api'],
        'as' => 'remote-config.api.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Experimentable Model
    |--------------------------------------------------------------------------
    |
    | The model that will receive experiment assignments. Typically your User
    | model. This model must use the Experimentable trait.
    |
    */

    'experimentable_model' => env('REMOTE_CONFIG_USER_MODEL', \App\Models\User::class),

    /*
    |--------------------------------------------------------------------------
    | Attribute Mapping
    |--------------------------------------------------------------------------
    |
    | Maps the standard attribute names to possible field names on your
    | experimentable model. The system will try each field in order until
    | it finds one that exists on the model.
    |
    */

    'attribute_mapping' => [
        'platform' => ['platform', 'os'],
        'country' => ['country_code', 'geo_country_code'],
        'language' => ['language', 'lang'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Flow Types
    |--------------------------------------------------------------------------
    |
    | Define the types of configuration flows available in your application.
    | These will appear in dropdowns in the admin panel.
    |
    */

    'flow_types' => [
        'general' => 'General Configuration',
        'onboarding' => 'Onboarding Flow',
        'feature' => 'Feature Configuration',
        'ui' => 'UI Settings',
        'content' => 'Content Configuration',
    ],

    /*
    |--------------------------------------------------------------------------
    | Targeting Options
    |--------------------------------------------------------------------------
    |
    | Define available platforms, countries, and languages for experiment
    | targeting. Customize these to match your application's requirements.
    |
    */

    'targeting' => [

        'platforms' => [
            'ios' => 'iOS',
            'android' => 'Android',
            'web' => 'Web',
            'mobile_web' => 'Mobile Web',
        ],

        'countries' => [
            'SA' => 'Saudi Arabia',
            'AE' => 'United Arab Emirates',
            'KW' => 'Kuwait',
            'QA' => 'Qatar',
            'BH' => 'Bahrain',
            'OM' => 'Oman',
            'EG' => 'Egypt',
            'JO' => 'Jordan',
            'LB' => 'Lebanon',
            'US' => 'United States',
            'GB' => 'United Kingdom',
        ],

        'languages' => [
            'ar' => 'Arabic',
            'en' => 'English',
            'fr' => 'French',
            'es' => 'Spanish',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Redis-based experiment counter storage. Redis is used
    | for atomic increment operations to distribute users across variants
    | based on configured ratios.
    |
    */

    'redis' => [
        'connection' => env('REMOTE_CONFIG_REDIS_CONNECTION', 'default'),
        'key_prefix' => 'remote_config:counter:',
    ],

    /*
    |--------------------------------------------------------------------------
    | Testing Override Configuration
    |--------------------------------------------------------------------------
    |
    | Redis cache key prefix for storing test overrides
    |
    */

    'testing' => [
        'cache_key_prefix' => 'remote_config_test_',
        'redis_connection' => env('REMOTE_CONFIG_REDIS_CONNECTION', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | Enable automatic audit logging for all changes to flows, experiments,
    | winners, and assignments. Logs include diffs of changes.
    |
    */

    'audit_logging' => [
        'enabled' => true,
        'log_assignments' => true,
        'log_confirmations' => true,
        'user_id_column' => 'user_id', // Column name in your auth table
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Configuration
    |--------------------------------------------------------------------------
    |
    | Customize the admin panel appearance and behavior
    |
    */

    'admin' => [
        'per_page' => 20,
        'title' => 'Remote Config & Experiments',
        'logo' => null, // URL to logo image
        'show_stats' => true,
        'date_format' => 'Y-m-d H:i:s',
        'home_route' => 'remote-config.experiments.index', // Default route when clicking title
    ],

    /*
    |--------------------------------------------------------------------------
    | JSON Editor Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the JSONEditor used in admin forms
    |
    */

    'json_editor' => [
        'mode' => 'tree', // tree, code, form, text, view
        'modes' => ['tree', 'code', 'form', 'text', 'view'],
        'theme' => 'ace/theme/jsoneditor',
        'cdn_version' => '9.10.0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Define validation rules for experiments
    |
    */

    'validation' => [
        'prevent_overlapping_experiments' => true,
        'min_ratio' => 1,
        'max_ratio' => 100,
        'require_platforms' => true,
        'require_flows' => true,
    ],

];
