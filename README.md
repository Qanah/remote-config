# Remote Config - Laravel A/B Testing & Remote Configuration Package

A powerful Laravel package for A/B testing, remote configuration management, and feature experimentation. Manage your app's configuration remotely, run experiments, and deploy winning variants without code changes.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Integration Guide](#integration-guide)
- [Usage Examples](#usage-examples)
- [API Reference](#api-reference)
- [Database Structure](#database-structure)
- [Admin Panel](#admin-panel)
- [Testing](#testing)
- [Best Practices](#best-practices)

## Features

- ✅ **A/B Testing**: Create experiments with multiple variants and ratio-based distribution
- ✅ **Remote Configuration**: Manage app configurations remotely without deploying
- ✅ **Winner Deployment**: Deploy winning variants to specific user segments
- ✅ **Test Overrides**: IP-based testing for QA teams using Laravel Cache
- ✅ **Polymorphic User Support**: Works with any user model
- ✅ **Comprehensive Targeting**: Platform, country, and language targeting
- ✅ **Audit Logging**: Complete audit trail of all changes with diffs
- ✅ **Flexible Architecture**: Configurable table prefixes, routes, and middleware
- ✅ **Helper Functions**: Convenient helper functions for common operations
- ✅ **Admin Panel**: Built-in admin interface for managing experiments

## Requirements

- PHP >= 8.1
- Laravel >= 10.0
- Redis (for test overrides and experiment counters)
- MySQL/PostgreSQL/SQLite (for data persistence)

## Installation

### Step 1: Install Package via Composer

```bash
composer require jawabapp/remote-config
```

### Step 2: Publish Configuration File

```bash
php artisan vendor:publish --tag=remote-config-config
```

This creates `config/remote-config.php` in your Laravel application.

### Step 3: Configure Database Connection

Make sure your `.env` file has database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 4: Configure Redis (Required for Test Overrides)

Add Redis configuration to your `.env`:

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CACHE_DB=1
```

### Step 5: Run Migrations

```bash
php artisan migrate
```

This creates 11 tables for the package.

### Step 6: (Optional) Publish Views for Customization

```bash
php artisan vendor:publish --tag=remote-config-views
```

### Step 7: (Optional) Publish Assets

```bash
php artisan vendor:publish --tag=remote-config-assets
```

## Configuration

The `config/remote-config.php` file contains all package settings. Here's a complete breakdown:

### Basic Settings

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Remote Config & Experiments
    |--------------------------------------------------------------------------
    | Master switch - when disabled, API returns base configurations only
    */
    'enabled' => env('REMOTE_CONFIG_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Testing Mode
    |--------------------------------------------------------------------------
    | Enable IP-based test overrides for QA teams (requires Redis)
    */
    'testing_enabled' => env('REMOTE_CONFIG_TESTING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | User Created After Date
    |--------------------------------------------------------------------------
    | Only users created after this date participate in experiments
    | Format: Y-m-d
    */
    'user_created_after_date' => env('REMOTE_CONFIG_USER_CREATED_AFTER', '2022-08-29'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (Time To Live)
    |--------------------------------------------------------------------------
    | TTL in seconds for caching experiment assignments
    | Default: 604800 (7 days)
    */
    'cache_ttl' => env('REMOTE_CONFIG_CACHE_TTL', 604800),
];
```

### Routes Configuration

```php
/*
|--------------------------------------------------------------------------
| Web Routes (Admin Panel)
|--------------------------------------------------------------------------
*/
'routes' => [
    'enabled' => true,
    'prefix' => env('REMOTE_CONFIG_ROUTE_PREFIX', 'remote-config'),
    'middleware' => ['web', 'auth'], // Add your auth middleware
    'as' => 'remote-config.',
],

/*
|--------------------------------------------------------------------------
| API Routes (Client-facing)
|--------------------------------------------------------------------------
*/
'api_routes' => [
    'enabled' => true,
    'prefix' => env('REMOTE_CONFIG_API_PREFIX', 'api/config'),
    'middleware' => ['api'], // Add rate limiting if needed
    'as' => 'remote-config.api.',
],
```

### User Model Configuration

```php
/*
|--------------------------------------------------------------------------
| Experimentable Model
|--------------------------------------------------------------------------
| The model that participates in experiments (usually User model)
*/
'experimentable_model' => env('REMOTE_CONFIG_USER_MODEL', \App\Models\User::class),

/*
|--------------------------------------------------------------------------
| Attribute Mapping
|--------------------------------------------------------------------------
| Maps standard attributes to your model's field names
*/
'attribute_mapping' => [
    'platform' => ['platform', 'os'], // Tries 'platform' first, then 'os'
    'country' => ['country_code', 'geo_country_code'],
    'language' => ['language', 'lang'],
],
```

### Flow Types Configuration

```php
/*
|--------------------------------------------------------------------------
| Flow Types
|--------------------------------------------------------------------------
| Define the types of configurations available in your app
*/
'flow_types' => [
    'general' => 'General Configuration',
    'onboarding' => 'Onboarding Flow',
    'feature' => 'Feature Flags',
    'ui' => 'UI Settings',
    'content' => 'Content Configuration',
    'checkout' => 'Checkout Flow',
    'pricing' => 'Pricing Configuration',
],
```

### Targeting Options

```php
/*
|--------------------------------------------------------------------------
| Targeting Options
|--------------------------------------------------------------------------
| Define available platforms, countries, and languages
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
        // Add your countries
    ],

    'languages' => [
        'ar' => 'Arabic',
        'en' => 'English',
        'fr' => 'French',
        'es' => 'Spanish',
        // Add your languages
    ],
],
```

### Redis Configuration

```php
/*
|--------------------------------------------------------------------------
| Redis Configuration
|--------------------------------------------------------------------------
| For experiment counters and test overrides
*/
'redis' => [
    'connection' => env('REMOTE_CONFIG_REDIS_CONNECTION', 'default'),
    'key_prefix' => 'remote_config:counter:',
],

/*
|--------------------------------------------------------------------------
| Testing Override Configuration
|--------------------------------------------------------------------------
| Cache store for test overrides (redis, memcached, etc.)
*/
'testing' => [
    'cache_store' => env('REMOTE_CONFIG_TESTING_CACHE_STORE', 'redis'),
],
```

### Audit Logging

```php
/*
|--------------------------------------------------------------------------
| Audit Logging
|--------------------------------------------------------------------------
| Enable automatic audit logging for all changes
*/
'audit_logging' => [
    'enabled' => true,
    'log_assignments' => true,
    'log_confirmations' => true,
    'user_id_column' => 'user_id', // Column name in your user table
],
```

### Database Configuration

```php
/*
|--------------------------------------------------------------------------
| Database Table Prefix
|--------------------------------------------------------------------------
| Prefix for all package tables. Leave empty for no prefix.
*/
'table_prefix' => env('REMOTE_CONFIG_TABLE_PREFIX', ''),
```

## Quick Start

### 1. Add Trait to Your User Model

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Jawabapp\RemoteConfig\Traits\Experimentable;

class User extends Authenticatable
{
    use Experimentable;

    // Your existing user model code...
}
```

### 2. Create Your First Flow

```php
use Jawabapp\RemoteConfig\Models\Flow;

// Create a base configuration
$baseFlow = Flow::create([
    'type' => 'onboarding',
    'content' => [
        'steps' => ['welcome', 'profile', 'preferences'],
        'theme' => 'light',
        'show_tutorial' => false,
    ],
    'is_active' => true,
]);
```

### 3. Get Configuration in Your App

```php
// In your controller
use Jawabapp\RemoteConfig\Models\Flow;

public function getOnboardingConfig(Request $request)
{
    $user = $request->user();

    // Get configuration for user
    $config = get_user_config($user, 'onboarding', [
        'platform' => $request->input('platform', 'web'),
        'country' => $user->country_code ?? 'US',
        'language' => $user->language ?? 'en',
    ]);

    return response()->json([
        'success' => true,
        'config' => $config,
    ]);
}
```

## Integration Guide

### Step-by-Step Integration for a New Laravel App

#### 1. Install and Configure

Follow the [Installation](#installation) steps above.

#### 2. Update User Model

Add the `Experimentable` trait to your User model:

```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Jawabapp\RemoteConfig\Traits\Experimentable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Experimentable;

    // Add these attributes if you want to use targeting
    protected $fillable = [
        'name',
        'email',
        'password',
        'platform',      // ios, android, web
        'country_code',  // SA, US, etc.
        'language',      // ar, en, etc.
    ];
}
```

#### 3. Add User Attributes Migration (Optional)

If your users table doesn't have platform/country/language columns:

```php
php artisan make:migration add_targeting_fields_to_users_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('platform')->nullable()->index();
            $table->string('country_code', 2)->nullable()->index();
            $table->string('language', 2)->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['platform', 'country_code', 'language']);
        });
    }
};
```

```bash
php artisan migrate
```

#### 4. Create API Controller

Create a controller to serve configurations to your app:

```bash
php artisan make:controller Api/ConfigController
```

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jawabapp\RemoteConfig\Services\ConfigService;

class ConfigController extends Controller
{
    protected ConfigService $configService;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Get remote configuration for user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'type' => 'required|string',
            'platform' => 'nullable|string',
            'country' => 'nullable|string',
            'language' => 'nullable|string',
        ]);

        $config = $this->configService->getConfig(
            $user,
            $validated['type'],
            [
                'platform' => $validated['platform'] ?? null,
                'country' => $validated['country'] ?? null,
                'language' => $validated['language'] ?? null,
            ],
            $request->ip() // For test overrides
        );

        return response()->json([
            'success' => true,
            'data' => $config,
        ]);
    }
}
```

