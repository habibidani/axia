<section class="w-full">
    <div class="mb-6">
        <x-settings.partials.settings-heading 
            heading="API Tokens" 
            description="Manage API tokens for integrating Axia with n8n and other services." 
        />
    </div>

    @if (session('message'))
        <flux:banner variant="success" class="mb-6">
            {{ session('message') }}
        </flux:banner>
    @endif

    <!-- Newly Created Token Alert -->
    @if ($newlyCreatedToken)
        <flux:card class="mb-6 bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400" />
                    <h3 class="font-semibold text-green-900 dark:text-green-100">Token Created Successfully!</h3>
                </div>
                
                <p class="text-sm text-green-800 dark:text-green-200">
                    Please copy your new API token. For your security, it won't be shown again.
                </p>
                
                <div class="relative">
                    <flux:input 
                        id="new-token" 
                        type="text" 
                        value="{{ $newlyCreatedToken }}" 
                        readonly 
                        class="font-mono text-sm bg-white dark:bg-gray-900"
                    />
                    <button 
                        type="button"
                        onclick="navigator.clipboard.writeText('{{ $newlyCreatedToken }}'); this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy', 2000)"
                        class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1 text-xs font-medium text-green-700 bg-green-100 rounded hover:bg-green-200 dark:text-green-300 dark:bg-green-900 dark:hover:bg-green-800"
                    >
                        Copy
                    </button>
                </div>

                <div class="text-xs text-green-700 dark:text-green-300">
                    <strong>Usage in n8n:</strong> Add this token to your HTTP Request node headers as 
                    <code class="px-1 py-0.5 bg-green-100 dark:bg-green-900 rounded">Authorization: Bearer {{ Str::limit($newlyCreatedToken, 20) }}...</code>
                </div>
            </div>
        </flux:card>
    @endif

    <!-- Create Token Form -->
    <flux:card class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Create New Token</h3>
            
            @if (!$showCreateForm)
                <flux:button wire:click="toggleCreateForm" size="sm" variant="primary">
                    <flux:icon.plus class="size-4" />
                    New Token
                </flux:button>
            @endif
        </div>

        @if ($showCreateForm)
            <form wire:submit="createToken" class="space-y-4">
                <flux:field>
                    <flux:label>Token Name</flux:label>
                    <flux:description>
                        Give this token a descriptive name (e.g., "n8n Daily Digest Workflow" or "Notion Integration")
                    </flux:description>
                    <flux:input 
                        wire:model="newTokenName" 
                        type="text" 
                        placeholder="n8n Production Workflow"
                        required
                    />
                    <flux:error name="newTokenName" />
                </flux:field>

                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary">
                        Create Token
                    </flux:button>
                    <flux:button type="button" wire:click="toggleCreateForm" variant="ghost">
                        Cancel
                    </flux:button>
                </div>
            </form>
        @endif
    </flux:card>

    <!-- Existing Tokens List -->
    <flux:card>
        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Active Tokens</h3>

        @if ($tokens->isEmpty())
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <flux:icon.key class="size-12 mx-auto mb-3 opacity-50" />
                <p>No API tokens created yet.</p>
                <p class="text-sm mt-1">Create a token to start integrating with n8n and other services.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($tokens as $token)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <flux:icon.key class="size-4 text-gray-400" />
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $token->name }}
                                </span>
                            </div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Created {{ $token->created_at->diffForHumans() }}
                                @if ($token->last_used_at)
                                    • Last used {{ $token->last_used_at->diffForHumans() }}
                                @else
                                    • Never used
                                @endif
                            </div>
                        </div>

                        <flux:button 
                            wire:click="deleteToken({{ $token->id }})" 
                            wire:confirm="Are you sure you want to delete this token? Any services using it will stop working."
                            variant="danger" 
                            size="sm"
                        >
                            <flux:icon.trash class="size-4" />
                            Delete
                        </flux:button>
                    </div>
                @endforeach
            </div>
        @endif
    </flux:card>

    <!-- API Documentation Link -->
    <flux:card class="mt-6 bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800">
        <div class="flex items-start gap-3">
            <flux:icon.book-open class="size-5 text-blue-600 dark:text-blue-400 mt-0.5" />
            <div>
                <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-1">API Documentation</h4>
                <p class="text-sm text-blue-800 dark:text-blue-200 mb-2">
                    Learn how to use the Axia API with n8n workflows, including endpoint references and examples.
                </p>
                <a 
                    href="{{ asset('API_DOCS.md') }}" 
                    target="_blank"
                    class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline"
                >
                    View API Documentation →
                </a>
            </div>
        </div>
    </flux:card>
</section>
