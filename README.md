# Remote Config - Laravel A/B Testing & Remote Configuration Package

A standalone Laravel package for A/B testing, remote configuration management, and feature experimentation. Built to be framework-agnostic and easily integrable into any Laravel project.

## Features

- ✅ **A/B Testing**: Create experiments with multiple variants and ratio-based distribution
- ✅ **Remote Configuration**: Manage app configurations remotely without deploying
- ✅ **Winner Deployment**: Deploy winning variants to specific user segments
- ✅ **Test Overrides**: IP-based testing for QA teams
- ✅ **Polymorphic User Support**: Works with any user model
- ✅ **Comprehensive Targeting**: Platform, country, and language targeting
- ✅ **Audit Logging**: Complete audit trail of all changes
- ✅ **Flexible Architecture**: Configurable table prefixes, routes, and middleware
- ✅ **Helper Functions**: Convenient helper functions for common operations

## Installation

### 1. Install via Composer

```bash
composer require jawabapp/remote-config
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --tag=remote-config-config
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. (Optional) Publish Views for Customization

```bash
php artisan vendor:publish --tag=remote-config-views
```

### 5. (Optional) Publish Assets

```bash
php artisan vendor:publish --tag=remote-config-assets
```

## Configuration

Edit `config/remote-config.php` to customize the package:

```php
return [
    // Enable/disable the entire system
    'enabled' => true,

    // Enable IP-based testing (requires Redis)
    'testing_enabled' => true,

    // Only users created after this date will be in experiments
    'user_created_after_date' => '2022-08-29',

    // Admin panel routes configuration
    'routes' => [
        'prefix' => 'remote-config',
        'middleware' => ['web', 'auth'],
    ],

    // API routes configuration
    'api_routes' => [
        'prefix' => 'api/config',
        'middleware' => ['api'],
    ],

    // User model that will participate in experiments
    'experimentable_model' => \App\Models\User::class,

    // Define flow types available
    'flow_types' => [
        'general' => 'General Configuration',
        'onboarding' => 'Onboarding Flow',
        // Add more types...
    ],

    // Targeting options
    'targeting' => [
        'platforms' => ['ios', 'android', 'web'],
        'countries' => ['SA', 'AE', 'US', ...],
        'languages' => ['ar', 'en', 'fr', ...],
    ],
];
```

## Setup

### Add Trait to User Model

Add the `Experimentable` trait to your User model (or any model that should participate in experiments):

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Jawabapp\RemoteConfig\Traits\Experimentable;

class User extends Authenticatable
{
    use Experimentable;

    // ... rest of your model
}
```

## Usage

### API Endpoints

#### Get Remote Configuration

```http
GET /api/config?type=default&platform=ios&country=SA&language=ar
```

**Response:**
```json
{
    "success": true,
    "data": {
        "feature_flags": {...},
        "ui_config": {...}
    },
    "meta": {
        "type": "default",
        "has_experiment": true,
        "experiment_id": 5,
        "flow_id": 12
    }
}
```

#### Confirm Experiment

```http
POST /api/config/confirm
Content-Type: application/json

{
    "experiment_name": "onboarding_v2",
    "metadata": {
        "completed_steps": 5,
        "time_spent": 120
    }
}
```

#### Report Validation Issue

```http
POST /api/config/issue
Content-Type: application/json

{
    "path": "feature_flags.new_checkout",
    "invalid_value": "invalid_string",
    "platform": "ios",
    "type": "type_error",
    "error_message": "Expected boolean, got string"
}
```

### Using Helper Functions

```php
// Get configuration for a user
$config = get_user_config($user, 'onboarding', [
    'platform' => 'ios',
    'country' => 'SA',
    'language' => 'ar'
]);

// Check if user is in an experiment
if (is_in_experiment($user, 'checkout_redesign')) {
    // User is in experiment
}

// Get the variant user is assigned to
$variant = experiment_variant($user, 'checkout_redesign');

// Get experiment statistics
$stats = experiment_stats($experiment);
```

### Using Trait Methods

```php
// Assign user to experiment
$assignment = $user->assignToExperiment('onboarding', null, [
    'platform' => 'ios',
    'country' => 'SA',
    'language' => 'ar'
]);

// Apply winner configuration
$config = $user->applyWinnerConfig($baseConfig, 'onboarding', [
    'platform' => 'ios',
    'country' => 'SA',
    'language' => 'ar'
]);

// Confirm experiment
$user->confirmExperiment('onboarding_v2', ['completed' => true]);

// Check if confirmed
if ($user->hasConfirmedExperiment('onboarding_v2')) {
    // User confirmed this experiment
}
```

