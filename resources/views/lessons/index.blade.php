@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Available Lessons</h1>
            @can('admin')
                <a href="{{ route('lessons.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full shadow-lg transition duration-300">
                    Create Lesson &plus;
                </a>
            @endcan
            @can('teacher')
                <a href="{{ route('lessons.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full shadow-lg transition duration-300">
                    Create Lesson &plus;
                </a>
            @endcan
        </div>

        <!-- Lesson Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($lessons as $lesson)
                <div
                    class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 flex flex-col h-full transform hover:scale-[1.02]">
                    <div class="p-6 flex-grow">
                        <div class="flex justify-between items-start">
                            <div class="flex space-x-2">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ $lesson->level }}
                                </span>
                                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'teacher')
                                                    @php
                                                        $statusClass = 'bg-red-100 text-red-800';
                                                        if ($lesson->status === 'published')
                                                            $statusClass = 'bg-green-100 text-green-800';
                                                        elseif ($lesson->status === 'draft')
                                                            $statusClass = 'bg-gray-100 text-gray-800';
                                                        elseif ($lesson->status === 'hidden')
                                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                                    @endphp
                                     <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                                        {{ ucfirst($lesson->status) }}
                                                    </span>
                                @endif
                            </div>
                            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'teacher')
                                <a href="{{ route('lessons.edit', $lesson->id) }}"
                                    class="text-gray-400 hover:text-blue-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </a>
                            @endif
                        </div>

                        <h3 class="mt-4 text-xl font-bold text-gray-900 leading-tight">
                            <a href="{{ route('lessons.telegram', $lesson->id) }}" class="hover:underline hover:text-blue-600">
                                {{ $lesson->title }}
                            </a>
                        </h3>

                        <p class="mt-2 text-gray-600 text-sm line-clamp-3">
                            {{ Str::limit($lesson->description, 150) }}
                        </p>

                        <div class="mt-4 flex items-center text-sm text-gray-500">
                            <span class="mr-2">{{ $lesson->subject }}</span>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t border-gray-100">
                        <div class="flex items-center">
                            <!-- Progress Bar (Placeholder) -->
                            <div class="w-24 bg-gray-200 rounded-full h-1.5 mr-2">
                                <div class="bg-green-500 h-1.5 rounded-full" style="width: 0%"></div>
                            </div>
                        </div>

                        <a href="{{ route('lessons.telegram', $lesson->id) }}"
                            class="text-blue-600 font-semibold text-sm hover:text-blue-800 inline-flex items-center group">
                            Start Learning
                            <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 bg-white rounded-lg shadow-sm border border-dashed border-gray-300">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No lessons</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new lesson.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $lessons->links() }}
        </div>
    </div>
@endsection