#### 5. Register API Routes

In `routes/api.php`:

```php
use App\Http\Controllers\Api\ConfigController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/config', [ConfigController::class, 'index']);
});
```

#### 6. Protect Admin Routes

In `config/remote-config.php`, configure admin routes:

```php
'routes' => [
    'enabled' => true,
    'prefix' => 'admin/remote-config',
    'middleware' => ['web', 'auth', 'admin'], // Add your admin middleware
    'as' => 'remote-config.',
],
```

Create an admin middleware if you don't have one:

```bash
php artisan make:middleware IsAdmin
```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
```

Register in `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ... other middleware
    'admin' => \App\Http\Middleware\IsAdmin::class,
];
```

## Usage Examples

### Example 1: Feature Flags

```php
// Create feature flags configuration
$featureFlags = Flow::create([
    'type' => 'feature',
    'content' => [
        'new_checkout' => false,
        'dark_mode' => true,
        'social_login' => true,
        'referral_program' => false,
    ],
    'is_active' => true,
]);

// In your app
$config = get_user_config($user, 'feature');

if ($config['new_checkout']) {
    // Show new checkout flow
} else {
    // Show old checkout flow
}
```

### Example 2: A/B Testing Onboarding Flow

```php
use Jawabapp\RemoteConfig\Models\Flow;
use Jawabapp\RemoteConfig\Models\Experiment;