## Database Structure

The package creates 11 tables:

| Table | Purpose |
|-------|---------|
| `flows` | Configuration variants |
| `flow_logs` | Audit log for flows |
| `experiments` | A/B test definitions |
| `experiment_logs` | Audit log for experiments |
| `experiment_flow` | Pivot table linking experiments to flows with ratios |
| `experiment_assignments` | User assignments to experiments (polymorphic) |
| `experiment_assignment_logs` | Historical assignment logs |
| `winners` | Deployed winning configurations |
| `winner_logs` | Audit log for winners |
| `confirmations` | User experiment confirmations |
| `validation_issues` | Reported configuration errors |

All tables support optional table prefix via config.

## Core Concepts

### Flows
Flows are JSON configuration variants. Each flow has:
- **Type**: Categorizes the configuration (e.g., 'onboarding', 'feature_flags')
- **Content**: JSON data containing the configuration
- **Status**: Active/inactive flag
- **Overwrite ID**: Allows multiple experiments on same base config

### Experiments
Experiments are A/B tests that compare multiple flows:
- **Name**: Human-readable experiment name
- **Targeting**: Platform, country, language filters
- **Flows**: Multiple flows with ratio distribution (e.g., 50/50 split)
- **Status**: Active/inactive
- **User Created After**: Only include users created after this date

### Assignments
When a user meets experiment criteria, an assignment is created:
- **Polymorphic User**: Works with any model
- **Sticky**: User always gets the same variant
- **Logged**: All assignments are logged for analysis

### Winners
When an experiment concludes, deploy the winner:
- **Target-Specific**: Deploy to specific platform/country/language
- **Priority**: Winners override experiments
- **Based on Flows**: Can be based on a winning flow

### Test Overrides
For QA testing (requires Redis):
- **IP-Based**: Testers get specific variants based on IP
- **Temporary**: Stored in Redis cache
- **Override Everything**: Highest priority

## Priority Order

Configuration is applied in this order (highest to lowest):

