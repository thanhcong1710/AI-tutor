<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $lesson->title }} - AI Tutor</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Smooth transitions */
        .segment-item {
            transition: all 0.2s ease;
        }

        .segment-item:hover {
            transform: translateX(4px);
        }

        /* Chat bubble animation */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chat-message {
            animation: slideIn 0.3s ease;
        }

        /* Progress bar */
        .progress-bar {
            transition: width 0.5s ease;
        }
    </style>
</head>

<body class="bg-gray-50">

    <!-- Top Navigation -->
    <nav class="bg-white border-b border-gray-200 fixed w-full top-0 z-50 shadow-sm">
        <div class="px-6 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('lessons.index') }}" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">{{ $lesson->title }}</h1>
                    <p class="text-sm text-gray-500">{{ $lesson->subject }} • {{ ucfirst($lesson->level) }}</p>
                </div>
            </div>

            <!-- Progress -->
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Tiến độ:</span>
                    <div class="w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div id="overall-progress" class="progress-bar h-full bg-green-500" style="width: 0%"></div>
                    </div>
                    <span id="progress-text" class="text-sm font-medium text-gray-900">0%</span>
                </div>

                <button onclick="toggleFullscreen()"
                    class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-expand"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="flex pt-16 h-screen">

        <!-- Left Sidebar - Curriculum -->
        <div class="w-80 bg-white border-r border-gray-200 overflow-y-auto">
            <div class="p-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Nội dung khóa học</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $lesson->segments->count() }} phần học</p>
            </div>

            <div class="p-2" id="segments-list">
                @foreach($lesson->segments as $index => $segment)
                    <div class="segment-item p-3 mb-2 rounded-lg cursor-pointer hover:bg-gray-50 {{ $index === 0 ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}"
                        data-segment-id="{{ $segment->id }}" data-segment-order="{{ $segment->order }}"
                        onclick="loadSegment({{ $segment->id }})">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <span
                                        class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-200 text-gray-600 text-xs flex items-center justify-center font-medium">
                                        {{ $index + 1 }}
                                    </span>
                                    <h3 class="text-sm font-medium text-gray-900">Phần {{ $index + 1 }}</h3>
                                </div>
                                <p class="text-xs text-gray-500 mt-1 ml-8">
                                    {{ $segment->questions->count() }} câu hỏi
                                </p>
                            </div>
                            <i class="fas fa-check-circle text-green-500 hidden segment-check-{{ $segment->id }}"></i>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Content Display -->
            <div class="flex-1 overflow-y-auto bg-white">
                <div class="max-w-4xl mx-auto p-8">

                    <!-- Audio Player (if available) -->
                    <div id="audio-container" class="mb-6 hidden">
                        <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl p-6 border border-purple-200">
                            <div class="flex items-center space-x-4">
                                <button id="play-audio-btn"
                                    class="w-12 h-12 bg-purple-600 hover:bg-purple-700 text-white rounded-full flex items-center justify-center shadow-lg transition">
                                    <i class="fas fa-play"></i>
                                </button>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Nghe giảng bài</span>
                                        <span id="audio-time" class="text-sm text-gray-500">0:00 / 0:00</span>
                                    </div>
                                    <div class="w-full h-2 bg-white rounded-full overflow-hidden">
                                        <div id="audio-progress" class="h-full bg-purple-600 transition-all"
                                            style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            <audio id="audio-player" class="hidden"></audio>
                        </div>
                    </div>

                    <!-- Lesson Content -->
                    <div id="lesson-content" class="prose prose-lg max-w-none">
                        <div class="animate-pulse">
                            <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                            <div class="h-4 bg-gray-200 rounded w-full mb-4"></div>
                            <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                        </div>
                    </div>

                    <!-- Quiz Section -->
                    <div id="quiz-section" class="mt-8 hidden">
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-200">
                            <div class="flex items-center space-x-2 mb-4">
                                <i class="fas fa-question-circle text-blue-600 text-xl"></i>
                                <h3 class="text-lg font-semibold text-gray-900">Câu hỏi kiểm tra</h3>
                            </div>

                            <div id="question-container">
                                <!-- Questions will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                        <button id="prev-btn"
                            class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                            <i class="fas fa-arrow-left mr-2"></i>
                            Phần trước
                        </button>

                        <button id="next-btn"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                            Phần tiếp theo
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- AI Chat Panel (Bottom) -->
            <div class="bg-white border-t border-gray-200">
                <div class="max-w-4xl mx-auto">
                    <!-- Chat Toggle -->
                    <button id="chat-toggle" onclick="toggleChat()"
                        class="w-full px-6 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-robot text-blue-600"></i>
                            <span class="font-medium text-gray-900">Hỏi AI Trợ Giảng</span>
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">GPT-4</span>
                        </div>
                        <i id="chat-chevron" class="fas fa-chevron-up text-gray-400"></i>
                    </button>

                    <!-- Chat Box -->
                    <div id="chat-box" class="border-t border-gray-200">
                        <div class="h-64 overflow-y-auto p-4 bg-gray-50" id="chat-messages">
                            <div class="chat-message flex items-start space-x-3 mb-4">
                                <div
                                    class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-robot text-white text-sm"></i>
                                </div>
                                <div class="flex-1 bg-white rounded-lg p-3 shadow-sm">
                                    <p class="text-sm text-gray-700">Xin chào! Tôi là AI Trợ Giảng. Bạn có thắc mắc gì
                                        về bài học này không? 😊</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-white border-t border-gray-200">
                            <div class="flex space-x-2">
                                <input type="text" id="chat-input" placeholder="Đặt câu hỏi về bài học..."
                                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    onkeypress="if(event.key === 'Enter') sendMessage()">
                                <button onclick="sendMessage()"
                                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // State
        let currentSegmentId = {{ $lesson->segments->first()->id ?? 'null' }};
        let currentSegmentOrder = 1;
        let totalSegments = {{ $lesson->segments->count() }};
        let completedSegments = new Set();
        let currentQuestions = [];
        let audioPlayer = document.getElementById('audio-player');
        let chatOpen = true;

        // Initialize
        document.addEventListener('DOMContentLoaded', function () {
            if (currentSegmentId) {
                loadSegment(currentSegmentId);
            }
            setupAudioPlayer();
        });

        // Load Segment
        async function loadSegment(segmentId) {
            try {
                const response = await fetch(`/learning/segments/${segmentId}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                // Check if request was successful
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load segment');
                }

                // Update current segment
                currentSegmentId = segmentId;
                currentSegmentOrder = data.data.segment.order;

                // Update UI
                updateSidebarSelection(segmentId);
                displayContent(data.data.segment);
                displayQuestions(data.data.segment.questions);
                updateNavigationButtons();
                updateProgress();

            } catch (error) {
                console.error('Error loading segment:', error);
                alert('Không thể tải nội dung. Vui lòng thử lại!');
            }
        }

        // Display Content
        function displayContent(segment) {
            const contentDiv = document.getElementById('lesson-content');
            contentDiv.innerHTML = `
                <div class="mb-6">
                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-full mb-4">
                        Phần ${segment.order}
                    </span>
                    <div class="text-gray-700 leading-relaxed whitespace-pre-wrap">
                        ${segment.ai_explanation || segment.content}
                    </div>
                </div>
            `;

            // Load audio if available
            if (segment.audio_url) {
                document.getElementById('audio-container').classList.remove('hidden');
                audioPlayer.src = segment.audio_url;
            } else {
                document.getElementById('audio-container').classList.add('hidden');
            }

            // Scroll to top
            document.querySelector('.flex-1.overflow-y-auto').scrollTop = 0;
        }

        // Display Questions
        function displayQuestions(questions) {
            currentQuestions = questions;
            const container = document.getElementById('question-container');

            if (!questions || questions.length === 0) {
                document.getElementById('quiz-section').classList.add('hidden');
                return;
            }

            document.getElementById('quiz-section').classList.remove('hidden');

            container.innerHTML = questions.map((q, index) => `
                <div class="mb-6 last:mb-0">
                    <p class="font-medium text-gray-900 mb-3">${index + 1}. ${q.question_text}</p>
                    
                    ${q.type === 'multiple_choice' ? `
                        <div class="space-y-2">
                            ${(Array.isArray(q.options) ? q.options : JSON.parse(q.options)).map((opt, i) => `
                                <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-white cursor-pointer transition">
                                    <input type="radio" name="question-${q.id}" value="${opt}" 
                                           onchange="submitAnswer(${q.id}, '${opt}')"
                                           class="w-4 h-4 text-blue-600">
                                    <span class="ml-3 text-gray-700">${opt}</span>
                                </label>
                            `).join('')}
                        </div>
                    ` : `
                        <textarea id="answer-${q.id}" rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Nhập câu trả lời của bạn..."></textarea>
                        <button onclick="submitAnswer(${q.id}, document.getElementById('answer-${q.id}').value)" 
                                class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                            Gửi câu trả lời
                        </button>
                    `}
                    
                    <div id="feedback-${q.id}" class="mt-3 hidden"></div>
                </div>
            `).join('');
        }

        // Submit Answer
        async function submitAnswer(questionId, answer) {
            try {
                const response = await fetch('/learning/answer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        question_id: questionId,
                        answer: answer,
                        segment_id: currentSegmentId
                    })
                });

                const data = await response.json();
                displayFeedback(questionId, data);

            } catch (error) {
                console.error('Error submitting answer:', error);
            }
        }

        // Display Feedback
        function displayFeedback(questionId, data) {
            const feedbackDiv = document.getElementById(`feedback-${questionId}`);
            feedbackDiv.classList.remove('hidden');

            const isCorrect = data.is_correct;
            feedbackDiv.innerHTML = `
                <div class="p-4 rounded-lg ${isCorrect ? 'bg-green-50 border border-green-200' : 'bg-yellow-50 border border-yellow-200'}">
                    <div class="flex items-start space-x-2">
                        <i class="fas ${isCorrect ? 'fa-check-circle text-green-600' : 'fa-lightbulb text-yellow-600'} mt-1"></i>
                        <div class="flex-1">
                            <p class="font-medium ${isCorrect ? 'text-green-900' : 'text-yellow-900'} mb-1">
                                ${isCorrect ? 'Chính xác! 🎉' : 'Chưa chính xác'}
                            </p>
                            <p class="text-sm ${isCorrect ? 'text-green-700' : 'text-yellow-700'}">
                                ${data.feedback}
                            </p>
                        </div>
                    </div>
                </div>
            `;

            if (isCorrect) {
                // Mark segment as completed after answering all questions correctly
                checkSegmentCompletion();
            }
        }

        // Check if segment is completed
        function checkSegmentCompletion() {
            // Logic to check if all questions answered correctly
            completedSegments.add(currentSegmentId);
            document.querySelector(`.segment-check-${currentSegmentId}`).classList.remove('hidden');
            updateProgress();
        }

        // Update Progress
        function updateProgress() {
            const progress = (completedSegments.size / totalSegments) * 100;
            document.getElementById('overall-progress').style.width = `${progress}%`;
            document.getElementById('progress-text').textContent = `${Math.round(progress)}%`;
        }

        // Navigation
        function updateNavigationButtons() {
            document.getElementById('prev-btn').disabled = currentSegmentOrder === 1;
            document.getElementById('next-btn').textContent = currentSegmentOrder === totalSegments ? 'Hoàn thành' : 'Phần tiếp theo';
        }

        document.getElementById('next-btn').addEventListener('click', function () {
            if (currentSegmentOrder < totalSegments) {
                const nextSegment = document.querySelector(`[data-segment-order="${currentSegmentOrder + 1}"]`);
                if (nextSegment) {
                    loadSegment(nextSegment.dataset.segmentId);
                }
            } else {
                // Complete lesson
                if (confirm('Bạn đã hoàn thành bài học! Xem kết quả?')) {
                    window.location.href = '/lessons/{{ $lesson->id }}/results';
                }
            }
        });

        document.getElementById('prev-btn').addEventListener('click', function () {
            if (currentSegmentOrder > 1) {
                const prevSegment = document.querySelector(`[data-segment-order="${currentSegmentOrder - 1}"]`);
                if (prevSegment) {
                    loadSegment(prevSegment.dataset.segmentId);
                }
            }
        });

        // Update Sidebar Selection
        function updateSidebarSelection(segmentId) {
            document.querySelectorAll('.segment-item').forEach(item => {
                item.classList.remove('bg-blue-50', 'border-l-4', 'border-blue-500');
            });
            document.querySelector(`[data-segment-id="${segmentId}"]`).classList.add('bg-blue-50', 'border-l-4', 'border-blue-500');
        }

        // Audio Player
        function setupAudioPlayer() {
            const playBtn = document.getElementById('play-audio-btn');
            const progressBar = document.getElementById('audio-progress');
            const timeDisplay = document.getElementById('audio-time');

            playBtn.addEventListener('click', function () {
                if (audioPlayer.paused) {
                    audioPlayer.play();
                    playBtn.innerHTML = '<i class="fas fa-pause"></i>';
                } else {
                    audioPlayer.pause();
                    playBtn.innerHTML = '<i class="fas fa-play"></i>';
                }
            });

            audioPlayer.addEventListener('timeupdate', function () {
                const progress = (audioPlayer.currentTime / audioPlayer.duration) * 100;
                progressBar.style.width = `${progress}%`;
                timeDisplay.textContent = `${formatTime(audioPlayer.currentTime)} / ${formatTime(audioPlayer.duration)}`;
            });

            audioPlayer.addEventListener('ended', function () {
                playBtn.innerHTML = '<i class="fas fa-play"></i>';
            });
        }

        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }

        // Chat Functions
        function toggleChat() {
            chatOpen = !chatOpen;
            const chatBox = document.getElementById('chat-box');
            const chevron = document.getElementById('chat-chevron');

            if (chatOpen) {
                chatBox.classList.remove('hidden');
                chevron.classList.remove('fa-chevron-down');
                chevron.classList.add('fa-chevron-up');
            } else {
                chatBox.classList.add('hidden');
                chevron.classList.remove('fa-chevron-up');
                chevron.classList.add('fa-chevron-down');
            }
        }

        async function sendMessage() {
            const input = document.getElementById('chat-input');
            const message = input.value.trim();

            if (!message) return;

            // Add user message
            addChatMessage(message, 'user');
            input.value = '';

            // Show typing indicator
            const typingId = addChatMessage('Đang suy nghĩ...', 'ai', true);

            try {
                const response = await fetch('/learning/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        message: message,
                        segment_id: currentSegmentId,
                        lesson_id: {{ $lesson->id }}
                    })
                });

                const data = await response.json();

                // Remove typing indicator
                document.getElementById(typingId).remove();

                // Add AI response
                addChatMessage(data.response, 'ai');

            } catch (error) {
                console.error('Error sending message:', error);
                document.getElementById(typingId).remove();
                addChatMessage('Xin lỗi, có lỗi xảy ra. Vui lòng thử lại!', 'ai');
            }
        }

        function addChatMessage(text, sender, isTyping = false) {
            const messagesDiv = document.getElementById('chat-messages');
            const messageId = `msg-${Date.now()}`;

            const messageHTML = sender === 'user' ? `
                <div class="chat-message flex items-start space-x-3 mb-4 justify-end">
                    <div class="bg-blue-600 text-white rounded-lg p-3 shadow-sm max-w-md">
                        <p class="text-sm">${text}</p>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user text-gray-600 text-sm"></i>
                    </div>
                </div>
            ` : `
                <div id="${messageId}" class="chat-message flex items-start space-x-3 mb-4">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-robot text-white text-sm"></i>
                    </div>
                    <div class="flex-1 bg-white rounded-lg p-3 shadow-sm max-w-md">
                        <p class="text-sm text-gray-700">${isTyping ? '<i class="fas fa-circle-notch fa-spin"></i> ' : ''}${text}</p>
                    </div>
                </div>
            `;

            messagesDiv.insertAdjacentHTML('beforeend', messageHTML);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;

            return messageId;
        }

        // Fullscreen
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }
    </script>
</body>

</html>