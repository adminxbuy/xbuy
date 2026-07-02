<?php
 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ContentManagerController extends Controller
{
    /**
     * Display content management page.
     */
    public function index(Request $request)
    {
        // Auto-prune filesystem trash
        $trashDir = public_path('website_assets/trash');
        if (File::exists($trashDir)) {
            $trashTypes = ['images', 'pdfs', 'videos'];
            foreach ($trashTypes as $type) {
                $subTrashDir = $trashDir . '/' . $type;
                if (File::exists($subTrashDir)) {
                    $trashFiles = File::files($subTrashDir);
                    foreach ($trashFiles as $file) {
                        if ($file->getMTime() < now()->subDays(30)->getTimestamp()) {
                            File::delete($file->getPathname());
                        }
                    }
                }
            }
        }

        $types = ['images', 'pdfs', 'videos'];
        $content = [];

        foreach ($types as $type) {
            $dir = public_path('website_assets/' . $type);
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true, true);
            }

            $files = File::files($dir);
            $content[$type] = [];

            foreach ($files as $file) {
                $filename = $file->getFilename();
                $relativePath = '/website_assets/' . $type . '/' . $filename;
                $content[$type][] = [
                    'name' => $filename,
                    'url' => $relativePath,
                    'size' => round($file->getSize() / 1024, 2) . ' KB',
                    'updated_at' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }

            // Sort by updated_at desc
            usort($content[$type], function ($a, $b) {
                return strcmp($b['updated_at'], $a['updated_at']);
            });
        }

        // Fetch trashed content
        $trashedContent = [];
        foreach ($types as $type) {
            $dir = public_path('website_assets/trash/' . $type);
            $trashedContent[$type] = [];
            if (File::exists($dir)) {
                $files = File::files($dir);
                foreach ($files as $file) {
                    $filename = $file->getFilename();
                    $relativePath = '/website_assets/trash/' . $type . '/' . $filename;
                    
                    $originalName = $filename;
                    if (preg_match('/^(.*)_deleted_\d+\.(.*)$/i', $filename, $matches)) {
                        $originalName = $matches[1] . '.' . $matches[2];
                    }
                    
                    $trashedContent[$type][] = [
                        'name' => $filename,
                        'original_name' => $originalName,
                        'url' => $relativePath,
                        'size' => round($file->getSize() / 1024, 2) . ' KB',
                        'deleted_at' => date('Y-m-d H:i:s', $file->getMTime()),
                    ];
                }
            }
        }

        return view('admin.content', compact('content', 'trashedContent'));
    }

    /**
     * Upload dynamic content.
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:images,pdfs,videos',
            'file' => 'required|file|max:20480', // Max 20MB
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        $type = $request->input('type');
        $file = $request->file('file');

        // Validate extension by type
        $ext = strtolower($file->getClientOriginalExtension());
        if ($type === 'images' && !in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico'])) {
            return back()->with('error', 'Invalid image file extension.');
        } elseif ($type === 'pdfs' && $ext !== 'pdf') {
            return back()->with('error', 'Only PDF files are allowed.');
        } elseif ($type === 'videos' && !in_array($ext, ['mp4', 'webm', 'ogg', 'mov'])) {
            return back()->with('error', 'Invalid video file extension.');
        }

        $dir = public_path('website_assets/' . $type);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalName);
        $filename = $cleanName . '_' . time() . '.' . $ext;

        try {
            $file->move($dir, $filename);
        } catch (\Exception $e) {
            copy($file->getRealPath(), $dir . '/' . $filename);
            @unlink($file->getRealPath());
        }

        return back()->with('success', 'File uploaded successfully.');
    }

    /**
     * Delete content file.
     */
    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $relativePath = $request->input('path');

        if (strpos($relativePath, '..') !== false || strpos($relativePath, '/website_assets/') !== 0) {
            return back()->with('error', 'Unauthorized file path deletion request.');
        }

        $absolutePath = public_path($relativePath);

        if (File::exists($absolutePath)) {
            preg_match('/\/website_assets\/(images|pdfs|videos)\/(.*)$/i', $relativePath, $matches);
            if (count($matches) === 3) {
                $type = $matches[1];
                $filename = $matches[2];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
                
                $trashDir = public_path('website_assets/trash/' . $type);
                if (!File::exists($trashDir)) {
                    File::makeDirectory($trashDir, 0755, true, true);
                }
                
                $newFilename = $nameWithoutExt . '_deleted_' . time() . '.' . $ext;
                $newAbsolutePath = $trashDir . '/' . $newFilename;
                
                File::move($absolutePath, $newAbsolutePath);
                return back()->with('success', 'File moved to trash.');
            }
        }

        return back()->with('error', 'File not found on server.');
    }

    /**
     * Restore content file from trash.
     */
    public function restore(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $relativePath = $request->input('path');

        if (strpos($relativePath, '..') !== false || strpos($relativePath, '/website_assets/trash/') !== 0) {
            return back()->with('error', 'Unauthorized file restore request.');
        }

        $absolutePath = public_path($relativePath);

        if (File::exists($absolutePath)) {
            preg_match('/\/website_assets\/trash\/(images|pdfs|videos)\/(.*)$/i', $relativePath, $matches);
            if (count($matches) === 3) {
                $type = $matches[1];
                $filename = $matches[2];
                
                $originalName = $filename;
                if (preg_match('/^(.*)_deleted_\d+\.(.*)$/i', $filename, $fileMatches)) {
                    $originalName = $fileMatches[1] . '.' . $fileMatches[2];
                }
                
                $destDir = public_path('website_assets/' . $type);
                $destPath = $destDir . '/' . $originalName;
                
                File::move($absolutePath, $destPath);
                return back()->with('success', 'File restored successfully.');
            }
        }

        return back()->with('error', 'File not found in trash.');
    }

    /**
     * Permanent delete content file from trash.
     */
    public function forceDelete(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'Only the Superadmin can permanently delete items.');
        }

        $request->validate([
            'path' => 'required|string',
        ]);

        $relativePath = $request->input('path');

        if (strpos($relativePath, '..') !== false || strpos($relativePath, '/website_assets/trash/') !== 0) {
            return back()->with('error', 'Unauthorized file deletion request.');
        }

        $absolutePath = public_path($relativePath);

        if (File::exists($absolutePath)) {
            File::delete($absolutePath);
            return back()->with('success', 'File deleted permanently.');
        }

        return back()->with('error', 'File not found in trash.');
    }
}
