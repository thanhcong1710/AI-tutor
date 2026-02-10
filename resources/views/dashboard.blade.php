@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Stats Card -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <h3 class="text-lg font-semibold text-gray-700">Tokens Usage</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['tokens_total']) }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Used</p>
            <hr class="my-4">
            <p class="text-sm font-semibold text-gray-700">{{ number_format($stats['tokens_month']) }}</p>
            <p class="text-xs text-gray-500">This Month</p>
        </div>

        @if($stats['role'] === 'admin' || $stats['role'] === 'teacher')
            <!-- Teacher Stats -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <h3 class="text-lg font-semibold text-gray-700">Platform Stats</h3>
                <div class="mt-2 flex justify-between">
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_students'] ?? 0) }}</p>
                        <p class="text-sm text-gray-500">Students</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_lessons'] ?? 0) }}</p>
                        <p class="text-sm text-gray-500">Lessons</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('lessons.create') }}"
                        class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Create New Lesson
                    </a>
                </div>
            </div>
        @endif

        <!-- Quick Action / User Profile -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <h3 class="text-lg font-semibold text-gray-700">Your Learning</h3>
            <p class="mt-2 text-sm text-gray-600">Start learning on Telegram now!</p>
            <div class="mt-4">
                <a href="https://t.me/{{ config('telegram.bots.mybot.username') }}" target="_blank"
                    class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Open Telegram Bot
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Lessons -->
    <div class="mt-8">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Recent Lessons</h3>
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @forelse($recentLessons as $lesson)
                    <li>
                        <a href="{{ route('lessons.telegram', $lesson->id) }}" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-blue-600 truncate">
                                        {{ $lesson->title }}
                                    </p>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        <p
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ $lesson->level }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-2 sm:flex sm:justify-between">
                                    <div class="sm:flex">
                                        <p class="flex items-center text-sm text-gray-500">
                                            {{ $lesson->subject }}
                                        </p>
                                    </div>
                                    <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                        <p>
                                            Updated {{ $lesson->updated_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="px-4 py-4 sm:px-6 text-center text-gray-500">No lessons available yet.</li>
                @endforelse
            </ul>
        </div>
        <div class="mt-4 text-right">
            <a href="{{ route('lessons.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">View All Lessons
                &rarr;</a>
        </div>
    </div>
@endsection