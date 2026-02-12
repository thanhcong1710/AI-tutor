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
                                <div class="flex space-x-3">
                                    <a href="{{ route('lessons.manage', $lesson->id) }}"
                                        class="text-gray-400 hover:text-green-600 transition-colors" title="Manage Content">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('lessons.edit', $lesson->id) }}"
                                        class="text-gray-400 hover:text-blue-600 transition-colors" title="Edit Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                </div>
                            @endif
                        </div>

                        <h3 class="mt-4 text-xl font-bold text-gray-900 leading-tight">
                            <a href="https://t.me/{{ $botUsername }}?start=learn_{{ $lesson->id }}" target="_blank"
                                class="hover:underline hover:text-blue-600">
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

                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                        <div class="flex items-center justify-between gap-3">
                            <!-- Learn on Web Button -->
                            <a href="{{ route('lessons.learn', $lesson->id) }}"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm py-2.5 px-4 rounded-lg inline-flex items-center justify-center group transition-all shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Học trên Web
                            </a>

                            <!-- Learn on Telegram Button -->
                            <a href="https://t.me/{{ $botUsername }}?start=learn_{{ $lesson->id }}" target="_blank"
                                class="flex-1 bg-white hover:bg-gray-50 text-blue-600 font-semibold text-sm py-2.5 px-4 rounded-lg inline-flex items-center justify-center group transition-all border-2 border-blue-600">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.64.135-.954l11.566-4.458c.538-.196 1.006.128.832.941z" />
                                </svg>
                                Telegram
                            </a>
                        </div>
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