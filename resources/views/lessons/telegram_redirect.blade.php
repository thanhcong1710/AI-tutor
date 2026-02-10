@extends('layouts.app')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-[50vh]">
        <div class="bg-white p-8 rounded-lg shadow-lg text-center max-w-lg">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Start Learning "{{ $lesson->title }}"</h1>
            <p class="text-gray-600 mb-6">
                We are redirecting you to Telegram to start this lesson with your AI Tutor.
            </p>

            <div class="space-y-4">
                <a href="{{ $link }}"
                    class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-full transition duration-300 transform hover:scale-105">
                    Open in Telegram
                </a>

                <p class="text-sm text-gray-400">
                    If nothing happens, make sure you have Telegram installed.
                </p>
            </div>

            <div class="mt-8 pt-4 border-t border-gray-100">
                <a href="{{ route('lessons.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">
                    &larr; Back to Lessons
                </a>
            </div>
        </div>
    </div>

    <script>
        // Analyze user agent to decide deep link vs web link if needed (advanced)
        // For now, simple redirect after 2 seconds
        setTimeout(function () {
            window.location.href = "{{ $link }}";
        }, 2000);
    </script>
@endsection