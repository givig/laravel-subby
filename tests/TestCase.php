<?php

namespace Bpuig\Subby\Tests;

use Bpuig\Subby\Models\Plan;
use Bpuig\Subby\Models\PlanFeature;
use Bpuig\Subby\SubbyServiceProvider;
use Bpuig\Subby\Tests\Database\Factories\UserFactory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected $testUser;
    protected $testPlanBasic;
    protected $testPlanFeatures;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->setupDefaultTestData();
    }


    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('subby', [
            // Database Tables
            'tables' => [
                'plans' => 'plans',
                'plan_features' => 'plan_features',
                'plan_subscriptions' => 'plan_subscriptions',
                'plan_subscription_usage' => 'plan_subscription_usage',
            ],
            // Models
            'models' => [
                'plan' => \Bpuig\Subby\Models\Plan::class,
                'plan_feature' => \Bpuig\Subby\Models\PlanFeature::class,
                'plan_subscription' => \Bpuig\Subby\Models\PlanSubscription::class,
                'plan_subscription_usage' => \Bpuig\Subby\Models\PlanSubscriptionUsage::class,
            ]
        ]);

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }


    /**
     * add the package provider
     *
     * @param $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [SubbyServiceProvider::class];
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase()
    {
        // import the CreatePostsTable class from the migration
        include_once __DIR__ . '/Database/migrations/create_users_table.php.stub';
        include_once __DIR__ . '/../database/migrations/create_plans_table.php.stub';
        include_once __DIR__ . '/../database/migrations/create_plan_features_table.php.stub';
        include_once __DIR__ . '/../database/migrations/create_plan_subscriptions_table.php.stub';
        include_once __DIR__ . '/../database/migrations/create_plan_subscription_usage_table.php.stub';

        $this->artisan('migrate:refresh');

        // run the up() method of that migration class
        (new \CreateUsersTable)->up();
        (new \CreatePlansTable)->up();
        (new \CreatePlanFeaturesTable)->up();
        (new \CreatePlanSubscriptionsTable)->up();
        (new \CreatePlanSubscriptionUsageTable)->up();
    }

    /**
     * Set up supervised data for testing
     */
    protected function setupDefaultTestData()
    {
        // Create test user
        $this->testUser = UserFactory::new()->create(['id' => 1, 'email' => 'test@user.com']);

        // Create a Basic plan
        $this->testPlanBasic = Plan::create([
            'tag' => 'basic',
            'name' => 'Basic Plan',
            'description' => 'Basic plan description',
            'is_active' => true,
            'price' => 9.99,
            'currency' => 'EUR'
        ]);

        $this->testPlanBasic->refresh();

        // Add some features to the Basic Plan
        $this->testPlanBasic->features()->saveMany([
            new PlanFeature(['tag' => 'social_profiles', 'name' => 'Social profiles available', 'value' => 3, 'sort_order' => 1]),
            new PlanFeature(['tag' => 'posts_per_social_profile', 'name' => 'Scheduled posts per profile', 'value' => 30, 'sort_order' => 10, 'resettable_period' => 1, 'resettable_interval' => 'month']),
            new PlanFeature(['tag' => 'analytics', 'name' => 'Analytics', 'value' => true, 'sort_order' => 15])
        ]);

        // Subscribe test user to plan
        $this->testUser->newSubscription('main', $this->testPlanBasic, 'Main subscription');
    }
}