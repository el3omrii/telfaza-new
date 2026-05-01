<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('channels')->orderBy('name')->paginate(20);

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:500',
            'color'       => 'nullable|string|max:7',
        ]);

        Category::create($data);

        return redirect()->route('categories.index')
                         ->with('success', 'Category created.');
    }

    public function show(Category $category): View
    {
        $channels = $category->channels()->with('country')->paginate(15);

        return view('categories.show', compact('category', 'channels'));
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name,' . $category->category_id . ',category_id',
            'description' => 'nullable|string|max:500',
            'color'       => 'nullable|string|max:7',
        ]);

        $category->update($data);

        return redirect()->route('categories.index')
                         ->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('categories.index')
                         ->with('success', 'Category deleted.');
    }
}