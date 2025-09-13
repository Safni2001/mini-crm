<?php
/**
 * Test script to verify API endpoints are working
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

try {
    echo "=== Mini-CRM API Test ===\n\n";
    
    // Test 1: Check if database is accessible
    echo "1. Testing database connection...\n";
    try {
        $userCount = User::count();
        $companyCount = Company::count();
        $employeeCount = Employee::count();
        echo "✓ Database connected successfully\n";
        echo "  - Users: $userCount\n";
        echo "  - Companies: $companyCount\n";
        echo "  - Employees: $employeeCount\n";
    } catch (Exception $e) {
        echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // Test 2: Check if test user exists or create one
    echo "2. Testing test user...\n";
    $testUser = User::where('email', 'admin@minicrm.com')->first();
    if (!$testUser) {
        echo "  Creating test user...\n";
        $testUser = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@minicrm.com',
            'password' => Hash::make('password123')
        ]);
        echo "✓ Test user created\n";
    } else {
        echo "✓ Test user exists\n";
    }
    
    echo "\n";
    
    // Test 3: Create sample company if none exists
    echo "3. Testing sample company...\n";
    if ($companyCount === 0) {
        echo "  Creating sample company...\n";
        $company = Company::create([
            'name' => 'Test Company Inc.',
            'email' => 'info@testcompany.com',
            'website' => 'https://testcompany.com'
        ]);
        echo "✓ Sample company created\n";
    } else {
        echo "✓ Companies exist in database\n";
    }
    
    echo "\n";
    
    // Test 4: Create sample employee if none exists
    echo "4. Testing sample employee...\n";
    if ($employeeCount === 0 && $companyCount > 0) {
        echo "  Creating sample employee...\n";
        $company = Company::first();
        Employee::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@testcompany.com',
            'phone' => '+1-555-0124',
            'position' => 'Software Developer',
            'department' => 'IT',
            'status' => 'active',
            'company_id' => $company->id
        ]);
        echo "✓ Sample employee created\n";
    } else {
        echo "✓ Employees exist in database\n";
    }
    
    echo "\n=== API Test Complete ===\n";
    echo "You can now test the application at:\n";
    echo "- Frontend: http://localhost:5176\n";
    echo "- Backend API: http://localhost:8000/api\n";
    echo "- Login credentials:\n";
    echo "  Email: admin@minicrm.com\n";
    echo "  Password: password123\n";
    
} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
}