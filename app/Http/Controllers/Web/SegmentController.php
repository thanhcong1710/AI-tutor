<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\LessonSegment;

class SegmentController extends Controller
{
    public function store(Request $request, Lesson $lesson)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string'
        ]);

        $lesson->segments()->create([
            'title' => $request->title,
            'content' => $request->content ?? '',
            'order' => $lesson->segments()->max('order') + 1,
        ]);

        return redirect()->route('lessons.manage', $lesson->id)->with('success', 'Segment created successfully.');
    }

    public function update(Request $request, LessonSegment $segment)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string'
        ]);

        $segment->update([
            'title' => $request->title,
            'content' => $request->content ?? '',
        ]);

        return redirect()->route('lessons.manage', $segment->lesson_id)->with('success', 'Segment updated successfully.');
    }

    public function destroy(LessonSegment $segment)
    {
        $lessonId = $segment->lesson_id;
        $segment->delete();

        return redirect()->route('lessons.manage', $lessonId)->with('success', 'Segment deleted successfully.');
    }
}
