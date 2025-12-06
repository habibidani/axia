import { Send } from 'lucide-react';

interface Message {
  id: string;
  text: string;
  sender: 'user' | 'ai';
}

interface ChatPanelProps {
  messages: Message[];
  onSendMessage: (text: string) => void;
}

export function ChatPanel({ messages, onSendMessage }: ChatPanelProps) {
  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    const text = formData.get('message') as string;
    if (text.trim()) {
      onSendMessage(text);
      e.currentTarget.reset();
    }
  };

  return (
    <div className="w-[270px] h-screen bg-[var(--bg-secondary)] border-r border-[var(--border)] flex flex-col">
      {/* Logo */}
      <div className="p-6 border-b border-[var(--border)]">
        <div className="flex items-center gap-2">
          <div className="w-7 h-7 rounded-full bg-gradient-to-br from-[#E94B8C] to-[#B03A6F] flex items-center justify-center">
            <span className="text-white text-sm">A</span>
          </div>
          <span className="text-[var(--text-primary)]">Axia</span>
        </div>
      </div>

      {/* Messages */}
      <div className="flex-1 overflow-y-auto p-4 space-y-3">
        {messages.map((message) => (
          <div
            key={message.id}
            className={`p-3 rounded-lg ${
              message.sender === 'user'
                ? 'bg-[var(--bg-tertiary)] ml-4'
                : 'bg-[var(--bg-primary)] mr-4'
            }`}
          >
            <p className="text-sm text-[var(--text-primary)]">{message.text}</p>
          </div>
        ))}
      </div>

      {/* Input */}
      <form onSubmit={handleSubmit} className="p-4 border-t border-[var(--border)]">
        <div className="relative">
          <input
            type="text"
            name="message"
            placeholder="Ask Axia..."
            className="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-2.5 pr-10 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50"
          />
          <button
            type="submit"
            className="absolute right-2 top-1/2 -translate-y-1/2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors"
          >
            <Send className="w-4 h-4" />
          </button>
        </div>
      </form>
    </div>
  );
}