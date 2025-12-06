import { ArrowLeft } from 'lucide-react';
import { useState } from 'react';

interface ProfilePageProps {
  onBack: () => void;
  userProfile: {
    name: string;
    email: string;
    company: string;
  };
  companyData?: {
    businessModel: string;
    stage: string;
    teamSize: string;
    timeframe: string;
    additionalInfo: string;
  };
  onSave?: (data: {
    name: string;
    email: string;
    company: string;
    businessModel: string;
    stage: string;
    teamSize: string;
    timeframe: string;
    additionalInfo: string;
  }) => void;
}

export function ProfilePage({ onBack, userProfile, companyData, onSave }: ProfilePageProps) {
  const [formData, setFormData] = useState({
    name: userProfile.name,
    email: userProfile.email,
    company: userProfile.company,
    businessModel: companyData?.businessModel || '',
    stage: companyData?.stage || 'PMF',
    teamSize: companyData?.teamSize || '',
    timeframe: companyData?.timeframe || 'This week',
    additionalInfo: companyData?.additionalInfo || '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (onSave) {
      onSave(formData);
    }
    onBack();
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

      {/* Profile Form */}
      <form onSubmit={handleSubmit} className="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border)]">
        <h2 className="text-[var(--text-primary)] mb-8">Profile</h2>

        <div className="space-y-6">
          {/* Personal Info */}
          <div>
            <label className="text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide block">
              Name
            </label>
            <input
              type="text"
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              className="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-2.5 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C] transition-colors"
              placeholder="Your name"
            />
          </div>

          <div>
            <label className="text-xs text-[var(--text-secondary)] mb-2 uppercase tracking-wide block">
              Email
            </label>
            <input
              type="email"
              value={formData.email}
              onChange={(e) => setFormData({ ...formData, email: e.target.value })}
              className="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-2.5 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C] transition-colors"
              placeholder="your@email.com"
            />
          </div>

          {/* Company Info */}
          {/* Removed - Company information fields */}
        </div>

        {/* Save Button */}
        <div className="mt-8 flex justify-end gap-3">
          <button
            type="button"
            onClick={onBack}
            className="px-6 py-2.5 text-sm text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            className="px-6 py-2.5 bg-[#E94B8C] hover:bg-[#D43D7A] text-white text-sm rounded-lg transition-colors"
          >
            Save Changes
          </button>
        </div>
      </form>
    </div>
  );
}