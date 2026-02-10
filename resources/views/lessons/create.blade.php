@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">Create New Lesson</h2>
        </div>

        <form action="{{ route('lessons.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <!-- Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Lesson Title</label>
                    <input type="text" name="title" id="title" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Intro to Algorithms">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                    <input type="text" name="subject" id="subject" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Computer Science">
                    @error('subject') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Level & Duration -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700">Level</label>
                    <select name="level" id="level" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                    </select>
                    @error('level') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="estimated_duration" class="block text-sm font-medium text-gray-700">Duration (mins)</label>
                    <input type="number" name="estimated_duration" id="estimated_duration"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="30">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
            </div>

            <hr class="border-gray-200">

            <!-- Content Source -->
            <div x-data="{ tab: 'file' }">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Lesson Content</h3>

                <div class="flex space-x-4 mb-4">
                    <button type="button" @click="tab = 'text'"
                        :class="{'bg-indigo-100 text-indigo-700': tab === 'text', 'text-gray-500 hover:text-gray-700': tab !== 'text'}"
                        class="px-3 py-2 font-medium text-sm rounded-md transition-colors">
                        Type Content
                    </button>
                    <button type="button" @click="tab = 'file'"
                        :class="{'bg-indigo-100 text-indigo-700': tab === 'file', 'text-gray-500 hover:text-gray-700': tab !== 'file'}"
                        class="px-3 py-2 font-medium text-sm rounded-md transition-colors">
                        Upload File
                    </button>
                </div>

                <!-- Text Input -->
                <div x-show="tab === 'text'" class="bg-gray-50 p-4 rounded-md border border-dashed border-gray-300">
                    <label for="content_text" class="block text-sm font-medium text-gray-700 mb-2">Detailed Content</label>
                    <textarea name="content_text" id="content_text" rows="10"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="# Chapter 1..."></textarea>
                </div>

                <!-- File Upload -->
                <div x-show="tab === 'file'" class="bg-blue-50 p-4 rounded-md border border-dashed border-blue-300">
                    <label for="lesson_file" class="block text-sm font-medium text-gray-700 mb-2">Upload File (TXT, PDF,
                        DOCX)</label>
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

            <script>
                // Simple drag and drop logic (optional)
                const dropArea = document.querySelector('.border-dashed');
                const fileInput = document.getElementById('lesson_file_input');

                // Basic file selection feedback
                fileInput.addEventListener('change', (e) => {
                    if (e.target.files.length > 0) {
                        alert(`Selected file: ${e.target.files[0].name}`);
                    }
                });
            </script>

            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button type="button" onclick="window.history.back()"
                    class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
                    Cancel
                </button>
                <button type="submit"
                    class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-300 transform hover:-translate-y-0.5">
                    Create Lesson
                </button>
            </div>
        </form>
    </div>
@endsection