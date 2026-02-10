<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Lesson::latest();

        if ($user->role === 'student') {
            $query->where('status', 'published');
        }

        $lessons = $query->paginate(10);
        return view('lessons.index', compact('lessons'));
    }

    public function create()
    {
        return view('lessons.create');
    }

    public function manage(Lesson $lesson)
    {
        $lesson->load(['segments.questions']);
        return view('lessons.manage', compact('lesson'));
    }

    public function edit(Lesson $lesson)
    {
        return view('lessons.edit', compact('lesson'));
    }

    public function update(Request $request, Lesson $lesson)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'required|string|max:100',
            'level' => 'required|string|in:Beginner,Intermediate,Advanced',
            'status' => 'required|in:draft,published,hidden',
            'content_text' => 'nullable|string',
            'lesson_file' => 'nullable|file|mimes:txt,pdf,doc,docx|max:10240',
        ]);

        $content = $request->input('content_text');

        // Handle File Update
        if ($request->hasFile('lesson_file')) {
            // Delete old file if exists (optional, keeping history might be better but for V1 delete)
            if ($lesson->original_file_path) {
                Storage::disk('local')->delete($lesson->original_file_path);
            }

            $file = $request->file('lesson_file');
            $filePath = $file->store('lessons', 'local');
            $fileType = $file->getClientOriginalExtension();

            $lesson->original_file_path = $filePath;
            $lesson->original_file_type = $fileType;

            if ($fileType === 'txt') {
                $content = file_get_contents($file->getRealPath());
            }
        }

        // If content was updated (either via new text or new file text extraction)
        if (!empty($content)) {
            $lesson->content = $content;
        }

        $lesson->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'subject' => $request->input('subject'),
            'level' => $request->input('level'),
            'status' => $request->input('status'),
        ]);

        return redirect()->route('lessons.index')->with('success', 'Lesson updated successfully!');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'required|string|max:100',
            'level' => 'required|string|in:Beginner,Intermediate,Advanced',
            'content_text' => 'nullable|string',
            'lesson_file' => 'nullable|file|mimes:txt,pdf,doc,docx|max:10240', // 10MB
        ]);

        $content = $request->input('content_text');
        $filePath = null;
        $fileType = null;

        // Handle File Upload
        if ($request->hasFile('lesson_file')) {
            $file = $request->file('lesson_file');
            $filePath = $file->store('lessons', 'local'); // Store in storage/app/lessons
            $fileType = $file->getClientOriginalExtension();

            // Simple text extraction for .txt files
            if ($fileType === 'txt') {
                $content = file_get_contents($file->getRealPath());
            }
            // For now, PDFs/Docs are just stored. Content extraction happens later or manual copy-paste.
        }

        if (empty($content) && empty($filePath)) {
            return back()->with('error', 'Please provide either text content or upload a file.');
        }

        Lesson::create([
            'teacher_id' => Auth::id(),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'subject' => $request->input('subject'),
            'level' => $request->input('level'),
            'content' => $content,
            'original_file_path' => $filePath,
            'original_file_type' => $fileType,
            'status' => 'published', // Default to published for Create V1, or change to draft later
        ]);

        return redirect()->route('lessons.index')->with('success', 'Lesson created successfully!');
    }

    public function telegramLink(Lesson $lesson)
    {
        // Deep link format: https://t.me/bot_username?start=parameter
        $botUsername = config('telegram.bots.mybot.username'); // Ensure this is set in .env
        $link = "https://t.me/{$botUsername}?start=learn_{$lesson->id}";

        return view('lessons.telegram_redirect', compact('link', 'lesson'));
    }
}
