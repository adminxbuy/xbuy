<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::published();

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('subtitle', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $articles = $query->orderBy('published_at', 'desc')->paginate(9);

        // Featured article is the latest published one
        $featuredArticle = Article::published()->orderBy('published_at', 'desc')->first();

        // Categories list
        $categories = Article::published()->whereNotNull('category')->distinct()->pluck('category');

        return view('blog.index', compact('articles', 'featuredArticle', 'categories'));
    }

    public function show($slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();

        // If not published and user is not admin, deny
        if (!$article->is_published && (!auth()->check() || auth()->user()->role !== 'admin')) {
            abort(404);
        }

        // Increment view count securely (avoid page refreshes counting in same session if you want, but simple increment is standard)
        $article->increment('views_count');

        // Recent articles
        $recentArticles = Article::published()
            ->where('id', '!=', $article->id)
            ->orderBy('published_at', 'desc')
            ->limit(4)
            ->get();

        return view('blog.show', compact('article', 'recentArticles'));
    }
}
