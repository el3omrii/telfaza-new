<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Channel;
use App\Models\Country;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $request->category));
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

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    private function storeImage($file, string $folder, string $channelName, string $suffix): string
    {
        $slug      = str($channelName)->slug();
        $unique    = substr(md5(uniqid('', true)), 0, 8);
        $extension = $file->extension();
        $filename  = "{$slug}-{$suffix}-{$unique}.{$extension}";

        return $file->storeAs($folder, $filename, 'uploads');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'logo'                => 'nullable|image|mimes:jpeg,png,webp,svg|max:2048',
            'image'               => 'nullable|image|mimes:jpeg,png,webp|max:4096',
            'country_id'          => 'nullable|exists:countries,id',
            'language'            => 'nullable|string|max:100',
            'epgid'               => 'nullable|string|max:10',
            'featured'            => 'nullable|boolean',
            'quality'             => 'nullable|in:4K,1080p,720p,480p,360p',
            'categories'          => 'nullable|array',
            'categories.*'        => 'exists:categories,id',
            'tags'                => 'nullable|array',
            'tags.*'              => 'exists:tags,id',
            'new_tags'            => 'nullable|string',
            'sources'             => 'nullable|array',
            'sources.*.type'      => 'required_with:sources|in:hls,dash,mp4',
            'sources.*.link'      => 'nullable|url|max:2048',
            'sources.*.drm'       => 'nullable|boolean',
            'sources.*.clearkeys' => 'nullable|string|max:4000',
        ]);

        $channel = Channel::create([
            'name'        => $request->name,
            'description' => $request->description,
            'country_id'  => $request->country_id,
            'language'    => $request->language,
            'epgid'       => $request->epgid,
            'quality'     => $request->quality,
            'featured'    => $request->boolean('featured'),
            'logo'        => $request->hasFile('logo')
                                ? $this->storeImage($request->file('logo'), 'channels/logos', $request->name, 'logo')
                                : null,
            'image'       => $request->hasFile('image')
                                ? $this->storeImage($request->file('image'), 'channels/images', $request->name, 'image')
                                : null,
        ]);

        $channel->categories()->sync($request->input('categories', []));

        // Resolve tag IDs — existing + newly typed ones
        $tagIds = $request->input('tags', []);
        if ($request->filled('new_tags')) {
            foreach (array_filter(array_map('trim', explode(',', $request->new_tags))) as $tagName) {
                $tag      = Tag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
        }
        $channel->tags()->sync(array_unique($tagIds));

        // Create inline sources
        foreach ($request->input('sources', []) as $src) {
            if (empty($src['type'])) continue;
            $channel->sources()->create([
                'type'      => $src['type'],
                'link'      => $src['link']      ?? null,
                'drm'       => !empty($src['drm']),
                'clearkeys' => $src['clearkeys'] ?? null,
            ]);
        }

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
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo'        => 'nullable|image|mimes:jpeg,png,webp,svg|max:2048',
            'image'       => 'nullable|image|mimes:jpeg,png,webp|max:4096',
            'country_id'  => 'nullable|exists:countries,id',
            'language'    => 'nullable|string|max:100',
            'epgid'       => 'nullable|string|max:10',
            'quality'     => 'nullable|in:4K,1080p,720p,480p,360p',
            'featured'    => 'nullable|boolean',
            'categories'  => 'nullable|array',
            'categories.*'=> 'exists:categories,id',
            'tags'        => 'nullable|array',
            'tags.*'      => 'exists:tags,id',
        ]);

        $data = [
            'name'        => $request->name,
            'description' => $request->description,
            'country_id'  => $request->country_id,
            'language'    => $request->language,
            'epgid'       => $request->epgid,
            'quality'     => $request->quality,
            'featured'    => $request->boolean('featured'),
        ];

        // Replace logo only if a new file was uploaded; delete the old one
        if ($request->hasFile('logo')) {
            if ($channel->logo) {
                Storage::disk('uploads')->delete($channel->logo);
            }
            $data['logo'] = $this->storeImage($request->file('logo'), 'channels/logos', $request->name, 'logo');
        }

        // Replace image only if a new file was uploaded; delete the old one
        if ($request->hasFile('image')) {
            if ($channel->image) {
                Storage::disk('uploads')->delete($channel->image);
            }
            $data['image'] = $this->storeImage($request->file('image'), 'channels/images', $request->name, 'image');
        }

        // Allow explicitly clearing logo/image via hidden checkbox
        if ($request->boolean('remove_logo') && $channel->logo) {
            Storage::disk('uploads')->delete($channel->logo);
            $data['logo'] = null;
        }

        if ($request->boolean('remove_image') && $channel->image) {
            Storage::disk('uploads')->delete($channel->image);
            $data['image'] = null;
        }

        $channel->update($data);
        $channel->categories()->sync($request->input('categories', []));
        $channel->tags()->sync($request->input('tags', []));

        return redirect()->route('channels.show', $channel)
                         ->with('success', 'Channel updated successfully.');
    }

    public function destroy(Channel $channel): RedirectResponse
    {
        // Clean up stored files before deleting the record
        if ($channel->logo)  Storage::disk('uploads')->delete($channel->logo);
        if ($channel->image) Storage::disk('uploads')->delete($channel->image);

        $channel->delete();

        return redirect()->route('channels.index')
                         ->with('success', 'Channel deleted.');
    }
}