// Create two onboarding variants
$variantA = Flow::create([
    'type' => 'onboarding',
    'content' => [
        'steps' => ['welcome', 'profile', 'preferences', 'complete'],
        'skip_enabled' => false,
    ],
    'is_active' => true,
]);

$variantB = Flow::create([
    'type' => 'onboarding',
    'content' => [
        'steps' => ['welcome', 'quick_setup'], // Shorter flow
        'skip_enabled' => true,
    ],
    'is_active' => true,
]);

// Create experiment
$experiment = Experiment::create([
    'name' => 'Quick Onboarding Test',
    'type' => 'onboarding',
    'platforms' => ['ios', 'android'],
    'countries' => ['US', 'SA'],
    'languages' => ['en'],
    'is_active' => true,
]);

// Attach variants with 50/50 split
$experiment->flows()->attach($variantA, ['ratio' => 50]);
$experiment->flows()->attach($variantB, ['ratio' => 50]);

// Users automatically get assigned
$config = get_user_config($user, 'onboarding', [
    'platform' => 'ios',
    'country' => 'US',
    'language' => 'en',
]);

// Track completion
$user->confirmExperiment('Quick Onboarding Test', [
    'completed' => true,
    'time_spent' => 120,
]);
```

### Example 3: Fetching Multiple Configuration Types

```php
// Fetch multiple types in a single API request
// GET /api/config?type[]=onboarding&type[]=feature&type[]=pricing

