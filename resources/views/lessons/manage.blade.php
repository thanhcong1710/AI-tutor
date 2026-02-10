@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto space-y-8"
        x-data="{ editingSegment: null, editingQuestion: null, addingQuestionTo: null }">
        <div class="flex justify-between items-center border-b pb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $lesson->title }}</h1>
                <p class="text-gray-500">Manage Segments & Questions</p>
            </div>
            <a href="{{ route('lessons.index') }}" class="text-gray-600 hover:text-gray-900 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Back to Lessons
            </a>
        </div>

        <!-- Segments List -->
        <div class="space-y-6">
            @forelse($lesson->segments as $segment)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Segment Header -->
                    <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-b border-gray-200">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-800">{{ $segment->title }}</h3>
                            @if($segment->content)
                                <p class="text-xs text-gray-500 truncate mt-1">{{ Str::limit($segment->content, 100) }}</p>
                            @endif
                        </div>
                        <div class="flex space-x-2">
                            <button @click="editingSegment = {{ $segment->id }}"
                                class="text-blue-600 hover:text-blue-800 text-sm">Edit</button>
                            <form action="{{ route('segments.destroy', $segment->id) }}" method="POST"
                                onsubmit="return confirm('Delete this segment?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                            </form>
                        </div>
                    </div>

                    <!-- Edit Segment Form (Hidden by default) -->
                    <div x-show="editingSegment === {{ $segment->id }}" class="p-6 bg-blue-50 border-b border-blue-100">
                        <form action="{{ route('segments.update', $segment->id) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Segment Title</label>
                                    <input type="text" name="title" value="{{ $segment->title }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Content</label>
                                    <textarea name="content" rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ $segment->content }}</textarea>
                                </div>
                                <div class="flex space-x-2">
                                    <button type="submit"
                                        class="bg-blue-600 text-white px-3 py-1 rounded-md text-sm">Save</button>
                                    <button type="button" @click="editingSegment = null"
                                        class="bg-white border text-gray-700 px-3 py-1 rounded-md text-sm">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Questions List -->
                    <div class="p-6">
                        <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Questions</h4>
                        <div class="space-y-4">
                            @forelse($segment->questions as $question)
                                <div
                                    class="border rounded-md p-4 {{ $question->status === 'inactive' ? 'bg-gray-100 opacity-75' : 'bg-white' }}">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                <span
                                                    class="px-2 py-0.5 rounded text-xs font-semibold bg-indigo-100 text-indigo-700">{{ $question->type }}</span>
                                                <span
                                                    class="px-2 py-0.5 rounded text-xs font-semibold {{ $question->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700' }}">{{ $question->status }}</span>
                                            </div>
                                            <p class="mt-2 text-gray-900 font-medium">{{ $question->question }}</p>
                                            <p class="text-sm text-gray-500 mt-1">Answer: <span
                                                    class="font-mono text-green-600">{{ $question->correct_answer }}</span></p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <!-- <button @click="editingQuestion = {{ $question->id }}" class="text-blue-600 hover:text-blue-800 text-xs">Edit</button> -->
                                            <form action="{{ route('questions.destroy', $question->id) }}" method="POST"
                                                onsubmit="return confirm('Delete question?')" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                    <!-- Options preview if MC -->
                                    @if($question->type === 'multiple_choice' && is_array($question->options))
                                        <div class="mt-2 grid grid-cols-2 gap-2 text-xs text-gray-600">
                                            @foreach($question->options as $key => $val)
                                                <div class="@if($key == $question->correct_answer) font-bold text-green-700 @endif">
                                                    {{ $key }}: {{ $val }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-400 italic">No questions in this segment yet.</p>
                            @endforelse
                        </div>

                        <!-- Add Question Button -->
                        <div class="mt-4">
                            <button @click="addingQuestionTo = {{ $segment->id }}"
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                    </path>
                                </svg>
                                Add Question
                            </button>
                        </div>

                        <!-- Add Question Form -->
                <div x-show="addingQuestionTo === {{ $segment->id }}" class="mt-4 p-4 bg-gray-50 rounded-md border border-gray-200" x-data="{ qType: 'multiple_choice' }">
                    <form action="{{ route('questions.store', $segment->id) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Question Text</label>
                                <input type="text" name="question" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Type</label>
                                    <select name="type" x-model="qType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="multiple_choice">Multiple Choice</option>
                                        <option value="true_false">True/False</option>
                                        <option value="short_answer">Short Answer</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Status</label>
                                    <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Multiple Choice Options -->
                            <div x-show="qType === 'multiple_choice'" class="space-y-2">
                                <label class="block text-xs font-medium text-gray-700">Options</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <input type="text" name="option_a" placeholder="Option A" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <input type="text" name="option_b" placeholder="Option B" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <input type="text" name="option_c" placeholder="Option C" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <input type="text" name="option_d" placeholder="Option D" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Correct Answer</label>
                                <input type="text" name="correct_answer" required placeholder="e.g. A, True, or the answer text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Explanation (Optional)</label>
                                <textarea name="explanation" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex justify-end space-x-3">
                            <button type="button" @click="addingQuestionTo = null" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Cancel</button>
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Save Question</button>
                        </div>
                    </form>
                </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 bg-white rounded-lg border border-dashed border-gray-300 text-gray-500">
                    No segments found. Create a segment to start adding questions.
                </div>
            @endforelse
        </div>

        <!-- Add Segment Section -->
        <div class="bg-gray-100 p-6 rounded-lg border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Add New Segment</h3>
            <form action="{{ route('segments.store', $lesson->id) }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-1">
                        <input type="text" name="title" required placeholder="Segment Title (e.g., Intro)"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <input type="text" name="content" placeholder="Content (Optional)"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>
                <div class="mt-4 text-right">
                    <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Segment
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection