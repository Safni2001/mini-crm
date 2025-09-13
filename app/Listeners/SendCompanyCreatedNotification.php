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
        $event->user->notify(new CompanyCreatedNotification($event->company, $event->user));
    }
}
