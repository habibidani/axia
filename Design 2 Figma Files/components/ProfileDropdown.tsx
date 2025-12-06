import { User, BarChart3, HelpCircle, LogOut } from 'lucide-react';
import { useState, useRef, useEffect } from 'react';

interface ProfileDropdownProps {
  onNavigate: (page: 'profile' | 'settings' | 'past-analyses' | 'how-it-works') => void;
  onLogout: () => void;
}

export function ProfileDropdown({ onNavigate, onLogout }: ProfileDropdownProps) {
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    }

    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside);
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [isOpen]);

  const handleMenuClick = (page: 'profile' | 'settings' | 'past-analyses' | 'how-it-works') => {
    onNavigate(page);
    setIsOpen(false);
  };

  return (
    <div className="relative" ref={dropdownRef}>
      {/* Avatar */}
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="w-10 h-10 rounded-full bg-[var(--bg-tertiary)] border border-[var(--border)] flex items-center justify-center hover:bg-[var(--bg-hover)] transition-colors"
      >
        <User className="w-5 h-5 text-[var(--text-secondary)]" />
      </button>

      {/* Dropdown Menu */}
      {isOpen && (
        <div className="absolute right-0 mt-2 w-64 bg-[var(--bg-secondary)] border border-[var(--border)] rounded-xl shadow-2xl overflow-hidden z-50">
          <div className="py-2">
            {/* Profile */}
            <button
              onClick={() => handleMenuClick('profile')}
              className="w-full px-5 py-3 flex items-center gap-3 hover:bg-[var(--bg-hover)] transition-colors text-left"
            >
              <User className="w-4 h-4 text-[var(--text-secondary)]" />
              <span className="text-sm text-[var(--text-primary)]">Profile</span>
            </button>

            {/* All Analyses */}
            <button
              onClick={() => handleMenuClick('past-analyses')}
              className="w-full px-5 py-3 flex items-center gap-3 hover:bg-[var(--bg-hover)] transition-colors text-left"
            >
              <BarChart3 className="w-4 h-4 text-[var(--text-secondary)]" />
              <span className="text-sm text-[var(--text-primary)]">All Analyses</span>
            </button>

            {/* How Axia Works */}
            <button
              onClick={() => handleMenuClick('how-it-works')}
              className="w-full px-5 py-3 flex items-center gap-3 hover:bg-[var(--bg-hover)] transition-colors text-left"
            >
              <HelpCircle className="w-4 h-4 text-[var(--text-secondary)]" />
              <span className="text-sm text-[var(--text-primary)]">How Axia Works</span>
            </button>

            <div className="my-1 h-px bg-[var(--border)]" />

            {/* Logout */}
            <button
              onClick={() => {
                onLogout();
                setIsOpen(false);
              }}
              className="w-full px-5 py-3 flex items-center gap-3 hover:bg-[var(--bg-hover)] transition-colors text-left"
            >
              <LogOut className="w-4 h-4 text-[var(--text-secondary)]" />
              <span className="text-sm text-[var(--text-primary)]">Logout</span>
            </button>
          </div>
        </div>
      )}
    </div>
  );
}