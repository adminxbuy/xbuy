<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display a list of active pages (slug + title).
     */
    public function index(): JsonResponse
    {
        $pages = Page::where('is_active', true)
            ->select(['title', 'slug', 'meta_title', 'meta_description', 'is_protected', 'updated_at'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pages,
        ]);
    }

    /**
     * Display the specified page by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $page = Page::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $page,
        ]);
    }

    /**
     * Get sitemap policy pages (slug + updated_at).
     */
    public function sitemap(): JsonResponse
    {
        $pages = Page::where('is_active', true)
            ->select(['slug', 'updated_at'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pages,
        ]);
    }
}
