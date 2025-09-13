<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_has_fillable_attributes(): void
    {
        $fillable = ['name', 'email', 'logo', 'website'];
        $company = new Company();

        $this->assertEquals($fillable, $company->getFillable());
    }

    public function test_company_has_many_employees(): void
    {
        $company = Company::factory()->create();
        $employees = Employee::factory(3)->create(['company_id' => $company->id]);

        $this->assertCount(3, $company->employees);
        $this->assertTrue($company->employees->contains($employees[0]));
    }

    public function test_company_logo_url_accessor_with_logo(): void
    {
        $company = Company::factory()->create(['logo' => 'logos/test-logo.jpg']);

        $expectedUrl = asset('storage/logos/test-logo.jpg');
        $this->assertEquals($expectedUrl, $company->logo_url);
    }

    public function test_company_logo_url_accessor_without_logo(): void
    {
        $company = Company::factory()->create(['logo' => null]);

        $this->assertNull($company->logo_url);
    }

    public function test_company_has_logo_method(): void
    {
        $companyWithLogo = Company::factory()->create(['logo' => 'logos/test.jpg']);
        $companyWithoutLogo = Company::factory()->create(['logo' => null]);

        $this->assertTrue($companyWithLogo->hasLogo());
        $this->assertFalse($companyWithoutLogo->hasLogo());
    }

    public function test_company_get_logo_path_method(): void
    {
        $company = Company::factory()->create(['logo' => 'logos/test.jpg']);

        $expectedPath = storage_path('app/public/logos/test.jpg');
        $this->assertEquals($expectedPath, $company->getLogoPath());
    }

    public function test_company_get_logo_path_returns_null_when_no_logo(): void
    {
        $company = Company::factory()->create(['logo' => null]);

        $this->assertNull($company->getLogoPath());
    }

    public function test_company_delete_logo_file_method(): void
    {
        $company = Company::factory()->create(['logo' => 'logos/test.jpg']);

        // Mock file system
        Storage::fake('public');
        Storage::disk('public')->put('logos/test.jpg', 'fake content');

        $this->assertTrue(Storage::disk('public')->exists('logos/test.jpg'));

        $company->deleteLogoFile();

        $this->assertFalse(Storage::disk('public')->exists('logos/test.jpg'));
    }

    public function test_company_appends_logo_url_to_array(): void
    {
        $company = Company::factory()->create(['logo' => 'logos/test.jpg']);
        $array = $company->toArray();

        $this->assertArrayHasKey('logo_url', $array);
        $this->assertEquals($company->logo_url, $array['logo_url']);
    }

    public function test_company_can_be_created_with_factory(): void
    {
        $company = Company::factory()->create([
            'name' => 'Test Company',
            'email' => 'test@company.com',
            'website' => 'https://testcompany.com'
        ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'Test Company',
            'email' => 'test@company.com',
            'website' => 'https://testcompany.com'
        ]);
    }

    public function test_company_name_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Company::create([
            'email' => 'test@company.com',
            'website' => 'https://testcompany.com'
        ]);
    }

    public function test_company_soft_deletes_related_employees(): void
    {
        $company = Company::factory()->create();
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $company->delete();

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }
}
