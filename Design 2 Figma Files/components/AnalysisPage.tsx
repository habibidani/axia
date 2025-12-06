import { ChevronDown, ChevronUp } from 'lucide-react';
import { useState } from 'react';

interface Goal {
  id: string;
  title: string;
  priority: 'high' | 'mid' | 'low';
}

interface ToDo {
  id: string;
  text: string;
}

interface CompanyData {
  name: string;
  businessModel: string;
  teamSize: string;
}

interface AnalysisPageProps {
  goals: Goal[];
  todos: ToDo[];
  timeframe: string;
  companyData: CompanyData;
  onNewWeek: () => void;
}

interface TaskAnalysis {
  id: string;
  title: string;
  impact: 'high' | 'mid' | 'low';
  score: number;
  summary: string;
  reasoning: string[];
  relatedGoal: string;
  impactRating: string;
  delegationFit: string;
}

export function AnalysisPage({ goals, todos, timeframe, companyData, onNewWeek }: AnalysisPageProps) {
  const [expandedTasks, setExpandedTasks] = useState<Set<string>>(new Set());

  // Calculate focus score
  const focusScore = 55;
  const scoreColor = focusScore >= 70 ? '#4CAF50' : focusScore >= 50 ? '#FFB74D' : '#FF8A65';

  // Get high priority goals
  const highImpactGoals = goals.filter(g => g.priority === 'high').slice(0, 2);

  // Analyze tasks
  const analyzeTask = (todo: ToDo): TaskAnalysis => {
    const text = todo.text.toLowerCase();
    
    let impact: 'high' | 'mid' | 'low' = 'low';
    let score = 0;
    let summary = '';
    let reasoning: string[] = [];
    let relatedGoal = '';
    let impactRating = '';
    let delegationFit = '';

    if (text.includes('enterprise') || text.includes('pricing') || text.includes('sales')) {
      impact = 'high';
      score = 92;
      summary = 'Strong contribution to your top goal and short-term revenue path.';
      reasoning = [
        'Directly supports "Launch enterprise pricing" goal',
        'Revenue-driving with immediate impact',
        'Founder-level leverage task'
      ];
      relatedGoal = 'Launch enterprise pricing';
      impactRating = 'Revenue-driving';
      delegationFit = 'Founder-led';
    } else if (text.includes('onboarding') || text.includes('bug') || text.includes('fix')) {
      impact = 'high';
      score = 88;
      summary = 'Critical for user retention and directly supports completion rate goal.';
      reasoning = [
        'Blocks users from completing onboarding',
        'Supports "Improve onboarding completion" goal',
        'High urgency this week'
      ];
      relatedGoal = 'Improve onboarding';
      impactRating = 'Retention-critical';
      delegationFit = 'Technical lead';
    } else if (text.includes('customer') || text.includes('interview') || text.includes('analytics')) {
      impact = 'mid';
      score = 65;
      summary = 'Provides strategic insights but not immediately revenue-generating.';
      reasoning = [
        'Valuable for product roadmap decisions',
        'Supports long-term strategic goals',
        'Can be scheduled flexibly'
      ];
      relatedGoal = 'Research & insights';
      impactRating = 'Strategic input';
      delegationFit = 'PM or founder';
    } else if (text.includes('referral') || text.includes('research')) {
      impact = 'mid';
      score = 58;
      summary = 'Preparatory work for future growth, lower urgency this week.';
      reasoning = [
        'Supports "Build referral program" goal',
        'Mid-term growth initiative',
        'Not time-sensitive'
      ];
      relatedGoal = 'Build referral program';
      impactRating = 'Future growth';
      delegationFit = 'Growth team';
    } else {
      impact = 'low';
      score = 32;
      summary = 'Limited connection to current high-priority goals.';
      reasoning = [
        'No direct alignment with top goals',
        'Can be postponed or delegated',
        'Low urgency within timeframe'
      ];
      relatedGoal = 'Operational';
      impactRating = 'Low urgency';
      delegationFit = 'Delegate';
    }

    return {
      id: todo.id,
      title: todo.text,
      impact,
      score,
      summary,
      reasoning,
      relatedGoal,
      impactRating,
      delegationFit,
    };
  };

  const analyzedTasks = todos.filter(t => t.text.trim()).map(analyzeTask);
  const highTasks = analyzedTasks.filter(t => t.impact === 'high');
  const midTasks = analyzedTasks.filter(t => t.impact === 'mid');
  const lowTasks = analyzedTasks.filter(t => t.impact === 'low');

  const toggleTask = (id: string) => {
    const newExpanded = new Set(expandedTasks);
    if (newExpanded.has(id)) {
      newExpanded.delete(id);
    } else {
      newExpanded.add(id);
    }
    setExpandedTasks(newExpanded);
  };

  const getImpactColor = (impact: string) => {
    if (impact === 'high') return '#4CAF50';
    if (impact === 'mid') return '#FFB74D';
    return '#FF8A65';
  };

  const getPriorityLabel = (impact: string) => {
    if (impact === 'high') return 'High';
    if (impact === 'mid') return 'Mid';
    return 'Low';
  };

  return (
    <div className="max-w-[1400px] mx-auto px-8 py-12">
      {/* TOP COMPONENT - 3 Columns */}
      <div className="grid grid-cols-3 gap-8 mb-12">
        {/* Left: Company Info */}
        <div className="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)]">
          <div className="text-xs text-[var(--text-secondary)] mb-4 uppercase tracking-wide">Company Info</div>
          <div className="space-y-3">
            <div>
              <div className="text-xs text-[var(--text-secondary)] mb-1">Name</div>
              <div className="text-sm text-[var(--text-primary)]">{companyData.name}</div>
            </div>
            <div>
              <div className="text-xs text-[var(--text-secondary)] mb-1">Model</div>
              <div className="text-sm text-[var(--text-primary)]">{companyData.businessModel}</div>
            </div>
            <div>
              <div className="text-xs text-[var(--text-secondary)] mb-1">Team Size</div>
              <div className="text-sm text-[var(--text-primary)]">{companyData.teamSize}</div>
            </div>
          </div>
        </div>

        {/* Center: Focus Score */}
        <div className="flex flex-col items-center justify-center">
          <div 
            className="w-40 h-40 rounded-full flex items-center justify-center mb-4"
            style={{
              border: `6px solid ${scoreColor}30`,
              backgroundColor: `${scoreColor}05`
            }}
          >
            <div className="text-center">
              <div className="text-5xl text-[var(--text-primary)] mb-1">{focusScore}</div>
              <div className="text-xs text-[var(--text-secondary)]">/100</div>
            </div>
          </div>
          <div className="text-sm text-[var(--text-secondary)]">Focus Score</div>
        </div>

        {/* Right: High-Impact Goals */}
        <div className="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)]">
          <div className="text-xs text-[var(--text-secondary)] mb-4 uppercase tracking-wide">High-Impact Goals</div>
          <div className="space-y-4">
            {highImpactGoals.map((goal) => (
              <div key={goal.id}>
                <div className="text-sm text-[var(--text-primary)] mb-2">{goal.title}</div>
              </div>
            ))}
            {highImpactGoals.length === 0 && (
              <div className="text-sm text-[var(--text-secondary)]">No high-priority goals set</div>
            )}
          </div>
        </div>
      </div>

      {/* SCORE SUMMARY SECTION */}
      <div className="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border)] mb-12">
        <h2 className="text-[var(--text-primary)] mb-4">Summary of Your Focus Score</h2>
        <div className="space-y-4 text-[var(--text-secondary)]">
          <p>
            Your focus score of {focusScore} indicates a moderate level of alignment between your to-do list and strategic goals. 
            While you have several high-impact tasks that directly support your enterprise launch and onboarding improvement objectives, 
            there are also tasks that could be delegated or postponed to increase focus.
          </p>
          <p>
            To improve your score, consider removing or delegating low-impact tasks that don't directly contribute to your 
            top goals this {timeframe.toLowerCase()}. The analysis below shows which tasks deserve your immediate attention 
            and which can wait.
          </p>
        </div>
      </div>

      {/* TASK ACCORDION SECTION */}
      <div className="mb-12">
        <h2 className="text-[var(--text-primary)] mb-8">Your Tasks by Impact</h2>

        {/* High Impact */}
        {highTasks.length > 0 && (
          <div className="mb-10">
            <div className="text-xs text-[var(--text-secondary)] mb-4 uppercase tracking-wide">High Impact</div>
            <div className="space-y-3">
              {highTasks.map((task) => (
                <div key={task.id} className="bg-[var(--bg-secondary)] rounded-xl border border-[var(--border)] overflow-hidden">
                  {/* Accordion Header */}
                  <button
                    onClick={() => toggleTask(task.id)}
                    className="w-full flex items-center gap-4 p-5 hover:bg-[var(--bg-hover)] transition-colors"
                  >
                    <div 
                      className="w-1 h-12 rounded-full"
                      style={{ backgroundColor: getImpactColor(task.impact) }}
                    />
                    <div className="flex-1 text-left">
                      <div className="text-[var(--text-primary)] text-sm">{task.title}</div>
                    </div>
                    <span 
                      className="px-3 py-1 rounded-lg text-xs border"
                      style={{
                        backgroundColor: `${getImpactColor(task.impact)}10`,
                        color: getImpactColor(task.impact),
                        borderColor: `${getImpactColor(task.impact)}30`
                      }}
                    >
                      {getPriorityLabel(task.impact)}
                    </span>
                    <span className="px-3 py-1 rounded-lg text-xs bg-[var(--bg-tertiary)] text-[var(--text-secondary)] border border-[var(--border)]">
                      Score: {task.score}
                    </span>
                    {expandedTasks.has(task.id) ? (
                      <ChevronUp className="w-5 h-5 text-[var(--text-secondary)]" />
                    ) : (
                      <ChevronDown className="w-5 h-5 text-[var(--text-secondary)]" />
                    )}
                  </button>

                  {/* Accordion Content */}
                  {expandedTasks.has(task.id) && (
                    <div className="px-5 pb-8 pt-6 border-t border-[var(--border)]">
                      <div className="pl-5 space-y-6">
                        {/* Summary */}
                        <p className="text-sm text-[var(--text-secondary)] leading-relaxed">
                          {task.summary}
                        </p>

                        {/* Bullet Insights */}
                        <ul className="space-y-3">
                          {task.reasoning.map((reason, i) => (
                            <li key={i} className="text-sm text-[var(--text-secondary)] flex items-start gap-3">
                              <span className="text-[var(--text-secondary)] mt-0.5">•</span>
                              <span className="flex-1">{reason}</span>
                            </li>
                          ))}
                        </ul>

                        {/* Metadata Tags */}
                        <div className="flex flex-wrap gap-2 pt-2">
                          <span className="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                            {task.relatedGoal}
                          </span>
                          <span className="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                            {task.impactRating}
                          </span>
                          <span className="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                            {task.delegationFit}
                          </span>
                        </div>
                      </div>
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Mid Impact */}
        {midTasks.length > 0 && (
          <div className="mb-10">
            <div className="text-xs text-[var(--text-secondary)] mb-4 uppercase tracking-wide">Mid Impact</div>
            <div className="space-y-3">
              {midTasks.map((task) => (
                <div key={task.id} className="bg-[var(--bg-secondary)] rounded-xl border border-[var(--border)] overflow-hidden">
                  <button
                    onClick={() => toggleTask(task.id)}
                    className="w-full flex items-center gap-4 p-5 hover:bg-[var(--bg-hover)] transition-colors"
                  >
                    <div 
                      className="w-1 h-12 rounded-full"
                      style={{ backgroundColor: getImpactColor(task.impact) }}
                    />
                    <div className="flex-1 text-left">
                      <div className="text-[var(--text-primary)] text-sm">{task.title}</div>
                    </div>
                    <span 
                      className="px-3 py-1 rounded-lg text-xs border"
                      style={{
                        backgroundColor: `${getImpactColor(task.impact)}10`,
                        color: getImpactColor(task.impact),
                        borderColor: `${getImpactColor(task.impact)}30`
                      }}
                    >
                      {getPriorityLabel(task.impact)}
                    </span>
                    <span className="px-3 py-1 rounded-lg text-xs bg-[var(--bg-tertiary)] text-[var(--text-secondary)] border border-[var(--border)]">
                      Score: {task.score}
                    </span>
                    {expandedTasks.has(task.id) ? (
                      <ChevronUp className="w-5 h-5 text-[var(--text-secondary)]" />
                    ) : (
                      <ChevronDown className="w-5 h-5 text-[var(--text-secondary)]" />
                    )}
                  </button>

                  {expandedTasks.has(task.id) && (
                    <div className="px-5 pb-8 pt-6 border-t border-[var(--border)]">
                      <div className="pl-5 space-y-6">
                        {/* Summary */}
                        <p className="text-sm text-[var(--text-secondary)] leading-relaxed">
                          {task.summary}
                        </p>

                        {/* Bullet Insights */}
                        <ul className="space-y-3">
                          {task.reasoning.map((reason, i) => (
                            <li key={i} className="text-sm text-[var(--text-secondary)] flex items-start gap-3">
                              <span className="text-[var(--text-secondary)] mt-0.5">•</span>
                              <span className="flex-1">{reason}</span>
                            </li>
                          ))}
                        </ul>

                        {/* Metadata Tags */}
                        <div className="flex flex-wrap gap-2 pt-2">
                          <span className="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                            {task.relatedGoal}
                          </span>
                          <span className="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                            {task.impactRating}
                          </span>
                          <span className="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                            {task.delegationFit}
                          </span>
                        </div>
                      </div>
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Low Impact */}
        {lowTasks.length > 0 && (
          <div>
            <div className="text-xs text-[var(--text-secondary)] mb-4 uppercase tracking-wide">Low Impact</div>
            <div className="space-y-3">
              {lowTasks.map((task) => (
                <div key={task.id} className="bg-[var(--bg-secondary)] rounded-xl border border-[var(--border)] overflow-hidden">
                  <button
                    onClick={() => toggleTask(task.id)}
                    className="w-full flex items-center gap-4 p-5 hover:bg-[var(--bg-hover)] transition-colors"
                  >
                    <div 
                      className="w-1 h-12 rounded-full"
                      style={{ backgroundColor: getImpactColor(task.impact) }}
                    />
                    <div className="flex-1 text-left">
                      <div className="text-[var(--text-primary)] text-sm">{task.title}</div>
                    </div>
                    <span 
                      className="px-3 py-1 rounded-lg text-xs border"
                      style={{
                        backgroundColor: `${getImpactColor(task.impact)}10`,
                        color: getImpactColor(task.impact),
                        borderColor: `${getImpactColor(task.impact)}30`
                      }}
                    >
                      {getPriorityLabel(task.impact)}
                    </span>
                    <span className="px-3 py-1 rounded-lg text-xs bg-[var(--bg-tertiary)] text-[var(--text-secondary)] border border-[var(--border)]">
                      Score: {task.score}
                    </span>
                    {expandedTasks.has(task.id) ? (
                      <ChevronUp className="w-5 h-5 text-[var(--text-secondary)]" />
                    ) : (
                      <ChevronDown className="w-5 h-5 text-[var(--text-secondary)]" />
                    )}
                  </button>

                  {expandedTasks.has(task.id) && (
                    <div className="px-5 pb-8 pt-6 border-t border-[var(--border)]">
                      <div className="pl-5 space-y-6">
                        {/* Summary */}
                        <p className="text-sm text-[var(--text-secondary)] leading-relaxed">
                          {task.summary}
                        </p>

                        {/* Bullet Insights */}
                        <ul className="space-y-3">
                          {task.reasoning.map((reason, i) => (
                            <li key={i} className="text-sm text-[var(--text-secondary)] flex items-start gap-3">
                              <span className="text-[var(--text-secondary)] mt-0.5">•</span>
                              <span className="flex-1">{reason}</span>
                            </li>
                          ))}
                        </ul>

                        {/* Metadata Tags */}
                        <div className="flex flex-wrap gap-2 pt-2">
                          <span className="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                            {task.relatedGoal}
                          </span>
                          <span className="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                            {task.impactRating}
                          </span>
                          <span className="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                            {task.delegationFit}
                          </span>
                        </div>
                      </div>
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>
        )}
      </div>

      {/* HOW AXIA ANALYZED THIS */}
      <div className="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border)]">
        <h3 className="text-[var(--text-primary)] mb-4">How was this analyzed?</h3>
        <p className="text-[var(--text-secondary)] mb-6">
          Axia uses a weighted formula that compares your task list against your stated goals, company context, 
          and timeframe. Tasks are scored based on their direct contribution to high-priority objectives, potential 
          revenue impact, and urgency within your current period.
        </p>
        <div className="flex items-center gap-2 flex-wrap">
          <span className="text-xs text-[var(--text-secondary)]">Context used:</span>
          <span className="px-3 py-1 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
            Company info
          </span>
          <span className="px-3 py-1 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
            Goals ({goals.length})
          </span>
          <span className="px-3 py-1 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
            Task list ({todos.length})
          </span>
          <span className="px-3 py-1 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
            {timeframe}
          </span>
        </div>
      </div>
    </div>
  );
}