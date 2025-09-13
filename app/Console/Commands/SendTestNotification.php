<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Events\CompanyCreated;
use App\Events\EmployeeCreated;
use Illuminate\Console\Command;

class SendTestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:test {type=company}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test notification emails (company or employee)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');

        if (!in_array($type, ['company', 'employee'])) {
            $this->error('Invalid type. Use "company" or "employee"');
            return 1;
        }

        // Get or create a test user
        $user = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );

        if ($type === 'company') {
            $this->sendCompanyTestNotification($user);
        } else {
            $this->sendEmployeeTestNotification($user);
        }

        return 0;
    }

    private function sendCompanyTestNotification(User $user)
    {
        // Create a test company
        $company = Company::create([
            'name' => 'Test Company ' . now()->format('Y-m-d H:i:s'),
            'email' => 'test@company.com',
            'website' => 'https://testcompany.com',
        ]);

        // Dispatch the event
        event(new CompanyCreated($company, $user));

        $this->info("Test company notification sent! Company: {$company->name}");
    }
}
