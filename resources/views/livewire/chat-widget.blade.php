<div>
    <!-- Chat Toggle Button (floating bottom-right) -->
    @if(!$isOpen)
        <button 
            wire:click="toggleChat"
            class="fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-blue-600 text-white shadow-lg transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600"
            aria-label="Chat öffnen"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
            </svg>
            @if(count($messages) > 1)
                <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
                    {{ count($messages) - 1 }}
                </span>
            @endif
        </button>
    @endif

    <!-- Chat Window -->
    @if($isOpen)
        <div class="fixed bottom-6 right-6 z-50 flex h-[600px] w-96 flex-col rounded-lg border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900">
            <!-- Header -->
            <div class="flex items-center justify-between rounded-t-lg border-b border-zinc-200 bg-blue-600 px-4 py-3 text-white dark:border-zinc-700 dark:bg-blue-500">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                    <h3 class="font-semibold">AI Assistent</h3>
                </div>
                <div class="flex items-center gap-2">
                    <button 
                        wire:click="clearChat"
                        class="rounded p-1 transition hover:bg-blue-700 dark:hover:bg-blue-600"
                        title="Chat zurücksetzen"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                    <button 
                        wire:click="toggleChat"
                        class="rounded p-1 transition hover:bg-blue-700 dark:hover:bg-blue-600"
                        title="Chat schließen"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 space-y-4 overflow-y-auto p-4" id="chat-messages">
                @foreach($messages as $index => $message)
                    <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="flex max-w-[85%] flex-col gap-1">
                            <div class="rounded-lg px-4 py-2 {{ $message['role'] === 'user' 
                                ? 'bg-blue-600 text-white dark:bg-blue-500' 
                                : 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100' }}">
                                <p class="whitespace-pre-wrap text-sm leading-relaxed">{{ $message['content'] }}</p>
                            </div>
                            <span class="px-2 text-xs text-zinc-500 dark:text-zinc-400 {{ $message['role'] === 'user' ? 'text-right' : 'text-left' }}">
                                {{ $message['timestamp'] }}
                            </span>
                        </div>
                    </div>
                @endforeach

                @if($isLoading)
                    <div class="flex justify-start">
                        <div class="flex items-center gap-2 rounded-lg bg-zinc-100 px-4 py-2 dark:bg-zinc-800">
                            <div class="flex gap-1">
                                <div class="h-2 w-2 animate-bounce rounded-full bg-zinc-400 dark:bg-zinc-500" style="animation-delay: 0ms"></div>
                                <div class="h-2 w-2 animate-bounce rounded-full bg-zinc-400 dark:bg-zinc-500" style="animation-delay: 150ms"></div>
                                <div class="h-2 w-2 animate-bounce rounded-full bg-zinc-400 dark:bg-zinc-500" style="animation-delay: 300ms"></div>
                            </div>
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">AI antwortet...</span>
                        </div>
                    </div>
                @endif

                @if($error)
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
                        <div class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ $error }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Input Area -->
            <div class="border-t border-zinc-200 p-4 dark:border-zinc-700">
                <form wire:submit="sendMessage" class="flex gap-2">
                    <input 
                        type="text" 
                        wire:model="userInput"
                        placeholder="Schreibe eine Nachricht..."
                        class="flex-1 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-400"
                        :disabled="$isLoading"
                    />
                    <button 
                        type="submit"
                        :disabled="$isLoading || empty(trim($userInput))"
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </form>
                @if($sessionId)
                    <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                        Session aktiv
                    </p>
                @endif
            </div>
        </div>
    @endif

    <!-- Auto-scroll to bottom script -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('messageSent', () => {
                setTimeout(() => {
                    const messagesDiv = document.getElementById('chat-messages');
                    if (messagesDiv) {
                        messagesDiv.scrollTop = messagesDiv.scrollHeight;
                    }
                }, 100);
            });
        });

        // Also scroll on component update
        document.addEventListener('livewire:update', () => {
            setTimeout(() => {
                const messagesDiv = document.getElementById('chat-messages');
                if (messagesDiv) {
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                }
            }, 100);
        });
    </script>
</div>
