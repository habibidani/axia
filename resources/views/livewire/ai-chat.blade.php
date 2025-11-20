<div class="ai-chat-container">
    <div class="bg-white rounded-lg shadow-lg h-[600px] flex flex-col">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
                <h3 class="text-lg font-semibold">Axia AI Assistant</h3>
            </div>
            <button wire:click="clearChat" class="hover:bg-blue-800 p-2 rounded transition" title="Chat zurücksetzen">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4">
            @if(empty($messages))
                <div class="text-center text-gray-400 mt-20">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <p class="text-lg">Stellen Sie eine Frage zu Ihren Zielen, KPIs oder TODOs</p>
                    <p class="text-sm mt-2">Der AI Assistant hilft Ihnen bei der Analyse und Planung</p>
                </div>
            @else
                @foreach($messages as $msg)
                    <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] {{ $msg['role'] === 'user' ? 'bg-blue-600 text-white' : ($msg['role'] === 'error' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }} rounded-lg px-4 py-3">
                            <div class="flex items-start gap-2">
                                @if($msg['role'] !== 'user')
                                    <svg class="w-5 h-5 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                @endif
                                <div class="flex-1">
                                    <div class="text-sm">{{ $msg['content'] }}</div>
                                    <div class="text-xs opacity-70 mt-1">{{ $msg['timestamp'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            @if($isLoading)
                <div class="flex justify-start">
                    <div class="bg-gray-100 rounded-lg px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex space-x-1">
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                            </div>
                            <span class="text-sm text-gray-600">AI denkt nach...</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Input -->
        <div class="border-t p-4">
            <form wire:submit.prevent="sendMessage" class="flex gap-2">
                <input 
                    type="text" 
                    wire:model="message" 
                    placeholder="Schreiben Sie Ihre Nachricht..."
                    class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    @if($isLoading) disabled @endif
                >
                <button 
                    type="submit" 
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    @if($isLoading || empty(trim($message))) disabled @endif
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Senden
                </button>
            </form>
            @if($sessionId)
                <div class="text-xs text-gray-500 mt-2">
                    Session: {{ substr($sessionId, 0, 8) }}...
                </div>
            @endif
        </div>
    </div>
</div>
