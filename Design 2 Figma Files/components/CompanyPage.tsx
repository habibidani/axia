import { useState } from 'react';
import { Globe } from 'lucide-react';

interface CompanyData {
  name: string;
  businessModel: string;
  stage: string;
  teamSize: string;
  timeframe: string;
  additionalInfo: string;
}

interface CompanyPageProps {
  data: CompanyData;
  onUpdate: (data: CompanyData) => void;
  onNext: () => void;
}

export function CompanyPage({ data, onUpdate, onNext }: CompanyPageProps) {
  const [domain, setDomain] = useState('');
  const [fetched, setFetched] = useState(false);
  const [loading, setLoading] = useState(false);

  const handleFetchDomain = async () => {
    if (!domain) return;
    setLoading(true);
    
    // Mock API call
    setTimeout(() => {
      onUpdate({
        ...data,
        name: domain.replace(/\.(com|io|net|org)$/, '').charAt(0).toUpperCase() + 
              domain.replace(/\.(com|io|net|org)$/, '').slice(1),
      });
      setFetched(true);
      setLoading(false);
    }, 800);
  };

  return (
    <div className="max-w-3xl mx-auto px-6 py-8">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-[var(--text-primary)] mb-2">Company Information</h1>
        <p>Give Axia your business context.</p>
      </div>

      {/* Main Card */}
      <div className="bg-[var(--bg-secondary)] rounded-2xl p-8 space-y-8">
        {/* Domain Fetch Section */}
        <div className="space-y-4">
          <label className="block text-sm text-[var(--text-primary)]">Domain</label>
          <div className="flex gap-3">
            <div className="flex-1 relative">
              <Globe className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-secondary)]" />
              <input
                type="text"
                value={domain}
                onChange={(e) => setDomain(e.target.value)}
                placeholder="acme.com"
                className="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-10 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50"
              />
            </div>
            <button
              onClick={handleFetchDomain}
              disabled={loading}
              className="px-6 py-3 bg-[var(--bg-tertiary)] hover:bg-[#2A2D30] border border-[var(--border)] rounded-lg text-[var(--text-primary)] transition-colors disabled:opacity-50"
            >
              {loading ? 'Fetching...' : 'Fetch Company Data'}
            </button>
          </div>
          {fetched && (
            <p className="text-sm text-[#4CAF50]">✓ Data found. Review below.</p>
          )}
        </div>

        <div className="h-px bg-[var(--border)]" />

        {/* Manual Inputs */}
        <div className="space-y-6">
          <div>
            <label className="block text-sm text-[var(--text-primary)] mb-2">Company Name</label>
            <input
              type="text"
              value={data.name}
              onChange={(e) => onUpdate({ ...data, name: e.target.value })}
              placeholder="Enter company name"
              className="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50"
            />
          </div>

          <div>
            <label className="block text-sm text-[var(--text-primary)] mb-2">Business Model</label>
            <select
              value={data.businessModel}
              onChange={(e) => onUpdate({ ...data, businessModel: e.target.value })}
              className="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] focus:outline-none focus:border-[#E94B8C]/50"
            >
              <option value="">Select business model</option>
              <option value="SaaS">SaaS</option>
              <option value="Marketplace">Marketplace</option>
              <option value="E-Commerce">E-Commerce</option>
              <option value="Services">Services</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <div>
            <label className="block text-sm text-[var(--text-primary)] mb-2">Stage</label>
            <select
              value={data.stage}
              onChange={(e) => onUpdate({ ...data, stage: e.target.value })}
              className="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] focus:outline-none focus:border-[#E94B8C]/50"
            >
              <option value="">Select stage</option>
              <option value="Pre-PMF">Pre-PMF</option>
              <option value="PMF">PMF</option>
              <option value="Scale">Scale</option>
            </select>
          </div>

          <div>
            <label className="block text-sm text-[var(--text-primary)] mb-2">Team Size</label>
            <input
              type="text"
              value={data.teamSize}
              onChange={(e) => onUpdate({ ...data, teamSize: e.target.value })}
              placeholder="e.g. 5-10"
              className="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50"
            />
          </div>

          <div>
            <label className="block text-sm text-[var(--text-primary)] mb-2">Timeframe</label>
            <select
              value={data.timeframe}
              onChange={(e) => onUpdate({ ...data, timeframe: e.target.value })}
              className="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] focus:outline-none focus:border-[#E94B8C]/50"
            >
              <option value="">Select timeframe</option>
              <option value="This week">This week</option>
              <option value="This month">This month</option>
              <option value="This quarter">This quarter</option>
            </select>
          </div>

          <div>
            <label className="block text-sm text-[var(--text-primary)] mb-2">Additional Information</label>
            <textarea
              value={data.additionalInfo}
              onChange={(e) => onUpdate({ ...data, additionalInfo: e.target.value })}
              placeholder="Any additional context..."
              rows={4}
              className="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50 resize-none"
            />
          </div>
        </div>

        {/* Next Button */}
        <div className="flex justify-end pt-4">
          <button
            onClick={onNext}
            disabled={!data.name}
            className="px-8 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Continue to Goals
          </button>
        </div>
      </div>
    </div>
  );
}