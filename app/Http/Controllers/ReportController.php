<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $reports = Report::with('channel')->orderByDesc('created_at')->paginate(20);

        return view('reports.index', compact('reports'));
    }

    public function edit(Report $report): View
    {
        $report->load('channel');

        return view('reports.edit', compact('report'));
    }

    public function update(Request $request, Report $report): RedirectResponse
    {
        $validated = $request->validate([
            'issue_type' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string'],
            'treated' => ['nullable', 'boolean'],
        ]);

        $report->update([
            ...$validated,
            'treated' => (bool) $request->boolean('treated'),
        ]);

        return redirect()->route('reports.index')->with('success', 'Report updated successfully.');
    }

    public function destroy(Report $report): RedirectResponse
    {
        $report->delete();

        return redirect()->route('reports.index')->with('success', 'Report removed successfully.');
    }
}
