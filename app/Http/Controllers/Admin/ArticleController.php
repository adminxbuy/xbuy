<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('subtitle', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_published', $request->input('status') === 'published');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        Article::onlyTrashed()->where('deleted_at', '<', now()->subDays(30))->forceDelete();
        $articles = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = Article::whereNotNull('category')->distinct()->pluck('category');
        $trashedArticles = Article::onlyTrashed()->get();

        return view('admin.articles.index', compact('articles', 'categories', 'trashedArticles'));
    }

    public function create()
    {
        return view('admin.articles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug',
            'subtitle' => 'nullable|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'cover_image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'cover_image_url' => 'nullable|url',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'published_at' => 'nullable|date',
        ]);

        $slug = $request->filled('slug') ? Str::slug($request->input('slug')) : Str::slug($request->input('title'));

        // Handle cover image
        $coverImage = null;
        if ($request->hasFile('cover_image_file')) {
            $file = $request->file('cover_image_file');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/articles'), $filename);
            $coverImage = '/uploads/articles/' . $filename;
        } elseif ($request->filled('cover_image_url')) {
            $coverImage = $request->input('cover_image_url');
        }

        $isPublished = $request->has('is_published');
        $publishedAt = $request->filled('published_at') 
            ? $request->input('published_at') 
            : ($isPublished ? now() : null);

        Article::create([
            'title' => $request->input('title'),
            'slug' => $slug,
            'subtitle' => $request->input('subtitle'),
            'content' => $request->input('content'),
            'category' => $request->input('category'),
            'cover_image' => $coverImage,
            'seo_title' => $request->input('seo_title'),
            'seo_description' => $request->input('seo_description'),
            'is_published' => $isPublished,
            'published_at' => $publishedAt,
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'Article created successfully.');
    }

    public function edit($id)
    {
        $article = Article::findOrFail($id);
        return view('admin.articles.edit', compact('article'));
    }

    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:articles,slug,' . $article->id,
            'subtitle' => 'nullable|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'cover_image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'cover_image_url' => 'nullable|url',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'published_at' => 'nullable|date',
        ]);

        $coverImage = $article->cover_image;
        if ($request->hasFile('cover_image_file')) {
            $file = $request->file('cover_image_file');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/articles'), $filename);
            $coverImage = '/uploads/articles/' . $filename;
        } elseif ($request->filled('cover_image_url')) {
            $coverImage = $request->input('cover_image_url');
        }

        $isPublished = $request->has('is_published');
        $publishedAt = $request->filled('published_at') 
            ? $request->input('published_at') 
            : ($isPublished ? ($article->published_at ?? now()) : null);

        $article->update([
            'title' => $request->input('title'),
            'slug' => Str::slug($request->input('slug')),
            'subtitle' => $request->input('subtitle'),
            'content' => $request->input('content'),
            'category' => $request->input('category'),
            'cover_image' => $coverImage,
            'seo_title' => $request->input('seo_title'),
            'seo_description' => $request->input('seo_description'),
            'is_published' => $isPublished,
            'published_at' => $publishedAt,
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'Article updated successfully.');
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Article deleted and sent to Trash.');
    }

    public function toggleStatus($id)
    {
        $article = Article::findOrFail($id);
        $article->is_published = !$article->is_published;
        if ($article->is_published && !$article->published_at) {
            $article->published_at = now();
        }
        $article->save();

        return response()->json([
            'success' => true,
            'is_published' => $article->is_published,
            'message' => 'Article status updated successfully.'
        ]);
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No articles selected.');
        }

        switch ($action) {
            case 'publish':
                Article::whereIn('id', $ids)->update([
                    'is_published' => true,
                    'published_at' => now()
                ]);
                $msg = 'Selected articles published successfully.';
                break;
            case 'draft':
                Article::whereIn('id', $ids)->update([
                    'is_published' => false
                ]);
                $msg = 'Selected articles moved to draft.';
                break;
            case 'delete':
                Article::whereIn('id', $ids)->delete();
                $msg = 'Selected articles deleted (soft delete).';
                break;
            default:
                return redirect()->back()->with('error', 'Invalid action selected.');
        }

        return redirect()->back()->with('success', $msg);
    }
}
