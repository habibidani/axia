<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Info -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    @if($company && $company->name)
                        <h1 class="text-2xl font-bold text-gray-900">{{ $company->name }}</h1>
                        <div class="mt-1 flex items-center gap-3 text-sm text-gray-600">
                            @if($company->business_model)
                                <span class="capitalize">{{ str_replace('_', ' ', $company->business_model) }}</span>
                            @endif
                            @if($company->team_cofounders || $company->team_employees)
                                <span>•</span>
                                <span>{{ $company->team_cofounders ?? 0 }} founders · {{ $company->team_employees ?? 0 }} employees</span>
                            @endif
                        </div>
                    @else
                        <h1 class="text-2xl font-bold text-gray-900">Welcome to axia</h1>
                    @endif
                </div>

                @if($lastRun)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4">
                        <div class="text-sm text-gray-600 mb-1">Last focus score</div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-bold text-gray-900">{{ $lastRun->overall_score }}</span>
                            <span class="text-lg text-gray-600">/100</span>
                        </div>
                    </div>
                @endif

                <div class="flex gap-2">
                    <a href="{{ route('company.edit') }}" wire:navigate class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                        Edit company
                    </a>
                    <a href="{{ route('goals.edit') }}" wire:navigate class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                        Edit goals & KPIs
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Main Card -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-rose-500 to-pink-500 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                A
                            </div>
                            <div class="flex-1 bg-gray-50 rounded-xl px-4 py-3">
                                <p class="text-sm text-gray-700">Hi, I'm axia, your AI focus coach.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-rose-500 to-pink-500 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                A
                            </div>
                            <div class="flex-1 bg-gray-50 rounded-xl px-4 py-3">
                                <p class="text-sm text-gray-700">Drop your to-dos here and I'll show you what really matters.</p>
                            </div>
                        </div>
                    </div>

                    <h2 class="text-xl font-bold text-gray-900 mb-2">Paste your to-do list</h2>
                    <p class="text-sm text-gray-600 mb-6">Paste your tasks for this week. axia will analyze them against your goals and KPIs.</p>

                    @if (session()->has('error'))
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                            <p class="text-sm text-red-800">{{ session('error') }}</p>
                        </div>
                    @endif

                    @if(!$showCsvUpload)
                        <form wire:submit.prevent="analyzeTodos">
                            <textarea
                                wire:model="todoText"
                                rows="12"
                                placeholder="One task per line...

Example:
Review Q1 metrics with team
Hire senior engineer
Update investor deck"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none text-sm"
                            ></textarea>

                            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                                <button
                                    type="submit"
                                    class="flex-1 bg-gradient-to-r from-rose-500 to-pink-500 text-white font-semibold py-3 px-6 rounded-xl hover:from-rose-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 shadow-sm"
                                >
                                    Analyze my to-dos
                                </button>
                                <button
                                    type="button"
                                    wire:click="$set('showCsvUpload', true)"
                                    class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50"
                                >
                                    Upload CSV instead
                                </button>
                            </div>
                        </form>
                    @else
                        <form wire:submit.prevent="uploadCsv">
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center">
                                <input
                                    type="file"
                                    wire:model="csvFile"
                                    accept=".csv,.txt"
                                    class="hidden"
                                    id="csvFile"
                                />
                                <label for="csvFile" class="cursor-pointer">
                                    <div class="text-sm font-medium text-gray-900">Click to upload CSV</div>
                                    <p class="text-xs text-gray-500 mt-1">CSV must include a "Task" column</p>
                                </label>
                                @if($csvFile)
                                    <p class="mt-2 text-sm text-green-600">Selected: {{ $csvFile->getClientOriginalName() }}</p>
                                @endif
                            </div>

                            <div class="mt-6 flex gap-3">
                                <button
                                    type="submit"
                                    class="flex-1 bg-gradient-to-r from-rose-500 to-pink-500 text-white font-semibold py-3 px-6 rounded-xl hover:from-rose-600 hover:to-pink-600 shadow-sm"
                                    @disabled(!$csvFile)
                                >
                                    Analyze CSV
                                </button>
                                <button
                                    type="button"
                                    wire:click="$set('showCsvUpload', false)"
                                    class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50"
                                >
                                    Use text instead
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Top KPI Sidebar -->
            <div class="lg:col-span-1">
                @if($topKpi)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <h3 class="text-sm font-semibold text-gray-900">Top KPI</h3>
                            <span class="inline-flex items-center px-2 py-1 rounded-md bg-rose-50 text-rose-700 text-xs font-medium">
                                Primary
                            </span>
                        </div>
                        
                        <h4 class="text-lg font-bold text-gray-900 mb-4">{{ $topKpi->name }}</h4>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-baseline">
                                <span class="text-sm text-gray-600">Current</span>
                                <span class="text-xl font-bold text-gray-900">
                                    {{ number_format($topKpi->current_value, 0) }} <span class="text-sm font-normal text-gray-600">{{ $topKpi->unit }}</span>
                                </span>
                            </div>
                            <div class="flex justify-between items-baseline">
                                <span class="text-sm text-gray-600">Target</span>
                                <span class="text-xl font-bold text-rose-600">
                                    {{ number_format($topKpi->target_value, 0) }} <span class="text-sm font-normal text-gray-600">{{ $topKpi->unit }}</span>
                                </span>
                            </div>
                        </div>
                        
                        <p class="mt-4 text-xs text-gray-500">
                            Analyses are primarily aligned with this KPI.
                        </p>
                    </div>
                @else
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">No Top KPI Set</h3>
                        <p class="text-sm text-gray-600 mb-4">Set a top KPI to get focused insights.</p>
                        <a href="{{ route('goals.edit') }}" wire:navigate class="inline-flex items-center text-sm font-medium text-rose-600 hover:text-rose-700">
                            Set your KPIs →
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
