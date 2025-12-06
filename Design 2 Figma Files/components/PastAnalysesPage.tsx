import { ArrowLeft } from 'lucide-react';

interface Analysis {
  id: string;
  date: string;
  score: number;
  tasksAnalyzed: number;
  goals: number;
}

interface PastAnalysesPageProps {
  onBack: () => void;
  analyses: Analysis[];
  onViewAnalysis: (id: string) => void;
}

export function PastAnalysesPage({ onBack, analyses, onViewAnalysis }: PastAnalysesPageProps) {
  const getScoreColor = (score: number) => {
    if (score >= 70) return '#4CAF50';
    if (score >= 50) return '#FFB74D';
    return '#FF8A65';
  };

  return (
    <div className="max-w-[1200px] mx-auto px-8 py-12">
      {/* Back Button */}
      <button
        onClick={onBack}
        className="flex items-center gap-2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors mb-8"
      >
        <ArrowLeft className="w-4 h-4" />
        <span className="text-sm">Back</span>
      </button>

      <h2 className="text-[var(--text-primary)] mb-8">All Analyses</h2>

      {/* Analyses Grid */}
      {analyses.length === 0 ? (
        <div className="bg-[var(--bg-secondary)] rounded-2xl p-12 border border-[var(--border)] text-center">
          <div className="text-[var(--text-secondary)] mb-2">No past analyses yet</div>
          <div className="text-sm text-[var(--text-secondary)]">
            Complete your first analysis to see it here
          </div>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {analyses.map((analysis) => (
            <button
              key={analysis.id}
              onClick={() => onViewAnalysis(analysis.id)}
              className="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)] hover:bg-[var(--bg-hover)] transition-colors text-left"
            >
              <div className="flex items-start gap-4 mb-4">
                {/* Score Circle */}
                <div
                  className="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0"
                  style={{
                    border: `3px solid ${getScoreColor(analysis.score)}30`,
                    backgroundColor: `${getScoreColor(analysis.score)}05`,
                  }}
                >
                  <div className="text-center">
                    <div className="text-xl text-[var(--text-primary)]">{analysis.score}</div>
                  </div>
                </div>

                {/* Info */}
                <div className="flex-1 min-w-0">
                  <div className="text-sm text-[var(--text-primary)] mb-1">Focus Score</div>
                  <div className="text-xs text-[var(--text-secondary)]">{analysis.date}</div>
                </div>
              </div>

              {/* Summary */}
              <div className="text-sm text-[var(--text-secondary)]">
                {analysis.tasksAnalyzed} tasks analyzed • {analysis.goals} goals
              </div>
            </button>
          ))}
        </div>
      )}
    </div>
  );
}