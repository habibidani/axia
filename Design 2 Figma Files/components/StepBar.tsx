import { Building2, Target, CheckSquare, BarChart3, Sun, Moon } from 'lucide-react';

interface StepBarProps {
  currentStep: string;
  onStepChange: (step: string) => void;
  profileDropdown?: React.ReactNode;
  isDarkMode: boolean;
  onToggleTheme: () => void;
}

const steps = [
  { id: 'company', label: 'Company', icon: Building2 },
  { id: 'goals', label: 'Goals', icon: Target },
  { id: 'todos', label: 'To-Dos', icon: CheckSquare },
  { id: 'analysis', label: 'Analysis', icon: BarChart3 },
];

export function StepBar({ currentStep, onStepChange, profileDropdown, isDarkMode, onToggleTheme }: StepBarProps) {
  const currentIndex = steps.findIndex(step => step.id === currentStep);
  
  return (
    <div className="h-14 bg-gradient-to-b from-[var(--bg-secondary)] to-[var(--bg-primary)] border-b border-[var(--border)] flex items-center justify-between px-6">
      {/* Segmented Pill Navigation */}
      <div className="relative flex items-center gap-1 bg-[var(--bg-tertiary)] rounded-full p-1">
        {/* Sliding highlight background */}
        <div
          className="absolute h-[calc(100%-8px)] bg-[#E94B8C] rounded-full transition-all duration-300 ease-out"
          style={{
            width: `calc(${100 / steps.length}% - 4px)`,
            left: `calc(${(currentIndex * 100) / steps.length}% + 4px)`,
          }}
        />
        
        {/* Step buttons */}
        {steps.map((step) => {
          const Icon = step.icon;
          const isActive = currentStep === step.id;
          
          return (
            <button
              key={step.id}
              onClick={() => onStepChange(step.id)}
              className={`relative z-10 px-4 py-1.5 rounded-full flex items-center gap-2 transition-colors ${
                isActive
                  ? 'text-[var(--text-primary)]'
                  : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)]'
              }`}
            >
              <Icon className="w-3.5 h-3.5" />
              <span className="text-sm">{step.label}</span>
            </button>
          );
        })}
      </div>

      {/* Right icons */}
      <div className="flex items-center gap-4">
        {/* Theme Toggle */}
        <button
          onClick={onToggleTheme}
          className="w-8 h-8 rounded-full flex items-center justify-center text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-tertiary)] transition-all"
        >
          {isDarkMode ? (
            <Sun className="w-4 h-4 transition-opacity duration-300" />
          ) : (
            <Moon className="w-4 h-4 transition-opacity duration-300" />
          )}
        </button>
        
        {profileDropdown}
      </div>
    </div>
  );
}