1. **Test Override** (if testing enabled and IP matches)
2. **Winner** (if exists for user's platform/country/language)
3. **Experiment** (if user is assigned to active experiment)
4. **Base Flow** (default configuration)

## Advanced Usage

### Custom Experiment Logic

```php
use Jawabapp\RemoteConfig\Services\ConfigService;
use Jawabapp\RemoteConfig\Services\ExperimentService;

$configService = app(ConfigService::class);
$experimentService = app(ExperimentService::class);

// Get configuration with custom logic
$config = $configService->getConfig(
    $user,
    'feature_flags',
    ['platform' => 'ios', 'country' => 'SA', 'language' => 'ar'],
    $testIp,
    $testWinnerId
);

// Get assignment stats
$stats = $configService->getAssignmentStats($experiment);

// Remove assignment
$configService->removeAssignment($user, $experimentId);

// Select flow from experiment
$flow = $experimentService->selectFlow($experiment);

// Get experiment statistics
$stats = $experimentService->getExperimentStats($experiment);

// Clear counters
$experimentService->clearExperimentCounters($experiment);
```

### Working with Test Overrides

```php
use Jawabapp\RemoteConfig\Models\TestOverride;
use Jawabapp\RemoteConfig\Models\Flow;

// Create test override
$testOverride = new TestOverride('192.168.1.1', 'onboarding');
$testOverride->set($flowId, 3600); // TTL in seconds

// Check if override exists
if ($testOverride->exists()) {
    $content = $testOverride->getContent();
}

// Delete override
$testOverride->delete();

// Get all overrides for a type
$overrides = TestOverride::getAllForType('onboarding');
```

## Admin Panel

Admin routes are available at `/remote-config` (configurable):

- `/remote-config/flows` - Manage configuration variants
- `/remote-config/experiments` - Manage A/B tests
- `/remote-config/winners` - Manage deployed winners
- `/remote-config/testing` - Manage test overrides

**Note**: Admin controllers and views are not yet implemented. You can create custom controllers extending the base Controller class.

## Events & Observers

The package automatically logs all changes via model observers:

- `FlowObserver` - Logs flow changes
- `ExperimentObserver` - Logs experiment changes
- `WinnerObserver` - Logs winner changes
- `ExperimentAssignmentObserver` - Logs user assignments

Disable logging in config:
```php
'audit_logging' => [
    'enabled' => false,
    'log_assignments' => false,
],
```

## Testing

### Testing Experiments in Development

1. **Use Test Overrides**: Set up IP-based overrides via the testing endpoint
2. **Use Query Parameters**: Pass variant via URL (e.g., `?experiment=variant_a`)
3. **Use Test Winner ID**: Pass `test_winner_id` in API request

### PHPUnit Testing

```php
use Jawabapp\RemoteConfig\Models\Experiment;
use Jawabapp\RemoteConfig\Models\Flow;

public function test_user_gets_experiment_variant()
{
    // Create experiment
    $experiment = Experiment::factory()->create([
        'type' => 'test',
        'platforms' => ['ios'],
        'countries' => ['US'],
        'languages' => ['en'],
        'is_active' => true,
    ]);

    // Create flows
    $flowA = Flow::factory()->create(['type' => 'test']);
    $flowB = Flow::factory()->create(['type' => 'test']);

    // Attach to experiment
    $experiment->flows()->attach($flowA, ['ratio' => 50]);
    $experiment->flows()->attach($flowB, ['ratio' => 50]);

    // Test assignment
    $user = User::factory()->create();
    $assignment = $user->assignToExperiment('test');

    $this->assertNotNull($assignment);
    $this->assertEquals($experiment->id, $assignment->experiment_id);
}
```

## Requirements

- PHP >= 8.1
- Laravel >= 10.0
- Redis (optional, for test overrides)
- jfcherng/php-diff >= 6.0 (for audit diffs)

## Roadmap / TODO

The package core is functional but still needs:

- [ ] Admin Panel Controllers (Flow, Experiment, Winner, Testing)
- [ ] Admin Panel Views with JSONEditor integration
- [ ] Admin Layout (Tailwind + Alpine.js)
- [ ] Request Validation Classes
- [ ] Event System (ExperimentAssigned, WinnerDeployed, etc.)
- [ ] Artisan Commands (clear cache, stats, etc.)
- [ ] Test Suite
- [ ] Documentation Site

## Contributing

Contributions are welcome! Please submit pull requests or open issues.

## License

MIT License

## Credits

Developed by Jawab Team based on internal experimentation system.

---

## Quick Start Example

```php
// 1. Add trait to User model
class User extends Authenticatable
{
    use \Jawabapp\RemoteConfig\Traits\Experimentable;
}

// 2. Create a base flow
$baseFlow = Flow::create([
    'type' => 'onboarding',
    'content' => [
        'steps' => ['welcome', 'profile', 'preferences'],
        'theme' => 'light',
    ],
    'is_active' => true,
]);

// 3. Create variant flows
$variantA = Flow::create([
    'type' => 'onboarding',
    'content' => [
        'steps' => ['welcome', 'tutorial', 'profile', 'preferences'],
        'theme' => 'light',
    ],
    'is_active' => true,
]);

$variantB = Flow::create([
    'type' => 'onboarding',
    'content' => [
        'steps' => ['welcome', 'quick_start'],
        'theme' => 'dark',
    ],
    'is_active' => true,
]);

// 4. Create experiment
$experiment = Experiment::create([
    'name' => 'Onboarding Redesign 2024',
    'type' => 'onboarding',
    'platforms' => ['ios', 'android'],
    'countries' => ['US', 'SA'],
    'languages' => ['en', 'ar'],
    'is_active' => true,
]);

// 5. Attach variants with 50/50 split
$experiment->flows()->attach($variantA, ['ratio' => 50]);
$experiment->flows()->attach($variantB, ['ratio' => 50]);

// 6. Get config for user (automatically assigns to experiment)
$config = get_user_config($user, 'onboarding', [
    'platform' => 'ios',
    'country' => 'US',
    'language' => 'en',
]);

// 7. User sees either variant A or B configuration
// Assignment is sticky - same user always gets same variant

// 8. Track completion
$user->confirmExperiment('Onboarding Redesign 2024', [
    'completed_at' => now(),
    'time_spent' => 120,
]);

// 9. After analysis, deploy winner
$winner = Winner::create([
    'type' => 'onboarding',
    'platform' => 'ios',
    'country_code' => 'US',
    'language' => 'en',
    'content' => $variantA->content,
    'flow_id' => $variantA->id,
    'is_active' => true,
]);

// Now all iOS/US/English users get variant A, regardless of experiment
```

That's it! You now have a fully functional A/B testing system.
