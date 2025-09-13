<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Company $company;
    public User $createdBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Company $company, User $createdBy)
    {
        $this->company = $company;
        $this->createdBy = $createdBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Company Created: ' . $this->company->name)
            ->markdown('emails.company.created', [
                'notifiable' => $notifiable,
                'company' => $this->company,
                'createdBy' => $this->createdBy
            ]);
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'company_id' => $this->company->id,
            'company_name' => $this->company->name,
            'company_email' => $this->company->email,
            'created_by_id' => $this->createdBy->id,
            'created_by_name' => $this->createdBy->name,
            'action' => 'company_created',
            'message' => 'New company "' . $this->company->name . '" was created by ' . $this->createdBy->name,
            'created_at' => $this->company->created_at->toISOString(),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
