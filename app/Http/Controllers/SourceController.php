<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Source;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SourceController extends Controller
{
    public function index(): View
    {
        $sources = Source::with('channel')->orderByDesc('source_id')->paginate(20);

        return view('sources.index', compact('sources'));
    }

    public function create(Channel $channel): View
    {
        return view('sources.create', compact('channel'));
    }

    public function store(Request $request, Channel $channel): RedirectResponse
    {
        $data = $request->validate([
            'type'      => 'required|in:hls,dash,mp4',
            'link'      => 'nullable|url|max:2048',
            'drm'       => 'boolean',
            'clearkeys' => 'nullable|integer',
        ]);

        $data['channel_id'] = $channel->channel_id;
        Source::create($data);

        return redirect()->route('channels.show', $channel)
                         ->with('success', 'Source added.');
    }

    public function edit(Channel $channel, Source $source): View
    {
        return view('sources.edit', compact('channel', 'source'));
    }

    public function update(Request $request, Channel $channel, Source $source): RedirectResponse
    {
        $data = $request->validate([
            'type'      => 'required|in:hls,dash,mp4',
            'link'      => 'nullable|url|max:2048',
            'drm'       => 'boolean',
            'clearkeys' => 'nullable|integer',
        ]);

        $source->update($data);

        return redirect()->route('channels.show', $channel)
                         ->with('success', 'Source updated.');
    }

    public function destroy(Channel $channel, Source $source): RedirectResponse
    {
        $source->delete();

        return redirect()->route('channels.show', $channel)
                         ->with('success', 'Source deleted.');
    }
}