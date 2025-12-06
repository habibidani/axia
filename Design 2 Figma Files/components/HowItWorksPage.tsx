import { ArrowLeft } from 'lucide-react';

interface HowItWorksPageProps {
  onBack: () => void;
  onStartSetup: () => void;
}

export function HowItWorksPage({ onBack, onStartSetup }: HowItWorksPageProps) {
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

      {/* Welcome Card */}
      <div className="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border)] mb-6">
        <h2 className="text-[var(--text-primary)] mb-6">Welcome to Axia</h2>
        <p className="text-[var(--text-secondary)] mb-4">Your AI Focus Coach</p>
      </div>

      {/* How it Works */}
      <div className="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border)] mb-6">
        <h3 className="text-[var(--text-primary)] mb-6">Here's how it works:</h3>
        
        <div className="space-y-6">
          {/* Step 1 */}
          <div className="flex gap-4">
            <div className="w-8 h-8 rounded-full bg-[var(--bg-tertiary)] border border-[var(--border)] flex items-center justify-center flex-shrink-0">
              <span className="text-sm text-[var(--text-primary)]">1</span>
            </div>
            <div>
              <div className="text-[var(--text-primary)] mb-1">Add company info</div>
              <div className="text-sm text-[var(--text-secondary)]">
                Tell us about your company, stage, and team size
              </div>
            </div>
          </div>

          {/* Step 2 */}
          <div className="flex gap-4">
            <div className="w-8 h-8 rounded-full bg-[var(--bg-tertiary)] border border-[var(--border)] flex items-center justify-center flex-shrink-0">
              <span className="text-sm text-[var(--text-primary)]">2</span>
            </div>
            <div>
              <div className="text-[var(--text-primary)] mb-1">Define your goals</div>
              <div className="text-sm text-[var(--text-secondary)]">
                Set your top priorities and what you want to achieve
              </div>
            </div>
          </div>

          {/* Step 3 */}
          <div className="flex gap-4">
            <div className="w-8 h-8 rounded-full bg-[var(--bg-tertiary)] border border-[var(--border)] flex items-center justify-center flex-shrink-0">
              <span className="text-sm text-[var(--text-primary)]">3</span>
            </div>
            <div>
              <div className="text-[var(--text-primary)] mb-1">Add your current To-Dos</div>
              <div className="text-sm text-[var(--text-secondary)]">
                Paste or upload your task list from any tool
              </div>
            </div>
          </div>

          {/* Step 4 */}
          <div className="flex gap-4">
            <div className="w-8 h-8 rounded-full bg-[var(--bg-tertiary)] border border-[var(--border)] flex items-center justify-center flex-shrink-0">
              <span className="text-sm text-[var(--text-primary)]">4</span>
            </div>
            <div>
              <div className="text-[var(--text-primary)] mb-1">Get your analysis</div>
              <div className="text-sm text-[var(--text-secondary)]">
                Axia analyzes everything and shows you what truly matters
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Action Buttons */}
      <div className="flex gap-4">
        <button
          onClick={onStartSetup}
          className="px-6 py-3 bg-white text-[var(--bg-primary)] rounded-lg hover:bg-[#E8E8E8] transition-colors"
        >
          Start Setup
        </button>
        <button
          onClick={onBack}
          className="px-6 py-3 bg-[var(--bg-tertiary)] text-[var(--text-primary)] rounded-lg border border-[var(--border)] hover:bg-[var(--bg-hover)] transition-colors"
        >
          Close
        </button>
      </div>
    </div>
  );
}