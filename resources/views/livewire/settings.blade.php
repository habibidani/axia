<div class="max-w-4xl mx-auto px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-[var(--text-primary)] mb-2">Settings</h1>
        <p>Manage your webhooks and integrations</p>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-[var(--accent-green-light)] border border-[rgba(76,175,80,0.3)] rounded-xl">
            <p class="text-sm text-[var(--accent-green)]">{{ session('success') }}</p>
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-[rgba(255,138,101,0.1)] border border-[rgba(255,138,101,0.3)] rounded-xl">
            <p class="text-sm text-[var(--accent-orange)]">{{ session('error') }}</p>
        </div>
    @endif

    <div class="space-y-8">
        <!-- Add New Webhook -->
        <div class="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)]">
            <h2 class="text-lg text-[var(--text-primary)] mb-6">Add New Webhook</h2>
            
            <form wire:submit="addWebhookPreset" class="space-y-5">
                <div class="space-y-2">
                    <label class="block text-sm text-[var(--text-primary)]">Name</label>
                    <input
                        type="text"
                        wire:model="newPresetName"
                        placeholder="e.g. Standard AI, Fast Model, Creative..."
                        required
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] transition-colors"
                    />
                </div>

                <div class="space-y-2">
                    <label class="block text-sm text-[var(--text-primary)]">Webhook URL</label>
                    <input
                        type="url"
                        wire:model="newPresetUrl"
                        placeholder="https://n8n.example.com/webhook/..."
                        required
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] transition-colors"
                    />
                </div>

                <div class="space-y-2">
                    <label class="block text-sm text-[var(--text-primary)]">Description (optional)</label>
                    <textarea
                        wire:model="newPresetDescription"
                        rows="2"
                        placeholder="e.g. Faster workflow with GPT-4o-mini"
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] transition-colors resize-none"
                    ></textarea>
                </div>

                <button
                    type="submit"
                    class="w-full px-6 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors"
                >
                    Save Webhook
                </button>
            </form>
        </div>

        <!-- Saved Webhooks -->
        <div class="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)]">
            <h2 class="text-lg text-[var(--text-primary)] mb-6">Saved Webhooks</h2>
            
            @if($presets->isEmpty())
                <p class="text-sm text-[var(--text-secondary)]">No webhooks saved yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($presets as $preset)
                        <div class="bg-[var(--bg-tertiary)] rounded-xl p-4 border {{ $preset->is_active ? 'border-[rgba(233,75,140,0.5)]' : 'border-[var(--border)]' }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <h3 class="text-sm font-medium text-[var(--text-primary)]">{{ $preset->name }}</h3>
                                        @if($preset->is_active)
                                            <span class="px-2 py-0.5 text-xs rounded bg-[var(--accent-pink-light)] text-[var(--accent-pink)] border border-[rgba(233,75,140,0.3)]">Active</span>
                                        @endif
                                        @if($preset->is_default)
                                            <span class="px-2 py-0.5 text-xs rounded bg-[var(--bg-secondary)] text-[var(--text-secondary)] border border-[var(--border)]">Default</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-[var(--text-secondary)] break-all mb-1">
                                        {{ $preset->webhook_url }}
                                    </p>
                                    @if($preset->description)
                                        <p class="text-xs text-[var(--text-muted)]">
                                            {{ $preset->description }}
                                        </p>
                                    @endif
                                </div>
                                
                                <div class="flex gap-2 shrink-0">
                                    @if(!$preset->is_active)
                                        <button
                                            wire:click="activatePreset('{{ $preset->id }}')"
                                            class="px-3 py-1.5 text-xs bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors"
                                        >
                                            Activate
                                        </button>
                                    @endif
                                    
                                    <button
                                        wire:click="testWebhook('{{ $preset->id }}')"
                                        class="px-3 py-1.5 text-xs bg-[var(--bg-secondary)] hover:bg-[var(--bg-hover)] text-[var(--text-primary)] border border-[var(--border)] rounded-lg transition-colors"
                                    >
                                        Test
                                    </button>
                                    
                                    @if(!$preset->is_default)
                                        <button
                                            wire:click="deletePreset('{{ $preset->id }}')"
                                            wire:confirm="Delete this webhook?"
                                            class="px-3 py-1.5 text-xs bg-[rgba(255,138,101,0.1)] hover:bg-[rgba(255,138,101,0.2)] text-[var(--accent-orange)] border border-[rgba(255,138,101,0.3)] rounded-lg transition-colors"
                                        >
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Chart Webhook -->
        <div class="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)]">
            <div class="flex items-center gap-2 mb-4">
                <h2 class="text-lg text-[var(--text-primary)]">Chart Webhook</h2>
                <span class="px-2 py-0.5 text-xs rounded bg-[var(--accent-pink-light)] text-[var(--accent-pink)] border border-[rgba(233,75,140,0.3)]">Specialized</span>
            </div>
            <p class="text-sm text-[var(--text-secondary)] mb-4">
                This webhook is used for chart generation (AntV Charts)
            </p>
            
            <div class="space-y-2">
                <label class="block text-sm text-[var(--text-primary)]">Chart Webhook URL</label>
                <input
                    type="url"
                    value="{{ auth()->user()->chart_webhook_url ?? '' }}"
                    readonly
                    class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)]"
                />
                <p class="text-xs text-[var(--text-secondary)]">
                    Status: {{ auth()->user()->chart_webhook_url ? '✓ Configured' : '✗ Not set' }}
                </p>
            </div>
        </div>

        <!-- Debug Info (only in debug mode) -->
        @if(config('app.debug'))
            <div class="bg-[var(--bg-tertiary)] rounded-xl p-4 border border-[var(--border)]">
                <h3 class="text-sm font-medium text-[var(--text-primary)] mb-2">Debug Info</h3>
                <div class="text-xs text-[var(--text-secondary)] space-y-1 font-mono">
                    <div>Presets Count: {{ $presets->count() }}</div>
                    <div>User Webhook: {{ auth()->user()->n8n_webhook_url ?? 'NULL' }}</div>
                    <div>Chart Webhook: {{ auth()->user()->chart_webhook_url ?? 'NULL' }}</div>
                </div>
            </div>
        @endif
    </div>
</div>
