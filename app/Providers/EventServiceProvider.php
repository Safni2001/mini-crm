<?php

namespace App\Providers;

use App\Events\CompanyCreated;
use App\Events\CompanyUpdated;
use App\Events\EmployeeCreated;
use App\Listeners\SendCompanyCreatedNotification;
use App\Listeners\SendCompanyUpdatedNotification;
use App\Listeners\SendEmployeeCreatedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        CompanyCreated::class => [
            SendCompanyCreatedNotification::class,
        ],
        
        CompanyUpdated::class => [
            SendCompanyUpdatedNotification::class,
        ],
        
        EmployeeCreated::class => [
            SendEmployeeCreatedNotification::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        parent::register();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
