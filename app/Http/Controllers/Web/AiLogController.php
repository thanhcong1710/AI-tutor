<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AiChatLog;
use Illuminate\Http\Request;

class AiLogController extends Controller
{
    public function index()
    {
        $logs = AiChatLog::with('user')->latest()->paginate(20);
        return view('ai_logs.index', compact('logs'));
    }
}
