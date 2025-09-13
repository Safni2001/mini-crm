<?php

namespace Tests\Feature;

use App\Events\CompanyCreated;
use App\Events\CompanyUpdated;
use App\Events\EmployeeCreated;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\CompanyCreatedNotification;
use App\Notifications\CompanyUpdatedNotification;
use App\Notifications\EmployeeCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_company_created_event_sends_notification(): void
    {
        Notification::fake();

        $company = Company::factory()->create();

        // Dispatch the event manually
        event(new CompanyCreated($company, $this->user));

        // Assert notification was sent to all users
        Notification::assertSentTo(
            User::all(),
            CompanyCreatedNotification::class,
            function ($notification) use ($company) {
                return $notification->company->id === $company->id;
            }
        );
    }

    public function test_company_created_notification_contains_correct_data(): void
    {
        $company = Company::factory()->create([
            'name' => 'Test Company',
            'email' => 'test@company.com'
        ]);

        $notification = new CompanyCreatedNotification($company, $this->user);

        // Test database representation
        $data = $notification->toDatabase($this->user);

        $this->assertEquals($company->id, $data['company_id']);
        $this->assertEquals('Test Company', $data['company_name']);
        $this->assertEquals($this->user->id, $data['created_by_id']);
        $this->assertEquals('company_created', $data['action']);
    }

    public function test_notifications_are_sent_via_mail_and_database(): void
    {
        $company = Company::factory()->create();
        $notification = new CompanyCreatedNotification($company, $this->user);

        $channels = $notification->via($this->user);

        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    public function test_mail_notification_has_correct_subject_and_content(): void
    {
        Mail::fake();

        $company = Company::factory()->create(['name' => 'Test Company']);
        $notification = new CompanyCreatedNotification($company, $this->user);

        $mailMessage = $notification->toMail($this->user);

        $this->assertEquals('New Company Created: Test Company', $mailMessage->subject);
        $this->assertEquals('emails.company.created', $mailMessage->markdown);
    }

    public function test_creating_company_via_api_triggers_notification(): void
    {
        Notification::fake();

        $companyData = [
            'name' => 'API Test Company',
            'email' => 'api@company.com',
            'website' => 'https://apicompany.com'
        ];

        $response = $this->postJson('/api/companies', $companyData);

        $response->assertStatus(201);

        // Verify notification was sent
        Notification::assertSentTo(
            User::all(),
            CompanyCreatedNotification::class
        );
    }


    public function test_notification_sent_to_all_users(): void
    {
        Notification::fake();

        $company = Company::factory()->create();

        // Dispatch event
        event(new CompanyCreated($company, $this->user));

        // Should send notification to all users
        Notification::assertSentTo(
            User::all(),
            CompanyCreatedNotification::class
        );
    }

    public function test_notification_database_storage(): void
    {
        $company = Company::factory()->create(['name' => 'Database Test Company']);

        // Send notification
        $this->user->notify(new CompanyCreatedNotification($company, $this->user));

        // Check database storage
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->user->id,
            'notifiable_type' => User::class,
            'type' => CompanyCreatedNotification::class,
        ]);

        // Check notification data
        $notification = $this->user->notifications->first();
        $this->assertEquals('company_created', $notification->data['action']);
        $this->assertEquals('Database Test Company', $notification->data['company_name']);
    }

    public function test_notification_can_be_marked_as_read(): void
    {
        $company = Company::factory()->create();

        // Send notification
        $this->user->notify(new CompanyCreatedNotification($company, $this->user));

        // Get notification
        $notification = $this->user->unreadNotifications->first();
        $this->assertNull($notification->read_at);

        // Mark as read
        $notification->markAsRead();

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }
}
