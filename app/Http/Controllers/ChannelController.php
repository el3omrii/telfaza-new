<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Channel;
use App\Models\Country;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChannelController extends Controller
{
    public function index(Request $request): View
    {
        $query = Channel::with(['country', 'categories', 'tags'])
            ->withCount('sources');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.category_id', $request->category));
        }

        if ($request->filled('country')) {
            $query->where('country_id', $request->country);
        }

        $channels   = $query->orderByDesc('views')->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $countries  = Country::orderBy('name')->get();

        return view('channels.index', compact('channels', 'categories', 'countries'));
    }

    public function create(): View
    {
        $countries  = Country::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $tags       = Tag::orderBy('name')->get();

        return view('channels.create', compact('countries', 'categories', 'tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo'        => 'nullable|string|max:255',
            'image'       => 'nullable|string|max:255',
            'country_id'  => 'nullable|exists:countries,country_id',
            'categories'  => 'nullable|array',
            'categories.*'=> 'exists:categories,category_id',
            'tags'        => 'nullable|array',
            'tags.*'      => 'exists:tags,tag_id',
        ]);

        $channel = Channel::create($data);
        $channel->categories()->sync($request->input('categories', []));
        $channel->tags()->sync($request->input('tags', []));

        return redirect()->route('channels.show', $channel)
                         ->with('success', 'Channel created successfully.');
    }

    public function show(Channel $channel): View
    {
        $channel->load(['country', 'categories', 'tags', 'sources']);
        $channel->incrementViews();

        return view('channels.show', compact('channel'));
    }

    public function edit(Channel $channel): View
    {
        $countries  = Country::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $tags       = Tag::orderBy('name')->get();

        return view('channels.edit', compact('channel', 'countries', 'categories', 'tags'));
    }

    public function update(Request $request, Channel $channel): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo'        => 'nullable|string|max:255',
            'image'       => 'nullable|string|max:255',
            'country_id'  => 'nullable|exists:countries,country_id',
            'categories'  => 'nullable|array',
            'categories.*'=> 'exists:categories,category_id',
            'tags'        => 'nullable|array',
            'tags.*'      => 'exists:tags,tag_id',
        ]);

        $channel->update($data);
        $channel->categories()->sync($request->input('categories', []));
        $channel->tags()->sync($request->input('tags', []));

        return redirect()->route('channels.show', $channel)
                         ->with('success', 'Channel updated successfully.');
    }

    public function destroy(Channel $channel): RedirectResponse
    {
        $channel->delete();

        return redirect()->route('channels.index')
                         ->with('success', 'Channel deleted.');
    }
}