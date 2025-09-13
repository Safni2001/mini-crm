<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display a listing of the resource with pagination.
     */
    public function index(): JsonResponse
    {
        $companies = Company::with([
            'employees' => function ($query) {
                $query->select('id', 'company_id', 'first_name', 'last_name', 'email');
            }
        ])->paginate(10);

        return response()->json([
            'data' => $companies->items(),
            'pagination' => [
                'current_page' => $companies->currentPage(),
                'first_page_url' => $companies->url(1),
                'from' => $companies->firstItem(),
                'last_page' => $companies->lastPage(),
                'last_page_url' => $companies->url($companies->lastPage()),
                'next_page_url' => $companies->nextPageUrl(),
                'path' => $companies->path(),
                'per_page' => $companies->perPage(),
                'prev_page_url' => $companies->previousPageUrl(),
                'to' => $companies->lastItem(),
                'total' => $companies->total(),
                'has_more_pages' => $companies->hasMorePages(),
                'on_first_page' => $companies->onFirstPage(),
            ],
            'message' => 'Companies retrieved successfully',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        if ($request->hasFile('logo')) {
            $validatedData['logo'] = $this->fileUploadService->uploadCompanyLogo($request->file('logo'));
        }

        $company = Company::create($validatedData);

        // Dispatch CompanyCreated event
        event(new \App\Events\CompanyCreated($company, $request->user()));

        return response()->json($company->load('employees'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company): JsonResponse
    {
        return response()->json($company->load('employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        // Store original company data before update
        $originalCompany = $company->replicate();

        $validatedData = $request->validated();

        if ($request->hasFile('logo')) {
            $validatedData['logo'] = $this->fileUploadService->uploadCompanyLogo(
                $request->file('logo'),
                $company->logo
            );
        }

        $company->update($validatedData);

        return response()->json($company->load('employees'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company): JsonResponse
    {
        // Delete logo file using the service
        if ($company->logo) {
            $this->fileUploadService->deleteFile($company->logo);
        }

        $company->delete();

        return response()->json(['message' => 'Company deleted successfully']);
    }
}
