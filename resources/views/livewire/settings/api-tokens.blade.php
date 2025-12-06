<div class="max-w-4xl mx-auto px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-[var(--text-primary)] mb-2">API Tokens</h1>
        <p>Manage API tokens for integrating Axia with n8n and other services.</p>
    </div>

    @if (session('message'))
        <div class="mb-6 p-4 bg-[var(--accent-green-light)] border border-[rgba(76,175,80,0.3)] rounded-xl">
            <p class="text-sm text-[var(--accent-green)]">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Newly Created Token Alert -->
    @if ($newlyCreatedToken)
        <div class="mb-6 bg-[var(--accent-green-light)] border border-[rgba(76,175,80,0.3)] rounded-2xl p-6">
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-[var(--accent-green)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="font-medium text-[var(--accent-green)]">Token Created Successfully!</h3>
                </div>
                
                <p class="text-sm text-[var(--text-secondary)]">
                    Please copy your new API token. For your security, it won't be shown again.
                </p>
                
                <div class="relative">
                    <input 
                        id="new-token" 
                        type="text" 
                        value="{{ $newlyCreatedToken }}" 
                        readonly 
                        class="w-full bg-[var(--bg-secondary)] border border-[var(--border)] rounded-lg px-4 py-3 pr-20 text-sm font-mono text-[var(--text-primary)]"
                    />
                    <button 
                        type="button"
                        onclick="navigator.clipboard.writeText('{{ $newlyCreatedToken }}'); this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy', 2000)"
                        class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 text-xs font-medium text-[var(--accent-green)] bg-[rgba(76,175,80,0.1)] rounded-lg hover:bg-[rgba(76,175,80,0.2)] transition-colors"
                    >
                        Copy
                    </button>
                </div>

                <div class="text-xs text-[var(--text-secondary)]">
                    <strong>Usage in n8n:</strong> Add this token to your HTTP Request node headers as 
                    <code class="px-1.5 py-0.5 bg-[var(--bg-tertiary)] rounded text-[var(--accent-pink)]">Authorization: Bearer {{ Str::limit($newlyCreatedToken, 20) }}...</code>
                </div>
            </div>
        </div>
    @endif

    <!-- Create Token Form -->
    <div class="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)] mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-[var(--text-primary)]">Create New Token</h3>
            
            @if (!$showCreateForm)
                <button 
                    wire:click="toggleCreateForm" 
                    class="px-4 py-2 bg-[#E94B8C] hover:bg-[#D43F7C] text-white text-sm rounded-lg transition-colors flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Token
                </button>
            @endif
        </div>

        @if ($showCreateForm)
            <form wire:submit="createToken" class="space-y-4">
                <div class="space-y-2">
                    <label class="block text-sm text-[var(--text-primary)]">Token Name</label>
                    <p class="text-xs text-[var(--text-secondary)]">
                        Give this token a descriptive name (e.g., "n8n Daily Digest Workflow" or "Notion Integration")
                    </p>
                    <input 
                        wire:model="newTokenName" 
                        type="text" 
                        placeholder="n8n Production Workflow"
                        required
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] transition-colors"
                    />
                    @error('newTokenName') 
                        <p class="text-xs text-[var(--accent-orange)]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button 
                        type="submit" 
                        class="px-6 py-2.5 bg-[#E94B8C] hover:bg-[#D43F7C] text-white text-sm rounded-lg transition-colors"
                    >
                        Create Token
                    </button>
                    <button 
                        type="button" 
                        wire:click="toggleCreateForm" 
                        class="px-6 py-2.5 bg-[var(--bg-tertiary)] hover:bg-[var(--bg-hover)] text-[var(--text-primary)] border border-[var(--border)] text-sm rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        @endif
    </div>

    <!-- Existing Tokens List -->
    <div class="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)]">
        <h3 class="text-lg font-medium text-[var(--text-primary)] mb-4">Active Tokens</h3>

        @if ($tokens->isEmpty())
            <div class="text-center py-12 text-[var(--text-secondary)]">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                <p>No API tokens created yet.</p>
                <p class="text-sm mt-1">Create a token to start integrating with n8n and other services.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($tokens as $token)
                    <div class="flex items-center justify-between p-4 bg-[var(--bg-tertiary)] rounded-xl border border-[var(--border)]">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[var(--text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                <span class="font-medium text-[var(--text-primary)]">
                                    {{ $token->name }}
                                </span>
                            </div>
                            <div class="mt-1 text-xs text-[var(--text-secondary)]">
                                Created {{ $token->created_at->diffForHumans() }}
                                @if ($token->last_used_at)
                                    • Last used {{ $token->last_used_at->diffForHumans() }}
                                @else
                                    • Never used
                                @endif
                            </div>
                        </div>

                        <button 
                            wire:click="deleteToken({{ $token->id }})" 
                            wire:confirm="Are you sure you want to delete this token? Any services using it will stop working."
                            class="px-3 py-1.5 text-xs bg-[rgba(255,138,101,0.1)] hover:bg-[rgba(255,138,101,0.2)] text-[var(--accent-orange)] border border-[rgba(255,138,101,0.3)] rounded-lg transition-colors flex items-center gap-1"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- API Documentation Link -->
    <div class="mt-6 bg-[var(--accent-pink-light)] border border-[rgba(233,75,140,0.3)] rounded-2xl p-6">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-[var(--accent-pink)] mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <div>
                <h4 class="font-medium text-[var(--accent-pink)] mb-1">API Documentation</h4>
                <p class="text-sm text-[var(--text-secondary)] mb-2">
                    Learn how to use the Axia API with n8n workflows, including endpoint references and examples.
                </p>
                <a 
                    href="{{ asset('API_DOCS.md') }}" 
                    target="_blank"
                    class="text-sm font-medium text-[var(--accent-pink)] hover:underline"
                >
                    View API Documentation →
                </a>
            </div>
        </div>
    </div>
</div>
