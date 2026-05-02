<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        $tags = Tag::withCount('channels')->orderBy('name')->paginate(20);

        return view('tags.index', compact('tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
        ]);

        Tag::create($data);

        return redirect()->route('tags.index')->with('success', 'Tag created.');
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->tag_id . ',tag_id',
        ]);

        $tag->update($data);

        return redirect()->route('tags.index')->with('success', 'Tag updated.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->route('tags.index')->with('success', 'Tag deleted.');
    }

    /**
     * AJAX quick-create called from channel create/edit form.
     * Returns JSON: { tag_id, name }
     */
    public function quickCreate(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tag = Tag::firstOrCreate(['name' => trim($data['name'])]);

        return response()->json([
            'tag_id' => $tag->tag_id,
            'name'   => $tag->name,
        ]);
    }
}