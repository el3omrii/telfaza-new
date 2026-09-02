<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Channel;
use App\Models\Report;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(): View
    {
        $stats = [
            'channels'          => Channel::count(),
            'publishedChannels' => Channel::where('published', true)->count(),
            'sources'           => Source::count(),
            'enabledSources'    => Source::where('enabled', true)->count(),
            'categories'        => Category::count(),
            'tags'              => Tag::count(),
            'totalViews'        => (int) Channel::sum('views'),
            'pendingReports'    => Report::where('treated', false)->count(),
        ];

        return view('dashboard.index', $stats);
    }
}
