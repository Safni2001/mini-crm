<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_has_fillable_attributes(): void
    {
        $fillable = ['first_name', 'last_name', 'company_id', 'email', 'phone'];
        $employee = new Employee();
        
        $this->assertEquals($fillable, $employee->getFillable());
    }

    public function test_employee_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $this->assertInstanceOf(Company::class, $employee->company);
        $this->assertEquals($company->id, $employee->company->id);
        $this->assertEquals($company->name, $employee->company->name);
    }

    public function test_employee_can_exist_without_company(): void
    {
        $employee = Employee::factory()->withoutCompany()->create();

        $this->assertNull($employee->company);
        $this->assertNull($employee->company_id);
    }

    public function test_employee_full_name_accessor(): void
    {
        $employee = Employee::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $this->assertEquals('John Doe', $employee->full_name);
    }

    public function test_employee_can_be_created_with_factory(): void
    {
        $company = Company::factory()->create();
        $employee = Employee::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@company.com',
            'phone' => '+1234567890',
            'company_id' => $company->id
        ]);

        $this->assertDatabaseHas('employees', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@company.com',
            'phone' => '+1234567890',
            'company_id' => $company->id
        ]);
    }

    public function test_employee_first_name_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Employee::create([
            'last_name' => 'Doe',
            'email' => 'john@company.com'
        ]);
    }

    public function test_employee_last_name_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Employee::create([
            'first_name' => 'John',
            'email' => 'john@company.com'
        ]);
    }

    public function test_employee_email_can_be_null(): void
    {
        $employee = Employee::factory()->create(['email' => null]);
        
        $this->assertNull($employee->email);
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'email' => null
        ]);
    }

    public function test_employee_phone_can_be_null(): void
    {
        $employee = Employee::factory()->create(['phone' => null]);
        
        $this->assertNull($employee->phone);
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'phone' => null
        ]);
    }

    public function test_employee_has_timestamps(): void
    {
        $employee = Employee::factory()->create();
        
        $this->assertNotNull($employee->created_at);
        $this->assertNotNull($employee->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $employee->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $employee->updated_at);
    }

    public function test_employee_company_relationship_uses_foreign_key(): void
    {
        $company = Company::factory()->create();
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        // Test the relationship is properly defined
        $this->assertEquals('company_id', $employee->company()->getForeignKeyName());
        $this->assertEquals('id', $employee->company()->getOwnerKeyName());
    }

    public function test_employee_can_be_updated(): void
    {
        $employee = Employee::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $employee->update([
            'first_name' => 'Jane',
            'email' => 'jane@updated.com'
        ]);

        $this->assertEquals('Jane', $employee->first_name);
        $this->assertEquals('jane@updated.com', $employee->email);
        $this->assertEquals('Doe', $employee->last_name); // Should remain unchanged
    }

    public function test_employee_can_be_deleted(): void
    {
        $employee = Employee::factory()->create();
        $employeeId = $employee->id;

        $employee->delete();

        $this->assertDatabaseMissing('employees', ['id' => $employeeId]);
    }

    public function test_employee_deletion_does_not_affect_company(): void
    {
        $company = Company::factory()->create();
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $employee->delete();

        $this->assertDatabaseHas('companies', ['id' => $company->id]);
        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }
}
