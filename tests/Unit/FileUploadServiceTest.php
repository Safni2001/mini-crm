<?php

namespace Tests\Unit;

use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadServiceTest extends TestCase
{
    protected $fileUploadService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileUploadService = new FileUploadService();
    }

    public function test_upload_company_logo_successfully(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.jpg', 200, 200);

        $path = $this->fileUploadService->uploadCompanyLogo($file);

        $this->assertStringStartsWith('logos/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_upload_replaces_existing_logo(): void
    {
        Storage::fake('public');

        // Create old logo
        $oldPath = 'logos/old-logo.jpg';
        Storage::disk('public')->put($oldPath, 'old content');

        $newFile = UploadedFile::fake()->image('new-logo.jpg', 200, 200);

        $path = $this->fileUploadService->uploadCompanyLogo($newFile, $oldPath);

        $this->assertNotEquals($oldPath, $path);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($path);
    }

    public function test_delete_file_removes_from_storage(): void
    {
        Storage::fake('public');

        $filePath = 'logos/test-logo.jpg';
        Storage::disk('public')->put($filePath, 'test content');

        $result = $this->fileUploadService->deleteFile($filePath);

        $this->assertTrue($result);
        Storage::disk('public')->assertMissing($filePath);
    }

    public function test_delete_nonexistent_file_returns_false(): void
    {
        Storage::fake('public');

        $result = $this->fileUploadService->deleteFile('nonexistent/file.jpg');

        $this->assertFalse($result);
    }

    public function test_upload_invalid_file_type_throws_exception(): void
    {
        $this->expectException(\Exception::class);

        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $this->fileUploadService->uploadCompanyLogo($file);
    }

    public function test_upload_oversized_file_throws_exception(): void
    {
        $this->expectException(\Exception::class);

        $file = UploadedFile::fake()->image('large.jpg', 200, 200)->size(5000);

        $this->fileUploadService->uploadCompanyLogo($file);
    }
}
