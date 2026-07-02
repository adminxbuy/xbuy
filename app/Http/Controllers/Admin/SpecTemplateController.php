<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SpecTemplate;
use Illuminate\Http\Request;

class SpecTemplateController extends Controller
{
    /**
     * Display a listing of the spec templates grouped by category.
     */
    public function index()
    {
        $categories = Category::parentOnly()->get();
        // Eager load spec templates
        $specTemplates = SpecTemplate::orderBy('sort_order')->get()->groupBy('category_id');
        return view('admin.spec-templates', compact('categories', 'specTemplates'));
    }

    /**
     * Store a newly created spec template in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'spec_key' => 'required|string|max:255',
            'spec_label' => 'required|string|max:255',
            'spec_type' => 'required|in:text,number,select,boolean',
            'spec_unit' => 'nullable|string|max:255',
            'options' => 'nullable|string', // comma-separated strings to be converted to JSON array
            'is_required' => 'nullable|boolean',
            'is_highlighted' => 'nullable|boolean',
        ]);

        $options = null;
        if ($request->input('spec_type') === 'select' && $request->input('options')) {
            $options = array_map('trim', explode(',', $request->input('options')));
        }

        SpecTemplate::create([
            'category_id' => $request->input('category_id'),
            'spec_key' => $request->input('spec_key'),
            'spec_label' => $request->input('spec_label'),
            'spec_type' => $request->input('spec_type'),
            'spec_unit' => $request->input('spec_unit'),
            'options' => $options,
            'is_required' => $request->boolean('is_required'),
            'is_highlighted' => $request->boolean('is_highlighted'),
            'sort_order' => SpecTemplate::where('category_id', $request->input('category_id'))->count(),
        ]);

        return back()->with('success', 'Specification field added successfully.');
    }

    /**
     * Update the specified spec template in storage.
     */
    public function update(Request $request, $id)
    {
        $template = SpecTemplate::findOrFail($id);

        $request->validate([
            'spec_label' => 'required|string|max:255',
            'spec_type' => 'required|in:text,number,select,boolean',
            'spec_unit' => 'nullable|string|max:255',
            'options' => 'nullable|string', // comma-separated strings
            'is_required' => 'nullable|boolean',
            'is_highlighted' => 'nullable|boolean',
        ]);

        $options = null;
        if ($request->input('spec_type') === 'select' && $request->input('options')) {
            $inputOptions = $request->input('options');
            if (is_array($inputOptions)) {
                $options = $inputOptions;
            } else {
                $options = array_map('trim', explode(',', $inputOptions));
            }
        }

        $template->update([
            'spec_label' => $request->input('spec_label'),
            'spec_type' => $request->input('spec_type'),
            'spec_unit' => $request->input('spec_unit'),
            'options' => $options,
            'is_required' => $request->boolean('is_required'),
            'is_highlighted' => $request->boolean('is_highlighted'),
        ]);

        return back()->with('success', 'Specification field updated successfully.');
    }

    /**
     * Remove the specified spec template from storage.
     */
    public function destroy($id)
    {
        $template = SpecTemplate::findOrFail($id);
        $template->delete();

        return back()->with('success', 'Specification field deleted successfully.');
    }
}
