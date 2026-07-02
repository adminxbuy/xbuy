<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ArticleApiController extends Controller
{
    /**
     * Public: Get published articles list.
     */
    public function index(Request $request): JsonResponse
    {
        $articles = Article::published()
            ->with('author')
            ->orderBy('published_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Articles retrieved successfully',
            'data' => $articles
        ]);
    }

    /**
     * Public: Show published article by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $article = Article::published()
            ->with('author')
            ->where('slug', $slug)
            ->firstOrFail();

        $article->increment('views_count');

        return response()->json([
            'success' => true,
            'message' => 'Article details retrieved successfully',
            'data' => $article
        ]);
    }

    /**
     * Admin: List all articles (including drafts).
     */
    public function adminIndex(Request $request): JsonResponse
    {
        if (!$request->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $query = Article::with('author');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $articles = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Admin articles retrieved successfully',
            'data' => $articles
        ]);
    }

    /**
     * Admin: Create a new article.
     */
    public function adminStore(Request $request): JsonResponse
    {
        if (!$request->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'status' => 'nullable|in:draft,published',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $status = $request->input('status', 'draft');
        $publishedAt = $status === 'published' ? now() : null;

        $article = Article::create([
            'title' => $request->input('title'),
            'slug' => $request->input('slug') ? Str::slug($request->input('slug')) : Str::slug($request->input('title')),
            'content' => $request->input('content'),
            'excerpt' => $request->input('excerpt'),
            'featured_image' => $request->input('featured_image'),
            'author_id' => $request->user()->id,
            'status' => $status,
            'published_at' => $publishedAt,
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'tags' => $request->input('tags', []),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Article created successfully',
            'data' => $article
        ], 201);
    }

    /**
     * Admin: Update article.
     */
    public function adminUpdate(int $id, Request $request): JsonResponse
    {
        if (!$request->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $article = Article::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug,' . $id,
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'status' => 'nullable|in:draft,published',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = [
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'excerpt' => $request->input('excerpt'),
            'featured_image' => $request->input('featured_image'),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'tags' => $request->input('tags', []),
        ];

        if ($request->filled('slug')) {
            $data['slug'] = Str::slug($request->input('slug'));
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            $data['status'] = $status;
            if ($status === 'published' && !$article->published_at) {
                $data['published_at'] = now();
            }
        }

        $article->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Article updated successfully',
            'data' => $article
        ]);
    }

    /**
     * Admin: Delete article.
     */
    public function adminDestroy(int $id, Request $request): JsonResponse
    {
        if (!$request->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $article = Article::findOrFail($id);
        $article->delete();

        return response()->json([
            'success' => true,
            'message' => 'Article deleted successfully'
        ]);
    }
}
