@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Xin chào, {{ Auth::user()->name }}! 👋</h1>
                        <p class="text-gray-600 mt-1">Sẵn sàng học tập hôm nay chưa?</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Điểm tích lũy</p>
                            <p class="text-2xl font-bold text-blue-600">0 XP</p>
                        </div>
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Khóa học đang học</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $assignedLessons->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-book text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Hoàn thành</p>
                            <p class="text-3xl font-bold text-green-600">0</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Streak</p>
                            <p class="text-3xl font-bold text-orange-600">0 ngày 🔥</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-fire text-orange-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Courses -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Khóa học của tôi</h2>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">{{ $assignedLessons->count() }} khóa học</span>
                    </div>
                </div>

                @if($assignedLessons->isEmpty())
                    <!-- Empty State -->
                    <div class="bg-white rounded-2xl shadow-sm p-12 text-center border border-gray-100">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-book-open text-gray-400 text-4xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Chưa có khóa học nào</h3>
                        <p class="text-gray-500 mb-6">Giáo viên sẽ giao bài học cho bạn sớm thôi!</p>
                    </div>
                @else
                    <!-- Course Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($assignedLessons as $assignment)
                            <div
                                class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 group">
                                <!-- Course Header -->
                                <div
                                    class="h-32 bg-gradient-to-br {{ $gradients[($loop->index % count($gradients))] }} p-6 relative overflow-hidden">
                                    <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16">
                                    </div>
                                    <div class="relative z-10">
                                        <span
                                            class="inline-block px-3 py-1 bg-white bg-opacity-20 backdrop-blur-sm text-white text-xs font-medium rounded-full mb-2">
                                            {{ ucfirst($assignment->lesson->subject) }}
                                        </span>
                                        <h3 class="text-white font-bold text-lg line-clamp-2">
                                            {{ $assignment->lesson->title }}
                                        </h3>
                                    </div>
                                </div>

                                <!-- Course Info -->
                                <div class="p-6">
                                    <div class="flex items-center space-x-4 mb-4">
                                        <div class="flex items-center text-sm text-gray-500">
                                            <i class="fas fa-signal mr-2"></i>
                                            {{ ucfirst($assignment->lesson->level) }}
                                        </div>
                                        <div class="flex items-center text-sm text-gray-500">
                                            <i class="fas fa-clock mr-2"></i>
                                            {{ $assignment->lesson->estimated_duration ?? 30 }} phút
                                        </div>
                                    </div>

                                    @if($assignment->teacher_notes)
                                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mb-4">
                                            <p class="text-xs text-blue-700">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                {{ $assignment->teacher_notes }}
                                            </p>
                                        </div>
                                    @endif

                                    <!-- Progress Bar -->
                                    <div class="mb-4">
                                        <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                                            <span>Tiến độ</span>
                                            <span>0%</span>
                                        </div>
                                        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-blue-500 to-purple-600" style="width: 0%">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Learning Mode Selection -->
                                    <div class="space-y-2">
                                        <p class="text-xs font-medium text-gray-700 mb-3">Chọn chế độ học:</p>

                                        <!-- Web Mode -->
                                        <a href="{{ route('lessons.learn', $assignment->lesson->id) }}"
                                            class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl transition-all group-hover:shadow-lg">
                                            <div class="flex items-center space-x-3">
                                                <div
                                                    class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-desktop"></i>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-sm">Học trên Web</p>
                                                    <p class="text-xs text-blue-100">Giao diện đầy đủ</p>
                                                </div>
                                            </div>
                                            <i class="fas fa-arrow-right"></i>
                                        </a>

                                        <!-- Telegram Mode -->
                                        <a href="https://t.me/{{ $botUsername }}?start=learn_{{ $assignment->lesson->id }}"
                                            target="_blank"
                                            class="flex items-center justify-between p-3 bg-white hover:bg-gray-50 border-2 border-blue-200 rounded-xl transition-all">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <i class="fab fa-telegram text-blue-600"></i>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-sm text-gray-900">Học trên Telegram</p>
                                                    <p class="text-xs text-gray-500">Học mọi lúc mọi nơi</p>
                                                </div>
                                            </div>
                                            <i class="fas fa-external-link-alt text-gray-400"></i>
                                        </a>
                                    </div>

                                    <!-- Due Date -->
                                    @if($assignment->due_date)
                                        <div class="mt-4 pt-4 border-t border-gray-100">
                                            <div class="flex items-center justify-between text-xs">
                                                <span class="text-gray-500">
                                                    <i class="fas fa-calendar-alt mr-1"></i>
                                                    Hạn nộp
                                                </span>
                                                <span
                                                    class="font-medium {{ $assignment->isOverdue() ? 'text-red-600' : 'text-gray-700' }}">
                                                    {{ \Carbon\Carbon::parse($assignment->due_date)->format('d/m/Y') }}
                                                    @if($assignment->isOverdue())
                                                        <span class="text-red-600">(Quá hạn)</span>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Learning Tips -->
            <div class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-2xl shadow-lg p-8 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h3 class="text-2xl font-bold mb-2">💡 Mẹo học tập hiệu quả</h3>
                        <p class="text-purple-100 mb-4">Học đều đặn mỗi ngày sẽ giúp bạn tiến bộ nhanh hơn!</p>
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check-circle"></i>
                                <span class="text-sm">Học 30 phút/ngày</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check-circle"></i>
                                <span class="text-sm">Ôn tập thường xuyên</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check-circle"></i>
                                <span class="text-sm">Hỏi AI khi cần</span>
                            </div>
                        </div>
                    </div>
                    <div class="hidden lg:block">
                        <div class="w-32 h-32 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-6xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection