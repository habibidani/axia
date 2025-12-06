<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Axia') }} – AI-Powered Task Prioritization</title>
    
    <meta name="description" content="Axia helps founders and teams prioritize what matters. AI-powered task analysis aligned with your business goals.">
    
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, rgba(233, 75, 140, 0.1) 0%, transparent 50%);
        }
        .glow {
            box-shadow: 0 0 60px rgba(233, 75, 140, 0.15);
        }
    </style>
</head>
<body class="min-h-screen bg-[var(--bg-primary)] text-[var(--text-primary)]">
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 border-b border-[var(--border)] bg-[var(--bg-primary)]/90 backdrop-blur-sm">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="{{ route('welcome') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#E94B8C] to-[#B03A6F] flex items-center justify-center">
                        <span class="text-white text-sm font-medium">A</span>
                    </div>
                    <span class="text-[var(--text-primary)] font-medium">Axia</span>
                </a>
                
                <!-- Nav Actions -->
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('home') }}" class="px-5 py-2 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors text-sm">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors text-sm">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="px-5 py-2 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors text-sm">
                            Get Started
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="min-h-screen flex items-center pt-16 hero-gradient">
        <div class="max-w-6xl mx-auto px-6 py-20">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                
                <!-- Left: Content -->
                <div class="space-y-8">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--bg-secondary)] rounded-full border border-[var(--border)]">
                        <span class="w-2 h-2 rounded-full bg-[#4CAF50] animate-pulse"></span>
                        <span class="text-sm text-[var(--text-secondary)]">AI-Powered Prioritization</span>
                    </div>
                    
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-medium leading-tight">
                        Focus on what 
                        <span class="text-[#E94B8C]">actually</span> 
                        moves the needle
                    </h1>
                    
                    <p class="text-lg text-[var(--text-secondary)] max-w-xl">
                        Axia analyzes your to-do list against your company's goals and KPIs, 
                        showing you exactly which tasks deserve your attention.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        @auth
                            <a href="{{ route('home') }}" class="px-8 py-4 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-xl transition-colors text-center font-medium">
                                Go to Dashboard →
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="px-8 py-4 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-xl transition-colors text-center font-medium">
                                Start Free →
                            </a>
                            <a href="{{ route('login') }}" class="px-8 py-4 bg-[var(--bg-secondary)] hover:bg-[var(--bg-hover)] text-[var(--text-primary)] border border-[var(--border)] rounded-xl transition-colors text-center font-medium">
                                Sign In
                            </a>
                        @endauth
                    </div>
                </div>
                
                <!-- Right: Visual -->
                <div class="relative">
                    <div class="bg-[var(--bg-secondary)] rounded-2xl border border-[var(--border)] p-8 glow">
                        <!-- Focus Score Preview -->
                        <div class="flex items-center justify-center mb-8">
                            <div class="w-32 h-32 rounded-full flex items-center justify-center" style="border: 4px solid rgba(76,175,80,0.3); background-color: rgba(76,175,80,0.05);">
                                <div class="text-center">
                                    <div class="text-4xl font-medium text-[var(--text-primary)]">78</div>
                                    <div class="text-xs text-[var(--text-secondary)]">/100</div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center text-sm text-[var(--text-secondary)] mb-8">Focus Score</div>
                        
                        <!-- Sample Tasks -->
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 p-3 bg-[var(--bg-tertiary)] rounded-lg">
                                <div class="w-1 h-8 rounded-full bg-[#4CAF50]"></div>
                                <div class="flex-1 text-sm text-[var(--text-primary)]">Close enterprise deal</div>
                                <span class="px-2 py-1 rounded text-xs bg-[rgba(76,175,80,0.1)] text-[#4CAF50]">High</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-[var(--bg-tertiary)] rounded-lg">
                                <div class="w-1 h-8 rounded-full bg-[#FFB74D]"></div>
                                <div class="flex-1 text-sm text-[var(--text-primary)]">Update landing page</div>
                                <span class="px-2 py-1 rounded text-xs bg-[rgba(255,183,77,0.1)] text-[#FFB74D]">Mid</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-[var(--bg-tertiary)] rounded-lg">
                                <div class="w-1 h-8 rounded-full bg-[#FF8A65]"></div>
                                <div class="flex-1 text-sm text-[var(--text-primary)]">Reply to emails</div>
                                <span class="px-2 py-1 rounded text-xs bg-[rgba(255,138,101,0.1)] text-[#FF8A65]">Low</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-24 bg-[var(--bg-secondary)]">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-medium mb-4">How Axia Works</h2>
                <p class="text-[var(--text-secondary)] max-w-2xl mx-auto">
                    Three simple steps to prioritize your work based on actual business impact.
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="bg-[var(--bg-primary)] rounded-2xl p-8 border border-[var(--border)]">
                    <div class="w-12 h-12 rounded-full bg-[var(--accent-pink-light)] flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-[#E94B8C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium mb-3">1. Add Your Company</h3>
                    <p class="text-sm text-[var(--text-secondary)]">
                        Tell us about your business, goals, and key metrics. This context helps Axia understand what matters most.
                    </p>
                </div>
                
                <!-- Step 2 -->
                <div class="bg-[var(--bg-primary)] rounded-2xl p-8 border border-[var(--border)]">
                    <div class="w-12 h-12 rounded-full bg-[var(--accent-pink-light)] flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-[#E94B8C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium mb-3">2. Paste Your To-Dos</h3>
                    <p class="text-sm text-[var(--text-secondary)]">
                        Paste your task list or upload a CSV. No integrations needed – just copy and paste from anywhere.
                    </p>
                </div>
                
                <!-- Step 3 -->
                <div class="bg-[var(--bg-primary)] rounded-2xl p-8 border border-[var(--border)]">
                    <div class="w-12 h-12 rounded-full bg-[var(--accent-pink-light)] flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-[#E94B8C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium mb-3">3. Get AI Analysis</h3>
                    <p class="text-sm text-[var(--text-secondary)]">
                        Receive a prioritized list with explanations. See what to focus on, what to delegate, and what to drop.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24">
        <div class="max-w-3xl mx-auto px-6 text-center">
            <h2 class="text-3xl font-medium mb-4">Ready to focus on what matters?</h2>
            <p class="text-[var(--text-secondary)] mb-8">
                Join founders and teams who use Axia to cut through the noise and prioritize effectively.
            </p>
            @guest
                <a href="{{ route('register') }}" class="inline-flex px-8 py-4 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-xl transition-colors font-medium">
                    Start Free – No Credit Card Required
                </a>
            @else
                <a href="{{ route('home') }}" class="inline-flex px-8 py-4 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-xl transition-colors font-medium">
                    Go to Dashboard
                </a>
            @endguest
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 border-t border-[var(--border)]">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-[#E94B8C] to-[#B03A6F] flex items-center justify-center">
                        <span class="text-white text-xs font-medium">A</span>
                    </div>
                    <span class="text-sm text-[var(--text-secondary)]">Axia</span>
                </div>
                <div class="flex items-center gap-6">
                    <a href="https://github.com/habibidani/axia" target="_blank" rel="noopener noreferrer" class="text-sm text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
                        GitHub
                    </a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
