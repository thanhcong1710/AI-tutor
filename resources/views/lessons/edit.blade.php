@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Edit Lesson: {{ $lesson->title }}</h2>
        </div>

        <form action="{{ route('lessons.update', $lesson->id) }}" method="POST" enctype="multipart/form-data"
            class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Lesson Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $lesson->title) }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject', $lesson->subject) }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('subject') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Level & Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700">Level</label>
                    <select name="level" id="level" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="Beginner" {{ $lesson->level == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="Intermediate" {{ $lesson->level == 'Intermediate' ? 'selected' : '' }}>Intermediate
                        </option>
                        <option value="Advanced" {{ $lesson->level == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                    @error('level') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="draft" {{ $lesson->status == 'draft' ? 'selected' : '' }}>Nháp (Draft)</option>
                        <option value="published" {{ $lesson->status == 'published' ? 'selected' : '' }}>Công khai (Public)
                        </option>
                        <option value="hidden" {{ $lesson->status == 'hidden' ? 'selected' : '' }}>Riêng tư (Hidden)</option>
                    </select>
                    @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $lesson->description) }}</textarea>
            </div>

            <hr class="border-gray-200">

            <!-- Content Source -->
            <div x-data="{ tab: 'file' }">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Update Content (Optional)</h3>

                <div class="flex space-x-4 mb-4">
                    <button type="button" @click="tab = 'text'"
                        :class="{'bg-indigo-100 text-indigo-700': tab === 'text', 'text-gray-500 hover:text-gray-700': tab !== 'text'}"
                        class="px-3 py-2 font-medium text-sm rounded-md transition-colors">
                        Edit Text
                    </button>
                    <button type="button" @click="tab = 'file'"
                        :class="{'bg-indigo-100 text-indigo-700': tab === 'file', 'text-gray-500 hover:text-gray-700': tab !== 'file'}"
                        class="px-3 py-2 font-medium text-sm rounded-md transition-colors">
                        Upload New File
                    </button>
                </div>

                <!-- Text Input -->
                <div x-show="tab === 'text'" class="bg-gray-50 p-4 rounded-md border border-dashed border-gray-300">
                    <label for="content_text" class="block text-sm font-medium text-gray-700 mb-2">Detailed Content</label>
                    <textarea name="content_text" id="content_text" rows="10"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('content_text', $lesson->content) }}</textarea>
                </div>

                <!-- File Upload -->
                <div x-show="tab === 'file'" class="bg-blue-50 p-4 rounded-md border border-dashed border-blue-300">
                    <label for="lesson_file" class="block text-sm font-medium text-gray-700 mb-2">Upload New File (TXT, PDF,
                        DOCX)</label>
                    @if($lesson->original_file_path)
                        <p class="text-xs text-gray-500 mb-2">Current file: {{ basename($lesson->original_file_path) }}</p>
                    @endif
                    <div
                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md bg-white hover:bg-gray-50 transition-colors cursor-pointer relative">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                viewBox="0 0 48 48" aria-hidden="true">
                                <path
                                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="lesson_file_input"
                                    class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>Upload a file</span>
                                    <input id="lesson_file_input" name="lesson_file" type="file" class="sr-only">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                TXT, PDF, DOCX up to 10MB
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200">
                <a href="{{ route('lessons.index') }}"
                    class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 mr-4">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-300">
                    Update Lesson
                </button>
            </div>
        </form>
    </div>
@endsection