<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Nếu bạn muốn dùng Tailwind CSS cho Blade, bạn cần chạy Vite hoặc Laravel Mix --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Hoặc link CSS trực tiếp nếu bạn không dùng build tool --}}
    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.header') {{-- Sẽ tạo partial view cho header --}}

        @hasSection('header')
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    @yield('header')
                </div>
            </header>
        @endif

        <main>
            @yield('content')
        </main>

        @include('layouts.footer') {{-- Sẽ tạo partial view cho footer --}}

        <div id="chatbot-container"></div> {{-- Một div để JS inject chatbot --}}
    </div>

    {{-- Nếu bạn muốn dùng JavaScript cho các tương tác nhỏ --}}
    {{-- <script src="{{ asset('js/app.js') }}"></script> --}}
    @stack('scripts') {{-- Để thêm script riêng cho từng trang --}}
    <script>
        // JS thuần cho chatbot bubble
        document.addEventListener('DOMContentLoaded', () => {
            const chatbotContainer = document.getElementById('chatbot-container');
            if (!chatbotContainer) return;

            const chatBubble = document.createElement('button');
            chatBubble.className = 'fixed bottom-6 right-6 z-50 bg-green-500 hover:bg-green-600 text-white p-4 rounded-full shadow-lg transition-all duration-300 ease-in-out transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-opacity-75';
            chatBubble.innerHTML = `
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
            `;
            chatbotContainer.appendChild(chatBubble);

            let isChatOpen = false;
            let chatWindow = null;

            chatBubble.addEventListener('click', () => {
                isChatOpen = !isChatOpen;
                if (isChatOpen) {
                    chatWindow = createChatWindow();
                    chatbotContainer.appendChild(chatWindow);
                } else {
                    if (chatWindow) {
                        chatWindow.remove();
                        chatWindow = null;
                    }
                }
            });

            function createChatWindow() {
                const window = document.createElement('div');
                window.className = 'fixed bottom-20 right-6 w-96 h-[500px] bg-white rounded-lg shadow-xl flex flex-col border border-gray-200 animate-fade-in';
                window.innerHTML = `
                    <div class="bg-blue-600 text-white p-4 flex justify-between items-center rounded-t-lg">
                        <h3 class="text-lg font-bold">Chatbot BeeMarket</h3>
                        <button class="close-chat-btn text-white hover:text-gray-200 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 space-y-3 chat-body">
                        <div class="flex justify-start">
                            <div class="max-w-[70%] p-2 rounded-lg bg-gray-200 text-gray-800">
                                Chào bạn! Tôi là chatbot của BeeMarket. Bạn có câu hỏi gì về cửa hàng không?
                            </div>
                        </div>
                        <div id="messages-container"></div>
                        <div id="loading-indicator" class="hidden flex justify-start">
                            <div class="bg-gray-200 text-gray-800 p-2 rounded-lg">
                                <span>Đang nghĩ...</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border-t border-gray-200">
                        <div class="flex">
                            <input
                                type="text"
                                placeholder="Nhập tin nhắn của bạn..."
                                class="flex-grow border border-gray-300 rounded-l-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 chat-input"
                            />
                            <button
                                class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-blue-500 send-chat-btn"
                            >
                                Gửi
                            </button>
                        </div>
                    </div>
                `;

                window.querySelector('.close-chat-btn').addEventListener('click', () => {
                    isChatOpen = false;
                    window.remove();
                    chatWindow = null;
                });

                const messagesContainer = window.querySelector('#messages-container');
                const loadingIndicator = window.querySelector('#loading-indicator');
                const chatInput = window.querySelector('.chat-input');
                const sendBtn = window.querySelector('.send-chat-btn');
                const chatBody = window.querySelector('.chat-body');

                const addMessage = (text, sender) => {
                    const msgDiv = document.createElement('div');
                    msgDiv.className = `flex ${sender === 'user' ? 'justify-end' : 'justify-start'}`;
                    msgDiv.innerHTML = `
                        <div class="max-w-[70%] p-2 rounded-lg ${sender === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'}">
                            ${text}
                        </div>
                    `;
                    messagesContainer.appendChild(msgDiv);
                    chatBody.scrollTop = chatBody.scrollHeight; // Auto-scroll
                };

                const sendMessage = async () => {
                    const userMessage = chatInput.value.trim();
                    if (userMessage === '') return;

                    addMessage(userMessage, 'user');
                    chatInput.value = '';
                    loadingIndicator.classList.remove('hidden'); // Show loading

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch('/api/chatbot/ask', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken // Gửi CSRF token
                            },
                            body: JSON.stringify({ message: userMessage })
                        });
                        const data = await response.json();
                        addMessage(data.answer, 'bot');
                    } catch (error) {
                        console.error('Lỗi khi gửi tin nhắn đến chatbot:', error);
                        addMessage('Xin lỗi, tôi không thể trả lời lúc này. Vui lòng thử lại sau.', 'bot');
                    } finally {
                        loadingIndicator.classList.add('hidden'); // Hide loading
                    }
                };

                sendBtn.addEventListener('click', sendMessage);
                chatInput.addEventListener('keyup', (event) => {
                    if (event.key === 'Enter') {
                        sendMessage();
                    }
                });

                return window;
            }
        });
    </script>
    <style>
        /* Basic animation for fade-in */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        /* Style cho scrollbar */
        .chat-body::-webkit-scrollbar {
            width: 8px;
        }
        .chat-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .chat-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        .chat-body::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</body>
</html>