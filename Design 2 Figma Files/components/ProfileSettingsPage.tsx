import { ArrowLeft } from 'lucide-react';
import { useState } from 'react';

interface ProfileSettingsPageProps {
  onBack: () => void;
  userProfile: {
    name: string;
    email: string;
  };
  onSave: (profile: { name: string; email: string; notifications: boolean }) => void;
}

export function ProfileSettingsPage({ onBack, userProfile, onSave }: ProfileSettingsPageProps) {
  const [name, setName] = useState(userProfile.name);
  const [email, setEmail] = useState(userProfile.email);
  const [notifications, setNotifications] = useState(true);

  const handleSave = () => {
    onSave({ name, email, notifications });
  };

  return (
    <div className="max-w-[800px] mx-auto px-8 py-12">
      {/* Back Button */}
      <button
        onClick={onBack}
        className="flex items-center gap-2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors mb-8"
      >
        <ArrowLeft className="w-4 h-4" />
        <span className="text-sm">Back</span>
      </button>

      {/* Settings Card */}
      <div className="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border)]">
        <h2 className="text-[var(--text-primary)] mb-8">Profile Settings</h2>

        <div className="space-y-6">
          {/* Name */}
          <div>
            <label className="text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide block">
              Name
            </label>
            <input
              type="text"
              value={name}
              onChange={(e) => setName(e.target.value)}
              className="w-full px-4 py-3 bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#4A4D52]"
            />
          </div>

          {/* Email */}
          <div>
            <label className="text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide block">
              Email
            </label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="w-full px-4 py-3 bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#4A4D52]"
            />
          </div>

          {/* Notifications */}
          <div>
            <label className="text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide block">
              Notifications
            </label>
            <div className="flex items-center gap-3">
              <button
                onClick={() => setNotifications(!notifications)}
                className={`w-12 h-6 rounded-full transition-colors ${
                  notifications ? 'bg-[#4CAF50]' : 'bg-[var(--bg-tertiary)]'
                } border border-[var(--border)]`}
              >
                <div
                  className={`w-4 h-4 bg-white rounded-full transition-transform ${
                    notifications ? 'translate-x-7' : 'translate-x-1'
                  }`}
                />
              </button>
              <span className="text-sm text-[var(--text-secondary)]">
                {notifications ? 'Enabled' : 'Disabled'}
              </span>
            </div>
          </div>

          {/* Save Button */}
          <div className="pt-4">
            <button
              onClick={handleSave}
              className="px-6 py-3 bg-white text-[var(--bg-primary)] rounded-lg hover:bg-[#E8E8E8] transition-colors"
            >
              Save Changes
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}