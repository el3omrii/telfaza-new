<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Source;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SourceController extends Controller
{
    private function hexToBase64url($hex) {
        $bytes = hex2bin($hex);
        $b64 = base64_encode($bytes);
        return str_replace(['+', '/'], ['-', '_'], rtrim($b64, '='));
    }

    private function base64urlToHex($b64url) {
        $b64 = str_replace(['-', '_'], ['+', '/'], $b64url);
        while (strlen($b64) % 4 !== 0) $b64 .= '=';
        $bytes = base64_decode($b64);
        return bin2hex($bytes);
    }
    public function index(): View
    {
        $sources = Source::with('channel')->orderByDesc('id')->paginate(20);

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
            'clearkeys' => 'nullable|string|max:4000',
        ]);

        // Process clearkeys: parse string, convert hex kid and key to base64url
        $clearkeysArray = [];
        if (!empty($data['clearkeys'])) {
            $lines = explode("\n", trim($data['clearkeys']));
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                [$kid, $key] = explode(':', $line, 2);
                $kid = trim($kid);
                $key = trim($key);
                if ($kid && $key) {
                    $clearkeysArray[$this->hexToBase64url($kid)] = $this->hexToBase64url($key);
                }
            }
        }
        $data['clearkeys'] = $clearkeysArray;

        $data['channel_id'] = $channel->id;
        Source::create($data);

        return redirect()->route('channels.show', $channel)
                         ->with('success', 'Source added.');
    }

    public function edit(Source $source): View
    {
        $channel = $source->channel;

        // Prepare clearkeys as string in hex format for the form
        $clearkeysString = '';
        if ($source->clearkeys) {
            foreach ($source->clearkeys as $kid => $key) {
                $clearkeysString .= $this->base64urlToHex($kid) . ':' . $this->base64urlToHex($key) . "\n";
            }
        }

        return view('sources.edit', compact('channel', 'source', 'clearkeysString'));
    }

    public function update(Request $request, Source $source): RedirectResponse
    {
        $data = $request->validate([
            'type'      => 'required|in:hls,dash,mp4',
            'link'      => 'nullable|url|max:2048',
            'drm'       => 'boolean',
            'clearkeys' => 'nullable|string|max:4000',
        ]);

        // Process clearkeys: parse string, convert hex keys to base64url
        $clearkeysArray = [];
        if (!empty($data['clearkeys'])) {
            $lines = explode("\n", trim($data['clearkeys']));
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                [$kid, $key] = explode(':', $line, 2);
                $kid = trim($kid);
                $key = trim($key);
                if ($kid && $key) {
                    $this->$clearkeysArray[$this->hexToBase64url($kid)] = $this->hexToBase64url($key);
                }
            }
        }
        $data['clearkeys'] = $clearkeysArray;

        $source->update($data);

        return redirect()->route('channels.show', $source->channel)
                         ->with('success', 'Source updated.');
    }

    public function destroy(Source $source): RedirectResponse
    {
        $source->delete();

        return redirect()->route('channels.show', $source->channel)
                         ->with('success', 'Source deleted.');
    }
}