$response = Http::withToken($token)->get('https://api.example.com/api/config', [
    'type' => ['onboarding', 'feature', 'pricing'],
    'platform' => 'ios',
    'country' => 'US',
    'language' => 'en',
]);

// Response format for multiple types:
{
    "success": true,
    "data": {
        "onboarding": {
            "steps": ["welcome", "profile", "preferences"],
            "theme": "light"
        },
        "feature": {
            "new_checkout": true,
            "dark_mode": false
        },
        "pricing": {
            "monthly_price": 9.99,
            "trial_days": 14
        }
    },
    "meta": {
        "onboarding": {
            "has_experiment": true,
            "experiment_id": 5,
            "flow_id": 12
        },
        "feature": {
            "has_experiment": false,
            "experiment_id": null,
            "flow_id": 8
        },
        "pricing": {
            "has_experiment": true,
            "experiment_id": 3,
            "flow_id": 15
        }
    }
}

// Single type request
// GET /api/config?type=onboarding
{
    "success": true,
    "data": {
        "onboarding": {
            "steps": ["welcome", "profile", "preferences"],
            "theme": "light"
        }
    },
    "meta": {
        "onboarding": {
            "has_experiment": true,
            "experiment_id": 5,
            "flow_id": 12
        }
    }
}

// No type parameter - returns ALL active types
// GET /api/config
{
    "success": true,
    "data": {
        "onboarding": {...},
        "feature": {...},
        "pricing": {...}
    },
    "meta": {
        "onboarding": {...},
        "feature": {...},
        "pricing": {...}
    }
}
```

### Example 4: Deploying Winners

```php
use Jawabapp\RemoteConfig\Models\Winner;

// After analyzing experiment results, deploy the winner
$winner = Winner::create([
    'type' => 'onboarding',
    'platform' => 'ios',
    'country_code' => 'US',
    'language' => 'en',
    'content' => [
        'steps' => ['welcome', 'quick_setup'],
        'skip_enabled' => true,
    ],
    'is_active' => true,
]);

// Now all iOS/US/English users get this configuration
// Winners override experiments
```

### Example 4: QA Testing with Test Overrides

```php
use Jawabapp\RemoteConfig\Models\TestOverride;
use Jawabapp\RemoteConfig\Models\Flow;

// Create a test flow for QA
$qaFlow = Flow::create([
    'type' => 'feature',
    'content' => [
        'new_feature_x' => true,
        'debug_mode' => true,
    ],
    'is_active' => true,
]);

// Create test override for specific IP
TestOverride::create([
    'ip' => '192.168.1.100', // QA tester's IP
    'type' => 'feature',
    'flow_id' => $qaFlow->id,
]);

// QA tester at this IP will now see the test flow
// Test overrides have highest priority
```

### Example 5: Multi-Platform Targeting

```php
$experiment = Experiment::create([
    'name' => 'Premium Features Test',
    'type' => 'pricing',
    'platforms' => ['ios'], // iOS only
    'countries' => ['US', 'GB', 'CA'],
    'languages' => ['en'],
    'user_created_after_date' => '2024-01-01', // Only new users
    'is_active' => true,
]);

$premiumFlow = Flow::create([
    'type' => 'pricing',
    'content' => [
        'monthly_price' => 9.99,
        'annual_price' => 99.99,
        'trial_days' => 14,
    ],
    'is_active' => true,
]);

