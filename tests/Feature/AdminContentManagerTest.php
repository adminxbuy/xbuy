<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminContentManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed base settings to prevent missing settings errors
        \App\Models\SiteSetting::create([
            'key' => 'commission_rates',
            'value' => json_encode(['gpu' => 5.0]),
            'type' => 'json',
            'group' => 'rates',
        ]);
        
        \App\Models\SiteSetting::create([
            'key' => 'testing_windows',
            'value' => json_encode(['gpu' => 3]),
            'type' => 'json',
            'group' => 'policy',
        ]);
    }

    public function test_guests_cannot_access_content_manager()
    {
        $this->get('/admin/content')->assertRedirect('/admin/login');
        $this->post('/admin/content/upload', [])->assertRedirect('/admin/login');
        $this->delete('/admin/content/delete', [])->assertRedirect('/admin/login');
    }

    public function test_regular_users_cannot_access_content_manager()
    {
        $user = User::factory()->create(['role' => 'buyer', 'phone' => '8888888888']);

        $this->actingAs($user)->get('/admin/content')->assertRedirect('/admin/login');
        $this->actingAs($user)->post('/admin/content/upload', [])->assertRedirect('/admin/login');
        $this->actingAs($user)->delete('/admin/content/delete', [])->assertRedirect('/admin/login');
    }

    public function test_admin_can_load_content_manager_page()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);

        $response = $this->actingAs($admin)->get('/admin/content');
        $response->assertStatus(200);
        $response->assertSee('Asset &amp; Content Manager', false);
    }

    public function test_admin_can_upload_and_delete_assets()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);

        // Create virtual asset directories if not exist
        $imagesDir = public_path('website_assets/images');
        $pdfsDir = public_path('website_assets/pdfs');
        $videosDir = public_path('website_assets/videos');
        File::ensureDirectoryExists($imagesDir);
        File::ensureDirectoryExists($pdfsDir);
        File::ensureDirectoryExists($videosDir);

        // 1. Upload mock image
        $imageFile = UploadedFile::fake()->image('test_logo.png');
        $response = $this->actingAs($admin)->post('/admin/content/upload', [
            'type' => 'images',
            'file' => $imageFile,
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success', 'File uploaded successfully.');

        // 2. Upload mock PDF
        $pdfFile = UploadedFile::fake()->create('test_guide.pdf', 100, 'application/pdf');
        $response = $this->actingAs($admin)->post('/admin/content/upload', [
            'type' => 'pdfs',
            'file' => $pdfFile,
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success', 'File uploaded successfully.');

        // Find the uploaded file in directory starting with test_logo_
        $uploadedImages = File::files($imagesDir);
        $testImageFile = null;
        foreach ($uploadedImages as $f) {
            if (str_starts_with($f->getFilename(), 'test_logo_')) {
                $testImageFile = $f;
                break;
            }
        }
        $this->assertNotNull($testImageFile, "Test uploaded image not found.");
        $uploadedFileName = $testImageFile->getFilename();
        $uploadedRelativePath = '/website_assets/images/' . $uploadedFileName;

        // 3. Delete uploaded file
        $deleteResponse = $this->actingAs($admin)->delete('/admin/content/delete', [
            'path' => $uploadedRelativePath,
        ]);
        $deleteResponse->assertRedirect();
        $deleteResponse->assertSessionHas('success', 'File moved to trash.');
        $this->assertFileDoesNotExist(public_path($uploadedRelativePath));

        // Clean up test PDF as well
        $uploadedPdfs = File::files($pdfsDir);
        foreach ($uploadedPdfs as $f) {
            if (str_starts_with($f->getFilename(), 'test_guide_')) {
                @unlink($f->getRealPath());
            }
        }
    }

    public function test_admin_cannot_delete_files_outside_website_assets()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);

        // Attempt directory traversal deletion
        $response = $this->actingAs($admin)->delete('/admin/content/delete', [
            'path' => '/website_assets/images/../../index.php',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Unauthorized file path deletion request.');
    }
}
