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
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Question Text</label>
                                <input type="text" name="question" required class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 transition-all duration-200">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Type</label>
                                    <select name="type" x-model="qType" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 transition-all duration-200">
                                        <option value="multiple_choice">Multiple Choice</option>
                                        <option value="true_false">True/False</option>
                                        <option value="short_answer">Short Answer</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                                    <select name="status" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 transition-all duration-200">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Multiple Choice Options -->
                            <div x-show="qType === 'multiple_choice'" class="space-y-3 p-4 bg-indigo-50 rounded-lg border border-indigo-100">
                                <label class="block text-sm font-semibold text-indigo-900">Answer Options</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-bold text-indigo-400">A.</span>
                                        <input type="text" name="option_a" placeholder="Option A Content" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all">
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="font-bold text-indigo-400">B.</span>
                                        <input type="text" name="option_b" placeholder="Option B Content" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all">
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="font-bold text-indigo-400">C.</span>
                                        <input type="text" name="option_c" placeholder="Option C Content" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all">
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="font-bold text-indigo-400">D.</span>
                                        <input type="text" name="option_d" placeholder="Option D Content" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all">
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Correct Answer</label>
                                <input type="text" name="correct_answer" required placeholder="e.g. A, True, or the answer text" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 transition-all duration-200">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Explanation (Optional)</label>
                                <textarea name="explanation" rows="2" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 transition-all duration-200"></textarea>
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
        <!-- Add Segment Section -->
        <div class="bg-white p-8 rounded-xl shadow-xl border border-gray-100 mt-10 relative overflow-hidden group hover:shadow-2xl transition-all duration-300">
            <!-- Decorative Background Element -->
            <div class="absolute top-0 right-0 p-8 opacity-5 transform translate-x-1/3 -translate-y-1/3 pointer-events-none">
                 <svg class="w-64 h-64 text-indigo-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-2.033 16.01c.564-1.789 1.632-3.932 1.821-4.474.273-.787-.211-1.136-1.74.209l-.34-.64c1.744-1.897 5.335-2.326 4.113.613-.763 1.835-1.309 3.074-1.621 4.03-.455 1.393.694.828 1.819-.211.154.25.214.366.195.421-1.751 2.921-6.721 2.228-4.248-3.007zm3.565-5.26c-.309-.311-.07-.743.439-1.258.522-.53.535-1.203.02-1.719-.508-.507-1.171-.502-1.706.035-.53.532-.97.289-.661.6.309.31.069.742-.439 1.257-.521.53-.535 1.203-.02 1.719.507.508 1.171.503 1.706-.035.53-.532.971-.289.661-.6z"/></svg>
            </div>

            <div class="relative z-10 flex items-center mb-8">
                <div class="bg-indigo-600 rounded-lg p-3 mr-4 shadow-md group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">Add New Segment</h3>
                    <p class="text-gray-500 text-sm mt-1">Structure your lesson by creating a new learning section.</p>
                </div>
            </div>
            
            <form action="{{ route('segments.store', $lesson->id) }}" method="POST" class="relative z-10">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-4 space-y-2">
                        <label class="block text-sm font-bold text-gray-700">Segment Title <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" name="title" required placeholder="e.g., Introduction to Syntax" 
                                class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-3 pl-10 text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none transition-all duration-200 shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                        </div>
                    </div>
                    <div class="md:col-span-8 space-y-2">
                        <label class="block text-sm font-bold text-gray-700">Description <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <div class="relative">
                            <input type="text" name="content" placeholder="Briefly describe what students will learn in this part..." 
                                class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-3 pl-10 text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none transition-all duration-200 shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button type="submit" 
                        class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-8 py-3 text-sm font-bold text-white shadow-lg hover:bg-indigo-700 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Create Segment
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection