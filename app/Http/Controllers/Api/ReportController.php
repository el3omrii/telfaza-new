<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function Store (Request $request) {
        $data = $request->validate([
            'issue_type' => 'required|string|max:255',
            'details'    => 'nullable|string|max:1000',
            'channel_id' => 'nullable|exists:channels,id',
        ]);

        $data['user_agent'] = $request->header('User-Agent');

        \App\Models\Report::create($data);

        return response()->json(['message' => 'Report submitted successfully.'], 201);
    }
}
