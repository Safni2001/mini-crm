<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmployeeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate a user
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_get_employees_list(): void
    {
        $company = Company::factory()->create();
        Employee::factory(3)->create(['company_id' => $company->id]);

        $response = $this->getJson('/api/employees');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'first_name', 'last_name', 'email', 'phone', 'company']
                    ],
                    'pagination',
                    'message'
                ]);
    }

    public function test_can_create_employee(): void
    {
        Event::fake();

        $company = Company::factory()->create();
        
        $employeeData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@company.com',
            'phone' => '+1-234-567-8900',
            'company_id' => $company->id
        ];

        $response = $this->postJson('/api/employees', $employeeData);

        $response->assertStatus(201)
                ->assertJson([
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@company.com',
                    'company_id' => $company->id
                ]);

        $this->assertDatabaseHas('employees', $employeeData);

        Event::assertDispatched(\App\Events\EmployeeCreated::class);
    }

    public function test_employee_creation_validation(): void
    {
        $response = $this->postJson('/api/employees', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['first_name', 'last_name', 'company_id']);
    }

    public function test_can_show_employee(): void
    {
        $employee = Employee::factory()->create();

        $response = $this->getJson("/api/employees/{$employee->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $employee->id,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'email' => $employee->email
                ]);
    }

    public function test_can_update_employee(): void
    {
        $employee = Employee::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@company.com'
        ]);

        $updateData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@company.com',
            'phone' => '+1-987-654-3210'
        ];

        $response = $this->putJson("/api/employees/{$employee->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $employee->id,
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'email' => 'jane.smith@company.com'
                ]);

        $this->assertDatabaseHas('employees', $updateData);
    }

    public function test_can_delete_employee(): void
    {
        $employee = Employee::factory()->create();

        $response = $this->deleteJson("/api/employees/{$employee->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Employee deleted successfully'
                ]);

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }

    public function test_employees_list_supports_pagination(): void
    {
        $company = Company::factory()->create();
        Employee::factory(25)->create(['company_id' => $company->id]);

        $response = $this->getJson('/api/employees?per_page=10&page=2');

        $response->assertStatus(200)
                ->assertJsonPath('pagination.current_page', 2)
                ->assertJsonPath('pagination.per_page', 10)
                ->assertJsonCount(10, 'data');
    }

    public function test_can_filter_employees_by_company(): void
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        
        Employee::factory(3)->create(['company_id' => $company1->id]);
        Employee::factory(2)->create(['company_id' => $company2->id]);

        $response = $this->getJson("/api/employees?company_id={$company1->id}");

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data');
    }

    public function test_unauthenticated_user_cannot_access_employees(): void
    {
        auth()->logout();

        $response = $this->getJson('/api/employees');

        $response->assertStatus(401);
    }

    public function test_employee_with_invalid_id_returns_404(): void
    {
        $response = $this->getJson('/api/employees/999999');

        $response->assertStatus(404);
    }

    public function test_employee_email_must_be_unique(): void
    {
        $company = Company::factory()->create();
        Employee::factory()->create(['email' => 'existing@company.com']);

        $response = $this->postJson('/api/employees', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'existing@company.com',
            'company_id' => $company->id
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_employee_must_belong_to_existing_company(): void
    {
        $response = $this->postJson('/api/employees', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_id' => 999999
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['company_id']);
    }

    public function test_employees_list_includes_company_info(): void
    {
        $company = Company::factory()->create(['name' => 'Test Company']);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $response = $this->getJson('/api/employees');

        $response->assertStatus(200)
                ->assertJsonPath('data.0.company.name', 'Test Company');
    }
}
