<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LessonQuestion;
use App\Models\LessonSegment;

class QuestionController extends Controller
{
    public function store(Request $request, LessonSegment $segment)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:multiple_choice,true_false,short_answer',
            'points' => 'integer|min:1',
            'difficulty' => 'in:easy,medium,hard',
            'correct_answer' => 'required|string',
            'explanation' => 'nullable|string',
            'option_a' => 'nullable|required_if:type,multiple_choice|string',
            'option_b' => 'nullable|required_if:type,multiple_choice|string',
            'option_c' => 'nullable|required_if:type,multiple_choice|string',
            'option_d' => 'nullable|required_if:type,multiple_choice|string',
            'status' => 'in:active,inactive',
        ]);

        $options = null;
        if ($validated['type'] === 'multiple_choice') {
            $options = [
                'A' => $request->option_a,
                'B' => $request->option_b,
                'C' => $request->option_c,
                'D' => $request->option_d,
            ];
        }

        $segment->questions()->create([
            'question' => $validated['question'],
            'type' => $validated['type'],
            'points' => $validated['points'] ?? 1,
            'difficulty' => $validated['difficulty'] ?? 'medium',
            'options' => $options,
            'correct_answer' => $validated['correct_answer'],
            'explanation' => $validated['explanation'],
            'status' => $validated['status'] ?? 'active',
            'order' => $segment->questions()->max('order') + 1,
        ]);

        return redirect()->back()->with('success', 'Question added successfully.');
    }

    public function update(Request $request, LessonQuestion $question)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:multiple_choice,true_false,short_answer',
            'points' => 'integer|min:1',
            'difficulty' => 'in:easy,medium,hard',
            'correct_answer' => 'required|string',
            'explanation' => 'nullable|string',
            'option_a' => 'nullable|required_if:type,multiple_choice|string',
            'option_b' => 'nullable|required_if:type,multiple_choice|string',
            'option_c' => 'nullable|required_if:type,multiple_choice|string',
            'option_d' => 'nullable|required_if:type,multiple_choice|string',
            'status' => 'in:active,inactive',
        ]);

        $options = null;
        if ($validated['type'] === 'multiple_choice') {
            $options = [
                'A' => $request->option_a,
                'B' => $request->option_b,
                'C' => $request->option_c,
                'D' => $request->option_d,
            ];
        }

        $question->update([
            'question' => $validated['question'],
            'type' => $validated['type'],
            'points' => $validated['points'] ?? 1,
            'difficulty' => $validated['difficulty'] ?? 'medium',
            'options' => $options,
            'correct_answer' => $validated['correct_answer'],
            'explanation' => $validated['explanation'],
            'status' => $validated['status'] ?? 'active',
        ]);

        return redirect()->back()->with('success', 'Question updated successfully.');
    }

    public function destroy(LessonQuestion $question)
    {
        $question->delete();
        return redirect()->back()->with('success', 'Question deleted successfully.');
    }
}
