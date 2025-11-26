<div class="min-h-screen bg-linear-to-br from-green-50 via-green-100 to-green-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    @if ($company && $company->name)
                        <h1 class="text-4xl font-bold text-gray-900">{{ $company->name }}</h1>
                        @if ($company->business_model)
                            <p class="text-base text-gray-600 mt-2 capitalize">
                                {{ str_replace('_', ' ', $company->business_model) }}</p>
                        @endif
                    @else
                        <h1 class="text-4xl font-bold text-gray-900">axia Dashboard</h1>
                    @endif
                </div>
            </div>

            <!-- Quick Actions + Last Score -->
            <div class="flex items-center gap-3">
                <a href="{{ route('company.edit') }}" wire:navigate
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white/80 backdrop-blur border border-gray-300 rounded-lg hover:bg-white hover:border-gray-400 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Company
                </a>
                <a href="{{ route('goals.edit') }}" wire:navigate
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white/80 backdrop-blur border border-gray-300 rounded-lg hover:bg-white hover:border-gray-400 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Goals & KPIs
                </a>
                @if ($lastRun)
                    <a href="{{ route('results.show', $lastRun) }}" wire:navigate
                        class="ml-auto inline-flex items-center gap-3 px-5 py-2.5 bg-white/90 backdrop-blur border-2 border-green-500 rounded-xl hover:shadow-xl hover:scale-105 transition-all">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span class="text-sm font-semibold text-gray-700">Last Evaluation:</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-2xl font-bold text-green-700">{{ $lastRun->overall_score }}</span>
                            <span class="text-sm text-gray-500">/100</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @endif
            </div>
        </div>

        <!-- Grid Layout: 2 Spalten -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Linke Spalte: Main Goal + Recent Todos -->
            <div class="space-y-6">

                <!-- Main Goal Card -->
                <div x-data="{ open: true }"
                    class="bg-white/90 backdrop-blur border-2 border-gray-800 rounded-2xl shadow-xl overflow-hidden h-[400px] flex flex-col">
                    <button @click="open = !open"
                        class="shrink-0 px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors border-b-2 border-gray-200">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-linear-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                            </div>
                            <h2 class="text-lg font-bold text-gray-900">Main Goal</h2>
                        </div>
                        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'rotate-180': open }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-collapse class="flex-1 overflow-y-auto px-6 py-4 custom-scrollbar">
                        @if ($topGoal)
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $topGoal->name }}</h3>
                            @if ($topGoal->description)
                                <p class="text-sm text-gray-600 mb-4">{{ $topGoal->description }}</p>
                            @endif
                            @if ($topKpi)
                                <div
                                    class="mt-4 p-4 bg-linear-to-br from-green-50 to-emerald-50 rounded-xl border border-green-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-semibold text-gray-900">{{ $topKpi->name }}</span>
                                        <span
                                            class="text-xs px-2 py-1 bg-green-600 text-white rounded-full font-medium">Top
                                            KPI</span>
                                    </div>
                                    <div class="flex items-baseline gap-2 mb-3">
                                        <span
                                            class="text-3xl font-bold text-gray-900">{{ number_format($topKpi->current_value, 0) }}</span>
                                        <span class="text-sm text-gray-600">/
                                            {{ number_format($topKpi->target_value, 0) }} {{ $topKpi->unit }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-linear-to-r from-green-500 to-emerald-600 h-2.5 rounded-full transition-all"
                                            style="width: {{ min(100, ($topKpi->current_value / $topKpi->target_value) * 100) }}%">
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">
                                        {{ number_format(($topKpi->current_value / $topKpi->target_value) * 100, 1) }}%
                                        complete</p>
                                </div>
                            @endif
                        @else
                            <p class="text-sm text-gray-500">No goals set yet. <a href="{{ route('goals.edit') }}"
                                    wire:navigate class="text-green-700 font-medium hover:text-green-800 underline">Add
                                    a goal →</a></p>
                        @endif
                    </div>
                </div>

                <!-- Recent Todos Card -->
                <div x-data="{ open: true }"
                    class="bg-white/90 backdrop-blur border-2 border-gray-800 rounded-2xl shadow-xl overflow-hidden h-[400px] flex flex-col">
                    <button @click="open = !open"
                        class="shrink-0 px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors border-b-2 border-gray-200">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-linear-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <h2 class="text-lg font-bold text-gray-900">Recent Todos</h2>
                            @if ($lastRun && $lastRun->todos->count() > 0)
                                <span
                                    class="ml-1 px-2 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">{{ $lastRun->todos->count() }}</span>
                            @endif
                        </div>
                        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'rotate-180': open }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-collapse class="flex-1 overflow-y-auto px-6 py-4 custom-scrollbar">
                        @if ($lastRun && $lastRun->todos->count() > 0)
                            <div class="space-y-2">
                                @foreach ($lastRun->todos->take(8) as $todo)
                                    <div
                                        class="group p-3 bg-linear-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all">
                                        <div class="flex items-start justify-between gap-3">
                                            <p class="text-sm text-gray-800 flex-1 leading-relaxed">
                                                {{ $todo->task }}</p>
                                            <div class="shrink-0 text-right">
                                                <div
                                                    class="px-2 py-1 bg-white rounded-md border border-gray-300 group-hover:border-blue-400 transition-colors">
                                                    <span
                                                        class="text-sm font-bold text-gray-900">{{ $todo->final_score }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No todos analyzed yet. Start chatting to get AI-powered
                                insights!</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Evaluation Results Card (NEUE ANZEIGE) -->
            @if ($lastRun)
                <div x-data="{ open: true }"
                    class="bg-white/90 backdrop-blur border-2 border-gray-800 rounded-2xl shadow-xl overflow-hidden flex flex-col"
                    style="height: 600px;">
                    <button @click="open = !open"
                        class="shrink-0 px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors border-b-2 border-gray-200">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-linear-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <h2 class="text-lg font-bold text-gray-900">Latest Evaluation</h2>
                            <span
                                class="ml-1 px-3 py-1 bg-green-500 text-white text-sm font-bold rounded-full">{{ $lastRun->overall_score }}/100</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'rotate-180': open }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-collapse class="flex-1 overflow-y-auto px-6 py-4 custom-scrollbar">
                        <!-- Score Trend Chart -->
                        <div class="mb-6 p-6 bg-linear-to-br from-green-50 to-emerald-50 rounded-xl border-2 border-green-300">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-sm font-semibold text-gray-700">Score Trend</span>
                                <span class="text-4xl font-bold text-green-700">{{ $lastRun->overall_score }}<span
                                        class="text-xl text-gray-500">/100</span></span>
                            </div>
                            <div style="height: 150px;">
                                <canvas id="scoreChart"></canvas>
                            </div>
                        </div>

                        <!-- Todo Distribution Chart -->
                        <div class="mb-6 p-4 bg-white rounded-xl border border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Task Distribution</h4>
                            <div style="height: 200px;">
                                <canvas id="todoDistChart"></canvas>
                            </div>
                        </div>

                        <!-- Analysis Stats -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="text-2xl font-bold text-blue-700">{{ $lastRun->todos->count() }}</div>
                                <div class="text-xs text-gray-600 mt-1">Todos Analyzed</div>
                            </div>
                            <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                                <div class="text-2xl font-bold text-purple-700">
                                    {{ $lastRun->created_at->diffForHumans() }}</div>
                                <div class="text-xs text-gray-600 mt-1">Last Run</div>
                            </div>
                        </div>

                        <!-- View Full Results Button -->
                        <a href="{{ route('results.show', $lastRun) }}" wire:navigate
                            class="block w-full px-6 py-3 bg-linear-to-r from-purple-600 to-pink-600 text-white text-center font-bold rounded-xl hover:shadow-xl hover:scale-105 transition-all">
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                View Full Evaluation Results
                            </div>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Rechte Spalte: AI Chat -->
            <div x-data="{ open: true }"
                class="bg-white/90 backdrop-blur border-2 border-gray-800 rounded-2xl shadow-xl overflow-hidden h-[824px] flex flex-col">
                <button @click="open = !open"
                    class="shrink-0 px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors border-b-2 border-gray-200">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-linear-to-br from-rose-500 to-pink-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">AI Assistant Chat</h2>
                        <div class="ml-2 w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    </div>
                    <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'rotate-180': open }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-collapse class="flex-1 flex flex-col overflow-hidden">
                    <!-- Chat Messages -->
                    <div class="px-6">
                        <div id="chatMessages" class="h-96 overflow-y-auto space-y-3 py-4 custom-scrollbar">
                            @forelse ($chatMessages as $msg)
                                @if ($msg['role'] === 'user')
                                    <div class="flex justify-end animate-fade-in">
                                        <div
                                            class="max-w-md bg-linear-to-r from-rose-500 to-pink-600 text-white px-4 py-3 rounded-2xl rounded-tr-sm shadow-lg">
                                            <p class="text-sm leading-relaxed">{{ $msg['content'] }}</p>
                                            <p class="text-xs opacity-75 mt-1.5">{{ $msg['timestamp'] }}</p>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex justify-start animate-fade-in">
                                        <div class="max-w-lg">
                                            <div class="flex items-start gap-3">
                                                <div
                                                    class="shrink-0 w-8 h-8 bg-linear-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center">
                                                    <span class="text-white text-xs font-bold">A</span>
                                                </div>
                                                <div
                                                    class="flex-1 bg-gray-100 px-4 py-3 rounded-2xl rounded-tl-sm border border-gray-200 shadow-sm">
                                                    <p class="text-sm text-gray-800 leading-relaxed">
                                                        {{ $msg['content'] }}</p>
                                                    <p class="text-xs text-gray-500 mt-1.5">{{ $msg['timestamp'] }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="flex items-center justify-center h-full">
                                    <div class="text-center">
                                        <div
                                            class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm text-gray-500">Start a conversation with your AI assistant
                                        </p>
                                    </div>
                                </div>
                            @endforelse

                            <!-- Loading indicator -->
                            <div wire:loading wire:target="sendMessage" class="flex justify-start animate-fade-in">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="shrink-0 w-8 h-8 bg-linear-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center">
                                        <span class="text-white text-xs font-bold">A</span>
                                    </div>
                                    <div
                                        class="bg-gray-100 px-4 py-3 rounded-2xl rounded-tl-sm border border-gray-200">
                                        <div class="flex items-center gap-2">
                                            <div class="flex gap-1">
                                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
                                                    style="animation-delay: 0ms"></div>
                                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
                                                    style="animation-delay: 150ms"></div>
                                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
                                                    style="animation-delay: 300ms"></div>
                                            </div>
                                            <span class="text-sm text-gray-600">Thinking...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Input -->
                    <div class="px-6 pb-6 pt-4 border-t border-gray-200 bg-gray-50/50">
                        @if (session()->has('error'))
                            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg flex items-start gap-2">
                                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm text-red-800">{{ session('error') }}</p>
                            </div>
                        @endif

                        <form wire:submit.prevent="sendMessage" class="flex gap-3">
                            <input type="text" wire:model="chatInput"
                                placeholder="Ask about your goals, get strategy advice, analyze todos..."
                                class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm bg-white shadow-sm transition-all"
                                wire:loading.attr="disabled" wire:target="sendMessage" />
                            <button type="submit" wire:loading.attr="disabled" wire:target="sendMessage"
                                class="px-6 py-3 bg-linear-to-r from-green-600 to-emerald-600 text-white font-semibold rounded-xl hover:from-green-700 hover:to-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg hover:shadow-xl">
                                <span wire:loading.remove wire:target="sendMessage" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                    Send
                                </span>
                                <span wire:loading wire:target="sendMessage">
                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Floating Calendar Widget - FIXED BOTTOM RIGHT (inside main container) -->
    <div x-data="{ calendarOpen: false }" class="fixed" style="bottom: 2rem; right: 2rem; z-index: 9999;">
        <!-- Calendar Button -->
        <button @click="calendarOpen = !calendarOpen"
            class="group relative w-16 h-16 bg-linear-to-br from-green-500 to-emerald-600 text-white rounded-2xl shadow-2xl hover:shadow-green-500/60 hover:scale-110 transition-all duration-300 flex items-center justify-center border-3 border-white">
            <svg class="w-8 h-8 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            @if (($lastRun ? 1 : 0) + ($topGoal?->kpis->count() ?? 0) > 0)
                <div
                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 rounded-full border-2 border-white flex items-center justify-center animate-pulse shadow-lg">
                    <span class="text-xs font-bold">{{ ($lastRun ? 1 : 0) + ($topGoal?->kpis->count() ?? 0) }}</span>
                </div>
            @endif
            <!-- Hover Tooltip -->
            <div
                class="absolute bottom-full right-0 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none shadow-xl">
                📅 View Calendar
            </div>
        </button>

        <!-- Calendar Panel - erscheint ÜBER dem Button -->
        <div x-show="calendarOpen" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" @click.away="calendarOpen = false"
            class="absolute w-[520px] bg-white border-4 border-green-500 rounded-2xl shadow-2xl overflow-hidden"
            style="bottom: 5rem; right: 0;">
            <!-- Header -->
            <div class="px-6 py-4 bg-linear-to-r from-green-500 to-emerald-600 border-b-2 border-green-600">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar & Events
                    </h3>
                    <button @click="calendarOpen = false"
                        class="text-white/90 hover:text-white hover:scale-110 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- FullCalendar Container -->
            <div id="calendar" class="p-5 bg-white"></div>

            <!-- Upcoming Events -->
            <div class="px-6 pb-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Upcoming Events
                </h4>
                <div class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar">
                    @if ($lastRun)
                        <div
                            class="p-3 bg-linear-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200 hover:shadow-md transition-all">
                            <div class="flex items-start gap-3">
                                <div class="shrink-0 w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900">Last Analysis</p>
                                    <p class="text-xs text-gray-600">
                                        {{ $lastRun->created_at->format('M d, Y · H:i') }}</p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                                            <div class="bg-green-600 h-1.5 rounded-full"
                                                style="width: {{ $lastRun->overall_score }}%"></div>
                                        </div>
                                        <span
                                            class="text-xs font-bold text-green-700">{{ $lastRun->overall_score }}/100</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @foreach (($topGoal?->kpis ?? collect())->take(3) as $kpi)
                        <div
                            class="p-3 bg-linear-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200 hover:shadow-md transition-all">
                            <div class="flex items-start gap-3">
                                <div class="shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $kpi->name }}</p>
                                    <p class="text-xs text-gray-600">Target:
                                        {{ number_format($kpi->target_value, 0) }} {{ $kpi->unit }}</p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                                            <div class="bg-blue-600 h-1.5 rounded-full"
                                                style="width: {{ min(100, ($kpi->current_value / $kpi->target_value) * 100) }}%">
                                            </div>
                                        </div>
                                        <span
                                            class="text-xs font-bold text-blue-700">{{ number_format(($kpi->current_value / $kpi->target_value) * 100, 0) }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if (!$lastRun && !$topGoal)
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 text-center">
                            <p class="text-xs text-gray-500">No events yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
<!-- End of calendar widget -->
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Score Trend Chart
            const scoreCtx = document.getElementById('scoreChart');
            if (scoreCtx && window.Chart) {
                const scoreData = {!! json_encode($scoreHistory ?? []) !!};
                new Chart(scoreCtx, {
                    type: 'line',
                    data: {
                        labels: scoreData.map(item => item.date),
                        datasets: [{
                            label: 'Focus Score',
                            data: scoreData.map(item => item.score),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                cornerRadius: 8,
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 13
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    color: '#6b7280',
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#6b7280',
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // Todo Distribution Chart
            const distCtx = document.getElementById('todoDistChart');
            if (distCtx && window.Chart) {
                const distData = {!! json_encode($todoDistribution ?? ['high' => 0, 'medium' => 0, 'low' => 0]) !!};
                new Chart(distCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['High Priority (80+)', 'Medium (50-79)', 'Low Priority (<50)'],
                        datasets: [{
                            data: [distData.high, distData.medium, distData.low],
                            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                            borderColor: '#fff',
                            borderWidth: 3,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 11,
                                        weight: '600'
                                    },
                                    color: '#374151',
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? Math.round((context.parsed /
                                            total) * 100) : 0;
                                        return context.label + ': ' + context.parsed + ' (' +
                                            percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // FullCalendar initialization
            const calendarEl = document.getElementById('calendar');
            if (calendarEl && window.FullCalendar) {
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,dayGridWeek'
                    },
                    height: 500,
                    locale: 'de',
                    events: {!! json_encode($events ?? []) !!},
                    eventClick: function(info) {
                        const props = info.event.extendedProps;
                        let details = info.event.title + '\n\n';

                        if (props.type === 'analysis') {
                            details += 'Score: ' + props.score + '/100\n';
                            details += 'Todos: ' + props.todosCount;
                        } else if (props.type === 'kpi') {
                            details += props.name + '\n';
                            details += 'Deadline: 30 Tage';
                        } else if (props.type === 'goal') {
                            details += props.description + '\n';
                            details += 'Priority: ' + props.priority;
                        }

                        alert(details);
                    }
                });

                calendar.render();
            }

            // Chat auto-scroll on new messages
            document.addEventListener('livewire:update', () => {
                const chatMessages = document.getElementById('chatMessages');
                if (chatMessages) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            });
        });
    </script>
@endpush
