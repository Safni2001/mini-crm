<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Company;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Employee::with(['company:id,name,email']);

        // Filter by company if company_id is provided
        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $employees = $query->paginate(10);

        return response()->json([
            'data' => $employees->items(),
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'first_page_url' => $employees->url(1),
                'from' => $employees->firstItem(),
                'last_page' => $employees->lastPage(),
                'last_page_url' => $employees->url($employees->lastPage()),
                'next_page_url' => $employees->nextPageUrl(),
                'path' => $employees->path(),
                'per_page' => $employees->perPage(),
                'prev_page_url' => $employees->previousPageUrl(),
                'to' => $employees->lastItem(),
                'total' => $employees->total(),
                'has_more_pages' => $employees->hasMorePages(),
                'on_first_page' => $employees->onFirstPage(),
            ],
            'message' => 'Employees retrieved successfully',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $employee = Employee::create($validatedData);

        return response()->json($employee->load('company'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee): JsonResponse
    {
        return response()->json($employee->load('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $validatedData = $request->validated();

        $employee->update($validatedData);

        return response()->json($employee->load('company'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully']);
    }
}
