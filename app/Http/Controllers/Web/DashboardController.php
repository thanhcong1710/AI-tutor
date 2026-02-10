<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Lesson;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Basic Stats
        $stats = [
            'tokens_total' => $user->total_tokens_used ?? 0,
            'tokens_month' => $user->tokens_used_this_month ?? 0,
            'role' => $user->role,
        ];

        // Role-specific data
        if ($user->role === 'admin' || $user->role === 'teacher') {
            $stats['total_students'] = User::where('role', 'student')->count();
            $stats['total_lessons'] = Lesson::count();
            $recentLessons = Lesson::latest()->take(5)->get();
        } else {
            // Student: Recent or Suggested Lessons
            // Start simple: just recent lessons
            $recentLessons = Lesson::latest()->take(3)->get();
        }

        return view('dashboard', compact('stats', 'recentLessons'));
    }
}
