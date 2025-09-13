<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class CreateTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-test-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test data for Mini-CRM application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating test data for Mini-CRM...');

        // Create test user
        $user = User::firstOrCreate(
            ['email' => 'admin@minicrm.com'],
            [
                'name' => 'Test Admin',
                'password' => Hash::make('password123')
            ]
        );
        $this->info('✓ Test user created/updated: ' . $user->email);

        // Create test company
        $company = Company::firstOrCreate(
            ['name' => 'Test Company Inc.'],
            [
                'email' => 'info@testcompany.com',
                'website' => 'https://testcompany.com'
            ]
        );
        $this->info('✓ Test company created/updated: ' . $company->name);

        // Create test employee
        $employee = Employee::firstOrCreate(
            ['email' => 'john.doe@testcompany.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+1-555-0124',
                'position' => 'Software Developer',
                'department' => 'IT',
                'status' => 'active',
                'company_id' => $company->id
            ]
        );
        $this->info('✓ Test employee created/updated: ' . $employee->first_name . ' ' . $employee->last_name);

        $this->info('Test data creation complete!');
        $this->info('Login credentials:');
        $this->info('Email: admin@minicrm.com');
        $this->info('Password: password123');
        
        return 0;
    }
}
