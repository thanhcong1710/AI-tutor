<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\LessonAssignment;

class StudentController extends Controller
{
    /**
     * Show student dashboard
     */
    public function dashboard()
    {
        $assignedLessons = LessonAssignment::where('student_id', Auth::id())
            ->with(['lesson', 'teacher:id,name'])
            ->latest()
            ->get();

        $botUsername = config('telegram.bot_username', 'your_bot');
        $botUsername = ltrim($botUsername, '@');

        // Gradient colors for course cards
        $gradients = [
            'from-blue-500 to-indigo-600',
            'from-purple-500 to-pink-600',
            'from-green-500 to-teal-600',
            'from-orange-500 to-red-600',
            'from-cyan-500 to-blue-600',
            'from-pink-500 to-rose-600',
        ];

        return view('student.dashboard', compact('assignedLessons', 'botUsername', 'gradients'));
    }
}
