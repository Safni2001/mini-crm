<?php

namespace App\Listeners;

use App\Events\CompanyCreated;
use App\Models\User;
use App\Notifications\CompanyCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendCompanyCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CompanyCreated $event): void
    {
        // Get all admin users to notify
        $adminUsers = User::get();

        // If no admin users exist, notify all users (fallback)
        if ($adminUsers->isEmpty()) {
            $adminUsers = User::all();
        }

        // Send notification to each admin user
        foreach ($adminUsers as $admin) {
            $admin->notify(new CompanyCreatedNotification($event->company, $event->user));
        }

        // Optionally send notification to all users (for demonstration)
        // Uncomment the line below if you want to notify all users
        // Notification::send(User::all(), new CompanyCreatedNotification($event->company, $event->user));
    }
}
