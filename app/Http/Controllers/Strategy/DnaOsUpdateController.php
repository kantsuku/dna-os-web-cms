<?php

namespace App\Http\Controllers\Strategy;

use App\Http\Controllers\Controller;
use App\Models\OrchestrationLog;
use Illuminate\Http\Request;

class DnaOsUpdateController extends Controller
{
    public function index(Request $request)
    {
        $updates = OrchestrationLog::where('event_type', 'dna_change_detected')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('strategy.dna-updates.index', compact('updates'));
    }

    public function show(OrchestrationLog $log)
    {
        return view('strategy.dna-updates.show', compact('log'));
    }
}
