<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Display a listing of the pages.
     */
    public function index()
    {
        // Auto-prune categories and pages deleted > 30 days ago
        PageCategory::onlyTrashed()->where('deleted_at', '<', now()->subDays(30))->forceDelete();
        Page::onlyTrashed()->where('deleted_at', '<', now()->subDays(30))->forceDelete();

        $pages = Page::with(['lastEditedBy', 'category'])->orderBy('sort_order')->orderBy('title')->get();
        $categories = PageCategory::orderBy('sort_order')->orderBy('name')->get();
        
        $trashedCategories = PageCategory::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
        $trashedPages = Page::onlyTrashed()->with('category')->orderBy('deleted_at', 'desc')->get();

        return view('admin.pages.index', compact('pages', 'categories', 'trashedCategories', 'trashedPages'));
    }

    /**
     * Store a newly created page in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:page_categories,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $slug = $request->filled('slug') 
            ? Str::slug($request->input('slug')) 
            : Str::slug($request->input('title'));

        $page = Page::create([
            'category_id' => $request->input('category_id'),
            'title' => $request->input('title'),
            'slug' => $slug,
            'content' => $request->input('content'),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'is_active' => $request->has('is_active'),
            'is_protected' => false,
            'last_edited_by' => auth()->id(),
            'last_edited_at' => now(),
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    /**
     * Show the form for editing the specified page.
     */
    public function edit(int $id)
    {
        $page = Page::findOrFail($id);
        $categories = PageCategory::orderBy('name')->get();
        return view('admin.pages.edit', compact('page', 'categories'));
    }

    /**
     * Update the specified page in storage.
     */
    public function update(Request $request, int $id)
    {
        $page = Page::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug,' . $page->id,
            'content' => 'required|string',
            'category_id' => 'nullable|exists:page_categories,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $slug = $page->is_protected ? $page->slug : Str::slug($request->input('slug'));

        $page->update([
            'category_id' => $request->input('category_id'),
            'title' => $request->input('title'),
            'slug' => $slug,
            'content' => $request->input('content'),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'is_active' => $request->has('is_active'),
            'last_edited_by' => auth()->id(),
            'last_edited_at' => now(),
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    /**
     * Remove the specified page from storage.
     */
    public function destroy(int $id)
    {
        $page = Page::findOrFail($id);

        if ($page->is_protected) {
            return redirect()->route('admin.pages.index')->with('error', 'Protected core pages cannot be deleted.');
        }

        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted successfully.');
    }

    /**
     * Store a newly created category.
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:page_categories,name',
        ]);

        PageCategory::create([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Parent category created successfully.');
    }

    /**
     * Delete a category.
     */
    public function destroyCategory(int $id)
    {
        $category = PageCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Parent category deleted successfully.');
    }

    /**
     * Reorder parent categories.
     */
    public function reorderCategories(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        foreach ($request->input('ids') as $index => $id) {
            PageCategory::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function reorderPages(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'page_id' => 'nullable|exists:pages,id',
            'category_id' => 'nullable',
        ]);

        if ($request->filled('page_id')) {
            $catId = $request->input('category_id');
            if ($catId === 'null' || $catId === '') {
                $catId = null;
            }
            Page::where('id', $request->input('page_id'))->update([
                'category_id' => $catId
            ]);
        }

        foreach ($request->input('ids') as $index => $id) {
            Page::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Restore a soft-deleted category.
     */
    public function restoreCategory(int $id)
    {
        $category = PageCategory::onlyTrashed()->findOrFail($id);
        $category->restore();

        return redirect()->route('admin.pages.index')->with('success', 'Parent category and its pages restored successfully.');
    }

    /**
     * Restore a soft-deleted page.
     */
    public function restorePage(int $id)
    {
        $page = Page::onlyTrashed()->findOrFail($id);
        $page->restore();

        return redirect()->route('admin.pages.index')->with('success', 'Page restored successfully.');
    }

    /**
     * Permanently delete a category.
     */
    public function forceDeleteCategory(int $id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.pages.index')->with('error', 'Only the Superadmin can permanently delete items.');
        }

        $category = PageCategory::onlyTrashed()->findOrFail($id);
        $category->forceDelete();

        return redirect()->route('admin.pages.index')->with('success', 'Parent category permanently deleted.');
    }

    /**
     * Permanently delete a page.
     */
    public function forceDeletePage(int $id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.pages.index')->with('error', 'Only the Superadmin can permanently delete items.');
        }

        $page = Page::onlyTrashed()->findOrFail($id);
        $page->forceDelete();

        return redirect()->route('admin.pages.index')->with('success', 'Page permanently deleted.');
    }
}
