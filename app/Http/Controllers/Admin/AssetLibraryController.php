<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AssetLibraryController extends Controller
{
    /**
     * Get a list of all images in the public website assets directory.
     */
    public function getImages()
    {
        $directory = public_path('website_assets/images');
        
        if (!File::exists($directory)) {
            return response()->json([]);
        }

        $files = File::files($directory);
        $images = [];

        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico'])) {
                $relativePath = '/website_assets/images/' . $file->getFilename();
                $images[] = [
                    'name' => $file->getFilename(),
                    'url' => $relativePath,
                    'size' => round($file->getSize() / 1024, 2) . ' KB',
                ];
            }
        }

        return response()->json($images);
    }
}
