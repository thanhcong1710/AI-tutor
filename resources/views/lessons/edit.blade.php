@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto bg-white shadow-xl rounded-xl overflow-hidden border border-gray-100">
        <div class="px-8 py-6 bg-gray-50 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">Edit Lesson: {{ $lesson->title }}</h2>
            <p class="text-sm text-gray-500 mt-1">Update lesson details and content.</p>
        </div>

        <form action="{{ route('lessons.update', $lesson->id) }}" method="POST" enctype="multipart/form-data"
            class="p-8 space-y-8">
            @csrf
            @method('PUT')

            <!-- Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label for="title" class="block text-sm font-semibold text-gray-700">Lesson Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $lesson->title) }}" required
                        class="w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none transition-all duration-200">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="subject" class="block text-sm font-semibold text-gray-700">Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject', $lesson->subject) }}" required
                        class="w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none transition-all duration-200">
                    @error('subject') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Level & Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label for="level" class="block text-sm font-semibold text-gray-700">Level</label>
                    <div class="relative">
                        <select name="level" id="level" required
                            class="w-full appearance-none rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none transition-all duration-200">
                            <option value="Beginner" {{ $lesson->level == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                            <option value="Intermediate" {{ $lesson->level == 'Intermediate' ? 'selected' : '' }}>Intermediate
                            </option>
                            <option value="Advanced" {{ $lesson->level == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </div>
                    </div>
                    @error('level') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="status" class="block text-sm font-semibold text-gray-700">Status</label>
                    <div class="relative">
                        <select name="status" id="status" required
                            class="w-full appearance-none rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none transition-all duration-200">
                            <option value="draft" {{ $lesson->status == 'draft' ? 'selected' : '' }}>Nháp (Draft)</option>
                            <option value="published" {{ $lesson->status == 'published' ? 'selected' : '' }}>Công khai
                                (Public)</option>
                            <option value="hidden" {{ $lesson->status == 'hidden' ? 'selected' : '' }}>Riêng tư (Hidden)
                            </option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </div>
                    </div>
                    @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="space-y-2">
                <label for="description" class="block text-sm font-semibold text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none transition-all duration-200">{{ old('description', $lesson->description) }}</textarea>
            </div>

            <hr class="border-gray-100">

            <!-- Content Source -->
            <div x-data="{ tab: 'file' }">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-800">Update Content (Optional)</h3>
                    <div class="flex bg-gray-100 p-1 rounded-lg">
                        <button type="button" @click="tab = 'text'"
                            :class="{'bg-white text-indigo-700 shadow-sm': tab === 'text', 'text-gray-500 hover:text-gray-700': tab !== 'text'}"
                            class="px-4 py-2 font-medium text-sm rounded-md transition-all duration-200">
                            Edit Text
                        </button>
                        <button type="button" @click="tab = 'file'"
                            :class="{'bg-white text-indigo-700 shadow-sm': tab === 'file', 'text-gray-500 hover:text-gray-700': tab !== 'file'}"
                            class="px-4 py-2 font-medium text-sm rounded-md transition-all duration-200">
                            Upload New File
                        </button>
                    </div>
                </div>

                <!-- Text Input -->
                <div x-show="tab === 'text'"
                    class="bg-gray-50 p-6 rounded-xl border border-gray-200 transition-all duration-300">
                    <label for="content_text" class="block text-sm font-semibold text-gray-700 mb-2">Detailed
                        Content</label>
                    <textarea name="content_text" id="content_text" rows="12"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none sm:text-sm transition-all duration-200">{{ old('content_text', $lesson->content) }}</textarea>
                </div>

                <!-- File Upload -->
                <div x-show="tab === 'file'"
                    class="bg-indigo-50 p-6 rounded-xl border-2 border-dashed border-indigo-200 hover:border-indigo-300 transition-all duration-300">
                    <label for="lesson_file" class="block text-sm font-semibold text-gray-700 mb-4">Upload New File (TXT,
                        PDF, DOCX)</label>
                    @if($lesson->original_file_path)
                        <p class="text-xs text-indigo-600 font-medium mb-3 bg-indigo-100 inline-block px-3 py-1 rounded-full">
                            Current file: {{ basename($lesson->original_file_path) }}</p>
                    @endif
                    <div
                        class="flex flex-col items-center justify-center pt-8 pb-8 bg-white rounded-lg cursor-pointer hover:bg-gray-50 transition-colors relative">
                        <svg class="w-14 h-14 text-indigo-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                            </path>
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="lesson_file_input"
                                class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none hover:underline">
                                <span>Upload a file</span>
                                <input id="lesson_file_input" name="lesson_file" type="file" class="sr-only">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">TXT, PDF, DOCX up to 10MB</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-6 border-t border-gray-100">
                <a href="{{ route('lessons.index') }}"
                    class="bg-white py-3 px-6 border border-gray-300 rounded-lg shadow-sm text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 mr-4 transition-all duration-200">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex justify-center py-3 px-8 border border-transparent shadow-md text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:-translate-y-0.5">
                    Update Lesson
                </button>
            </div>
        </form>
    </div>
@endsection