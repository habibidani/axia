import { createChat } from '@n8n/chat';

// Initialize n8n chat widget
export function initializeChat() {
    createChat({
        // n8n webhook URL - wird durch n8n Workflow bereitgestellt
        webhookUrl: window.n8nChatConfig?.webhookUrl || 'https://n8n.getaxia.de/webhook/chat',
        
        // Webhook config
        webhookConfig: {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        },

        // Chat appearance
        chatInputPlaceholder: 'Ask axia anything...',
        chatSessionKey: 'axia-chat-session',
        showWelcomeScreen: true,
        welcomeScreenTitle: 'Hi! 👋',
        welcomeScreenMessage: 'I\'m axia, your AI focus coach. How can I help you today?',
        
        // Initial messages
        initialMessages: [
            {
                text: 'Welcome! I can help you with:\n\n• Analyzing your to-dos\n• Creating SMART goals\n• Understanding your focus score\n• Suggesting priorities\n\nWhat would you like to know?',
                sender: 'bot',
            },
        ],

        // Styling
        theme: {
            primaryColor: '#ee4769', // rose-500 from app.css
            chatWindowBackground: '#ffffff',
            messageBotBackground: '#f9fafb',
            messageBotColor: '#374151',
            messageUserBackground: '#ee4769',
            messageUserColor: '#ffffff',
            inputBackground: '#ffffff',
            inputBorder: '#e5e7eb',
        },

        // Position
        mode: 'window', // 'window' or 'fullscreen'
        showWindowCloseButton: true,
        
        // Optional: Pass user context
        metadata: {
            userId: window.axiaUser?.id,
            userName: window.axiaUser?.name,
            userEmail: window.axiaUser?.email,
            companyId: window.axiaUser?.company_id,
        },
    });
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeChat);
} else {
    initializeChat();
}
