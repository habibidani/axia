import { Plus, X } from 'lucide-react';

interface Goal {
  id: string;
  title: string;
  description: string;
  priority: 'high' | 'mid' | 'low';
}

interface GoalsPageProps {
  goals: Goal[];
  onUpdate: (goals: Goal[]) => void;
  onNext: () => void;
}

export function GoalsPage({ goals, onUpdate, onNext }: GoalsPageProps) {
  const addGoal = () => {
    const newGoal: Goal = {
      id: Date.now().toString(),
      title: '',
      description: '',
      priority: 'mid',
    };
    onUpdate([...goals, newGoal]);
  };

  const updateGoal = (id: string, updates: Partial<Goal>) => {
    onUpdate(goals.map((g) => (g.id === id ? { ...g, ...updates } : g)));
  };

  const deleteGoal = (id: string) => {
    onUpdate(goals.filter((g) => g.id !== id));
  };

  return (
    <div className="max-w-3xl mx-auto px-6 py-8">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-[var(--text-primary)] mb-2">Goals for this period</h1>
        <p>Define your key goals with clear priorities.</p>
      </div>

      {/* Add Goal Button */}
      <button
        onClick={addGoal}
        className="w-full mb-6 py-4 bg-[var(--bg-secondary)] hover:bg-[var(--bg-hover)] border-2 border-dashed border-[var(--border)] hover:border-[#E94B8C]/30 rounded-2xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors flex items-center justify-center gap-2"
      >
        <Plus className="w-5 h-5" />
        Add Goal
      </button>

      {/* Goals */}
      <div className="space-y-6">
        {goals.map((goal) => (
          <div key={goal.id} className="bg-[var(--bg-secondary)] rounded-2xl p-6 space-y-4">
            {/* Title */}
            <div className="relative">
              <input
                type="text"
                value={goal.title}
                onChange={(e) => updateGoal(goal.id, { title: e.target.value })}
                placeholder="Describe your goal"
                className="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 pr-10 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50"
              />
              <button
                onClick={() => deleteGoal(goal.id)}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            {/* Description */}
            <textarea
              value={goal.description}
              onChange={(e) => updateGoal(goal.id, { description: e.target.value })}
              placeholder="Additional details (optional)"
              rows={2}
              className="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50 resize-none"
            />

            {/* Priority */}
            <div>
              <label className="block text-sm text-[var(--text-secondary)] mb-2">Priority</label>
              <div className="flex gap-3">
                {(['high', 'mid', 'low'] as const).map((priority) => (
                  <button
                    key={priority}
                    onClick={() => updateGoal(goal.id, { priority })}
                    className={`px-6 py-2 rounded-lg border transition-colors ${
                      goal.priority === priority
                        ? priority === 'high'
                          ? 'bg-[#4CAF50]/10 border-[#4CAF50]/50 text-[#4CAF50]'
                          : priority === 'mid'
                          ? 'bg-[#FFB74D]/10 border-[#FFB74D]/50 text-[#FFB74D]'
                          : 'bg-[#FF8A65]/10 border-[#FF8A65]/50 text-[#FF8A65]'
                        : 'bg-[var(--bg-tertiary)] border-[var(--border)] text-[var(--text-secondary)] hover:border-[var(--border)]'
                    }`}
                  >
                    {priority.charAt(0).toUpperCase() + priority.slice(1)}
                  </button>
                ))}
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Add Goal Button (Bottom) */}
      {goals.length > 0 && (
        <button
          onClick={addGoal}
          className="w-full mt-6 py-4 bg-[var(--bg-secondary)] hover:bg-[var(--bg-hover)] border-2 border-dashed border-[var(--border)] hover:border-[#E94B8C]/30 rounded-2xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors flex items-center justify-center gap-2"
        >
          <Plus className="w-5 h-5" />
          Add Goal
        </button>
      )}

      {/* Next Button */}
      {goals.length > 0 && (
        <div className="flex justify-end mt-8">
          <button
            onClick={onNext}
            className="px-8 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors"
          >
            Continue to To-Dos
          </button>
        </div>
      )}
    </div>
  );
}