$experiment->flows()->attach($premiumFlow, ['ratio' => 100]);
```

## API Reference

### API Endpoints

#### `POST /api/config/issue` - Report Validation Issues

Report validation issues from client applications. Supports both single and multiple issues in one request.

**Single Issue:**
```bash
curl -X POST https://api.example.com/api/config/issue \
  -H "Authorization: Bearer token" \
  -H "Content-Type: application/json" \
  -d '{
    "path": "features",
    "invalid_value": [],
    "platform": "android",
    "type": "features",
    "error_message": "Invalid data type: expected Map, got List"
  }'

# Response:
{
  "success": true,
  "message": "Validation issue reported successfully",
  "data": {
    "id": 123,
    "path": "features",
    "invalid_value": [],
    "platform": "android",
    "type": "features",
    "error_message": "Invalid data type: expected Map, got List",
    "created_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

**Multiple Issues (Bulk):**
```bash
curl -X POST https://api.example.com/api/config/issue \
  -H "Authorization: Bearer token" \
  -H "Content-Type: application/json" \
  -d '[
    {
      "path": "features",
      "invalid_value": [],
      "platform": "android",
      "type": "features",
      "error_message": "Invalid data type: expected Map, got List"
    },
    {
      "path": "ui",
      "invalid_value": [],
      "platform": "android",
      "type": "ui",
      "error_message": "Invalid data type: expected Map, got List"
    }
  ]'

# Response:
{
  "success": true,
  "message": "2 validation issues reported successfully",
  "data": [
    {
      "id": 123,
      "path": "features",
      ...
    },
    {
      "id": 124,
      "path": "ui",
      ...
    }
  ]
}
```

### Helper Functions

#### `get_user_config($experimentable, string $type, array $attributes = [])`

Get configuration for a user with automatic experiment assignment.

```php
$config = get_user_config($user, 'onboarding', [
    'platform' => 'ios',
    'country' => 'SA',
    'language' => 'ar',
]);
```

#### `is_in_experiment($experimentable, string $experimentName): bool`

Check if user is assigned to an experiment.

```php
if (is_in_experiment($user, 'Quick Onboarding Test')) {
    // User is in this experiment
}
```

#### `experiment_variant($experimentable, string $type)`

Get the flow variant a user is assigned to.

```php
$variant = experiment_variant($user, 'onboarding');
echo $variant->content['steps']; // Array of steps
```

#### `experiment_stats(Experiment $experiment): array`

Get statistics for an experiment.

```php
$stats = experiment_stats($experiment);
// Returns assignment counts and percentages per variant
```

### Trait Methods (Experimentable)

#### `assignToExperiment(string $type, ?int $flowId = null, array $attributes = [])`

Manually assign user to an experiment.

```php
$assignment = $user->assignToExperiment('onboarding', null, [
    'platform' => 'ios',
    'country' => 'US',
    'language' => 'en',
]);
```

#### `confirmExperiment(string $experimentName, array $metadata = [])`

Confirm/track experiment completion.

```php
$user->confirmExperiment('Quick Onboarding Test', [
    'completed_steps' => 4,
    'time_spent' => 180,
    'converted' => true,
]);
```

#### `hasConfirmedExperiment(string $experimentName): bool`

Check if user confirmed an experiment.

```php
if ($user->hasConfirmedExperiment('Quick Onboarding Test')) {
    // User completed this experiment
}
```

### ConfigService Methods

```php
use Jawabapp\RemoteConfig\Services\ConfigService;

$configService = app(ConfigService::class);

// Get configuration with test override support
$config = $configService->getConfig(
    $user,
    'feature_flags',
    ['platform' => 'ios', 'country' => 'SA', 'language' => 'ar'],
    $request->ip() // For test overrides
);

// Get assignment statistics
$stats = $configService->getAssignmentStats($experiment);
```

### ExperimentService Methods

```php
use Jawabapp\RemoteConfig\Services\ExperimentService;

$experimentService = app(ExperimentService::class);

// Select flow variant for user (with ratio-based distribution)
$flow = $experimentService->selectFlow($experiment);

// Get experiment statistics
$stats = $experimentService->getExperimentStats($experiment);

// Clear experiment counters
$experimentService->clearExperimentCounters($experiment);
```

### TestOverride Methods

```php
use Jawabapp\RemoteConfig\Models\TestOverride;

// Create test override
TestOverride::create([
    'ip' => '192.168.1.1',
    'type' => 'onboarding',
    'flow_id' => 5,
]);

// Check if exists
$exists = TestOverride::exists('192.168.1.1', 'onboarding');

// Find by IP and type
$override = TestOverride::findByIpAndType('192.168.1.1', 'onboarding');

// Delete
TestOverride::deleteByIpAndType('192.168.1.1', 'onboarding');

// Get all for a type
$overrides = TestOverride::getAllForType('onboarding');
// Returns: ['192.168.1.1' => 5, '10.0.0.1' => 7]

// Get all
$all = TestOverride::all();

// Clear all
TestOverride::clear();
```

## Database Structure

The package creates 11 tables:

| Table | Purpose |
|-------|---------|
| `flows` | Configuration variants (JSON content) |
| `flow_logs` | Audit log for flow changes |
| `experiments` | A/B test definitions with targeting |
| `experiment_logs` | Audit log for experiment changes |
| `experiment_flow` | Pivot table (experiments ↔ flows with ratios) |
| `experiment_assignments` | User-to-experiment assignments (polymorphic) |
| `experiment_assignment_logs` | Historical assignment logs |
| `winners` | Deployed winning configurations |
| `winner_logs` | Audit log for winner changes |
| `confirmations` | User experiment confirmations/conversions |
| `validation_issues` | Client-reported configuration errors |

### Configuration Priority Order

1. **Test Override** (highest) - IP-based override for testing
2. **Winner** - Deployed winning variant for specific targeting
3. **Experiment** - Active A/B test assignment
4. **Base Flow** (lowest) - Default configuration

## Admin Panel

Access the admin panel at `/remote-config` (or your configured prefix):

- `/remote-config/flows` - Manage configuration variants
- `/remote-config/experiments` - Create and manage A/B tests
- `/remote-config/winners` - Deploy winning configurations
- `/remote-config/testing` - Manage test overrides for QA

The admin panel includes:
- JSON editor for configuration content
- Experiment flow attachment with ratio configuration
- Assignment statistics and analytics
- Audit logs with change tracking

## Testing

### Testing in Development

#### Option 1: Use Test Overrides

```php
// Set up test override for your IP
TestOverride::create([
    'ip' => request()->ip(),
    'type' => 'onboarding',
    'flow_id' => $testFlowId,
]);
```

#### Option 2: Use Helper Functions

```php
// In your tests
$config = get_user_config($testUser, 'onboarding', [
    'platform' => 'ios',
    'country' => 'US',
    'language' => 'en',
]);

$this->assertEquals(['steps' => ['welcome', 'profile']], $config);
```

### PHPUnit Testing

```php
use Jawabapp\RemoteConfig\Models\Experiment;
use Jawabapp\RemoteConfig\Models\Flow;
use Tests\TestCase;

class ExperimentTest extends TestCase
{
    public function test_user_gets_assigned_to_experiment()
    {
        // Create flows
        $flowA = Flow::factory()->create([
            'type' => 'test',
            'content' => ['variant' => 'A'],
        ]);

        $flowB = Flow::factory()->create([
            'type' => 'test',
            'content' => ['variant' => 'B'],
        ]);

        // Create experiment
        $experiment = Experiment::factory()->create([
            'type' => 'test',
            'platforms' => ['web'],
            'countries' => ['US'],
            'languages' => ['en'],
            'is_active' => true,
        ]);

        // Attach flows with 50/50 split
        $experiment->flows()->attach($flowA, ['ratio' => 50]);
        $experiment->flows()->attach($flowB, ['ratio' => 50]);

        // Test assignment
        $user = User::factory()->create([
            'platform' => 'web',
            'country_code' => 'US',
            'language' => 'en',
        ]);

        $config = get_user_config($user, 'test');

        $this->assertTrue(
            $config['variant'] === 'A' || $config['variant'] === 'B'
        );

        // Assignment should be sticky
        $config2 = get_user_config($user, 'test');
        $this->assertEquals($config, $config2);
    }

    public function test_winner_overrides_experiment()
    {
        $winner = Winner::create([
            'type' => 'test',
            'platform' => 'web',
            'country_code' => 'US',
            'language' => 'en',
            'content' => ['variant' => 'winner'],
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'platform' => 'web',
            'country_code' => 'US',
            'language' => 'en',
        ]);

        $config = get_user_config($user, 'test');

        $this->assertEquals('winner', $config['variant']);
    }
}
```

## Best Practices

### 1. Configuration Structure

Keep your configurations consistent:

```php
// Good: Structured, typed configuration
[
    'features' => [
        'new_checkout' => true,
        'dark_mode' => false,
    ],
    'limits' => [
        'max_uploads' => 10,
        'file_size_mb' => 5,
    ],
]

// Avoid: Flat, untyped configuration
[
    'setting1' => 'value',
    'setting2' => 123,
    'random_key' => true,
]
```

### 2. Experiment Naming

Use clear, descriptive experiment names:

```php
// Good
'Checkout Flow Redesign 2024 Q1'
'Premium Pricing Test - iOS Only'

// Avoid
'test1'
'experiment_abc'
```

### 3. Ratio Configuration

Always ensure ratios sum to a reasonable distribution:

```php
// 50/50 split
$experiment->flows()->attach($flowA, ['ratio' => 50]);
$experiment->flows()->attach($flowB, ['ratio' => 50]);

// 70/30 split
$experiment->flows()->attach($control, ['ratio' => 70]);
$experiment->flows()->attach($variant, ['ratio' => 30]);
```

### 4. Targeting

Be specific with targeting to avoid polluting data:

```php
$experiment = Experiment::create([
    'name' => 'iOS Premium Feature Test',
    'type' => 'pricing',
    'platforms' => ['ios'], // Specific platform
    'countries' => ['US', 'GB'], // Specific countries
    'languages' => ['en'], // Specific language
    'user_created_after_date' => '2024-01-01', // Only new users
    'is_active' => true,
]);
```

### 5. Monitoring and Cleanup

Regularly review and deactivate old experiments:

```php
// Deactivate experiment after conclusion
$experiment->update(['is_active' => false]);

// Deploy winner
$winner = Winner::create([
    'type' => $experiment->type,
    'platform' => 'ios',
    'country_code' => 'US',
    'language' => 'en',
    'content' => $winningFlow->content,
    'is_active' => true,
]);
```

### 6. Test Before Deploying

Always test configurations with test overrides before deploying:

```php
// Create test override for your IP
TestOverride::create([
    'ip' => '203.0.113.1',
    'type' => 'feature',
    'flow_id' => $newFeatureFlow->id,
]);

// Test in browser/app
// Then clear when done
TestOverride::clear();
```

## Environment Variables Reference

Add these to your `.env` file:

```env
# Enable/Disable System
REMOTE_CONFIG_ENABLED=true

# Testing
REMOTE_CONFIG_TESTING_ENABLED=true
REMOTE_CONFIG_TESTING_CACHE_STORE=redis

# User Filter
REMOTE_CONFIG_USER_CREATED_AFTER=2022-08-29

# Cache
REMOTE_CONFIG_CACHE_TTL=604800

# Routes
REMOTE_CONFIG_ROUTE_PREFIX=remote-config
REMOTE_CONFIG_API_PREFIX=api/config

# Database
REMOTE_CONFIG_TABLE_PREFIX=

# Redis
REMOTE_CONFIG_REDIS_CONNECTION=default

# User Model
REMOTE_CONFIG_USER_MODEL=App\Models\User
```

## Contributing

Contributions are welcome! Please submit pull requests or open issues on GitHub.

## License

MIT License

## Credits

Developed by [Jawab Team](https://jawab.app)

## Support

For issues and questions:
- GitHub Issues: [jawabapp/remote-config](https://github.com/jawabapp/remote-config)
- Email: support@jawab.app