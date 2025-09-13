<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate a user
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_get_companies_list(): void
    {
        Company::factory(3)->create();

        $response = $this->getJson('/api/companies');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'name', 'email', 'website', 'logo_url', 'employees']
                    ],
                    'pagination',
                    'message'
                ]);
    }

    public function test_can_create_company(): void
    {
        Event::fake();

        $companyData = [
            'name' => 'Test Company',
            'email' => 'test@company.com',
            'website' => 'https://testcompany.com'
        ];

        $response = $this->postJson('/api/companies', $companyData);

        $response->assertStatus(201)
                ->assertJson([
                    'name' => 'Test Company',
                    'email' => 'test@company.com',
                    'website' => 'https://testcompany.com'
                ]);

        $this->assertDatabaseHas('companies', $companyData);

        Event::assertDispatched(\App\Events\CompanyCreated::class);
    }

    public function test_can_create_company_with_logo(): void
    {
        Storage::fake('public');

        $logo = UploadedFile::fake()->image('logo.jpg', 200, 200);

        $response = $this->postJson('/api/companies', [
            'name' => 'Test Company',
            'email' => 'test@company.com',
            'logo' => $logo
        ]);

        $response->assertStatus(201);

        $company = Company::first();
        $this->assertNotNull($company->logo);
        
        Storage::disk('public')->assertExists($company->logo);
    }

    public function test_company_creation_validation(): void
    {
        $response = $this->postJson('/api/companies', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    public function test_can_show_company(): void
    {
        $company = Company::factory()->create();

        $response = $this->getJson("/api/companies/{$company->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $company->id,
                    'name' => $company->name,
                    'email' => $company->email
                ]);
    }

    public function test_can_update_company(): void
    {
        Event::fake();
        
        $company = Company::factory()->create([
            'name' => 'Original Company',
            'email' => 'original@company.com'
        ]);

        $updateData = [
            'name' => 'Updated Company',
            'email' => 'updated@company.com',
            'website' => 'https://updated.com'
        ];

        $response = $this->putJson("/api/companies/{$company->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $company->id,
                    'name' => 'Updated Company',
                    'email' => 'updated@company.com'
                ]);

        $this->assertDatabaseHas('companies', $updateData);

        Event::assertDispatched(\App\Events\CompanyUpdated::class);
    }

    public function test_can_update_company_logo(): void
    {
        Storage::fake('public');
        
        $company = Company::factory()->create(['logo' => 'logos/old-logo.jpg']);
        
        // Create the old logo file
        Storage::disk('public')->put('logos/old-logo.jpg', 'old logo content');
        
        $newLogo = UploadedFile::fake()->image('new-logo.jpg', 200, 200);

        $response = $this->putJson("/api/companies/{$company->id}", [
            'name' => $company->name,
            'logo' => $newLogo
        ]);

        $response->assertStatus(200);

        $company->refresh();
        $this->assertNotEquals('logos/old-logo.jpg', $company->logo);
        
        Storage::disk('public')->assertExists($company->logo);
    }

    public function test_can_delete_company(): void
    {
        $company = Company::factory()->create();
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $response = $this->deleteJson("/api/companies/{$company->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Company deleted successfully'
                ]);

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }

    public function test_company_deletion_removes_logo_file(): void
    {
        Storage::fake('public');
        
        $company = Company::factory()->create(['logo' => 'logos/test-logo.jpg']);
        Storage::disk('public')->put('logos/test-logo.jpg', 'logo content');

        $response = $this->deleteJson("/api/companies/{$company->id}");

        $response->assertStatus(200);
        Storage::disk('public')->assertMissing('logos/test-logo.jpg');
    }

    public function test_companies_list_supports_pagination(): void
    {
        Company::factory(25)->create();

        $response = $this->getJson('/api/companies?per_page=10&page=2');

        $response->assertStatus(200)
                ->assertJsonPath('pagination.current_page', 2)
                ->assertJsonPath('pagination.per_page', 10)
                ->assertJsonCount(10, 'data');
    }

    
    public function test_unauthenticated_user_cannot_access_companies(): void
    {
        auth()->logout();

        $response = $this->getJson('/api/companies');

        $response->assertStatus(401);
    }

    public function test_company_with_invalid_id_returns_404(): void
    {
        $response = $this->getJson('/api/companies/999999');

        $response->assertStatus(404);
    }

    public function test_company_email_must_be_unique(): void
    {
        Company::factory()->create(['email' => 'existing@company.com']);

        $response = $this->postJson('/api/companies', [
            'name' => 'New Company',
            'email' => 'existing@company.com'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_company_logo_must_be_image(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->postJson('/api/companies', [
            'name' => 'Test Company',
            'logo' => $file
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['logo']);
    }

    public function test_companies_list_includes_employee_count(): void
    {
        $company = Company::factory()->create();
        Employee::factory(3)->create(['company_id' => $company->id]);

        $response = $this->getJson('/api/companies');

        $response->assertStatus(200)
                ->assertJsonPath('data.0.employees', fn($employees) => count($employees) === 3);
    }
}
