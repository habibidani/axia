import { useState, useEffect } from 'react';
import { ChatPanel } from './components/ChatPanel';
import { StepBar } from './components/StepBar';
import { CompanyPage } from './components/CompanyPage';
import { GoalsPage } from './components/GoalsPage';
import { ToDosPage } from './components/ToDosPage';
import { AnalysisPage } from './components/AnalysisPage';
import { ProfileDropdown } from './components/ProfileDropdown';
import { LoadingOverlay } from './components/LoadingOverlay';
import { ProfilePage } from './components/ProfilePage';
import { ProfileSettingsPage } from './components/ProfileSettingsPage';
import { PastAnalysesPage } from './components/PastAnalysesPage';
import { HowItWorksPage } from './components/HowItWorksPage';

interface Message {
  id: string;
  text: string;
  sender: 'user' | 'ai';
}

interface CompanyData {
  name: string;
  businessModel: string;
  stage: string;
  teamSize: string;
  timeframe: string;
  additionalInfo: string;
}

interface Goal {
  id: string;
  title: string;
  description: string;
  priority: 'high' | 'mid' | 'low';
}

interface ToDo {
  id: string;
  text: string;
}

interface Analysis {
  id: string;
  date: string;
  score: number;
  tasksAnalyzed: number;
  goals: number;
}

type Page = 'main' | 'profile' | 'settings' | 'past-analyses' | 'how-it-works';

export default function App() {
  const [currentStep, setCurrentStep] = useState('analysis');
  const [currentPage, setCurrentPage] = useState<Page>('main');
  const [isLoading, setIsLoading] = useState(false);
  const [showIntro, setShowIntro] = useState(false);
  const [selectedAnalysisId, setSelectedAnalysisId] = useState<string | null>(null);
  const [isDarkMode, setIsDarkMode] = useState(true);

  const [userProfile, setUserProfile] = useState({
    name: 'John Founder',
    email: 'john@acmecorp.com',
    company: 'Acme Corp',
  });

  const [pastAnalyses, setPastAnalyses] = useState<Analysis[]>([
    {
      id: '1',
      date: 'November 20, 2025',
      score: 55,
      tasksAnalyzed: 12,
      goals: 3,
    },
  ]);

  const [messages, setMessages] = useState<Message[]>([
    {
      id: '1',
      text: 'Welcome to Axia. Let me help you prioritize what matters.',
      sender: 'ai',
    },
  ]);

  const [companyData, setCompanyData] = useState<CompanyData>({
    name: 'Acme Corp',
    businessModel: 'SaaS',
    stage: 'PMF',
    teamSize: '8-12',
    timeframe: 'This week',
    additionalInfo: 'B2B productivity tool for remote teams',
  });

  const [goals, setGoals] = useState<Goal[]>([
    {
      id: '1',
      title: 'Launch new enterprise pricing tier',
      description: 'Create and launch enterprise plan to target larger customers',
      priority: 'high',
    },
    {
      id: '2',
      title: 'Improve onboarding completion rate',
      description: 'Increase from 45% to 65%',
      priority: 'high',
    },
    {
      id: '3',
      title: 'Build referral program',
      description: 'Incentivize existing users to bring new customers',
      priority: 'mid',
    },
  ]);

  const [todos, setTodos] = useState<ToDo[]>([
    { id: '1', text: 'Finalize enterprise pricing model' },
    { id: '2', text: 'Create sales deck for enterprise customers' },
    { id: '3', text: 'Update website with new pricing tier' },
    { id: '4', text: 'Fix onboarding step 3 bug' },
    { id: '5', text: 'Add progress indicators to onboarding' },
    { id: '6', text: 'Write blog post about new feature' },
    { id: '7', text: 'Update team wiki documentation' },
    { id: '8', text: 'Review analytics dashboard' },
    { id: '9', text: 'Schedule customer interviews' },
    { id: '10', text: 'Organize team offsite' },
    { id: '11', text: 'Update LinkedIn company page' },
    { id: '12', text: 'Research referral program tools' },
  ]);

  // Apply theme class to document root
  useEffect(() => {
    if (isDarkMode) {
      document.documentElement.classList.remove('light');
    } else {
      document.documentElement.classList.add('light');
    }
  }, [isDarkMode]);

  const handleSendMessage = (text: string) => {
    const userMessage: Message = {
      id: Date.now().toString(),
      text,
      sender: 'user',
    };
    setMessages([...messages, userMessage]);

    // Mock AI response
    setTimeout(() => {
      const aiMessage: Message = {
        id: (Date.now() + 1).toString(),
        text: 'I understand. Let me help you with that.',
        sender: 'ai',
      };
      setMessages((prev) => [...prev, aiMessage]);
    }, 500);
  };

  const handleNewWeek = () => {
    setGoals([]);
    setTodos([]);
    setCurrentStep('company');
  };

  const handleProfileNavigate = (page: 'profile' | 'settings' | 'past-analyses' | 'how-it-works') => {
    setCurrentPage(page);
  };

  const handleLogout = () => {
    console.log('Logout clicked');
    // Add logout logic here
  };

  const handleSaveProfile = (profile: { 
    name: string; 
    email: string; 
    company: string;
    businessModel: string;
    stage: string;
    teamSize: string;
    timeframe: string;
    additionalInfo: string;
  }) => {
    setUserProfile({ 
      name: profile.name, 
      email: profile.email,
      company: profile.company 
    });
    setCompanyData({
      ...companyData,
      businessModel: profile.businessModel,
      stage: profile.stage,
      teamSize: profile.teamSize,
      timeframe: profile.timeframe,
      additionalInfo: profile.additionalInfo,
    });
    setCurrentPage('main');
  };

  const handleViewAnalysis = (id: string) => {
    setSelectedAnalysisId(id);
    setCurrentPage('main');
    setCurrentStep('analysis');
  };

  const handleStartSetup = () => {
    setCurrentPage('main');
    setCurrentStep('company');
  };

  return (
    <div className="flex h-screen bg-[var(--bg-primary)]">
      {/* Loading Overlay */}
      {isLoading && <LoadingOverlay />}

      {/* Chat Panel - only show on main page */}
      {currentPage === 'main' && (
        <ChatPanel messages={messages} onSendMessage={handleSendMessage} />
      )}

      {/* Main Content */}
      <div className="flex-1 flex flex-col">
        {/* Step Bar with Profile Dropdown - only show on main page */}
        {currentPage === 'main' ? (
          <StepBar 
            currentStep={currentStep} 
            onStepChange={setCurrentStep}
            isDarkMode={isDarkMode}
            onToggleTheme={() => setIsDarkMode(!isDarkMode)}
            profileDropdown={
              <ProfileDropdown
                onNavigate={handleProfileNavigate}
                onLogout={handleLogout}
              />
            }
          />
        ) : null}

        {/* Content Area */}
        <div className="flex-1 overflow-y-auto">
          {currentPage === 'main' && (
            <>
              {currentStep === 'company' && (
                <CompanyPage
                  data={companyData}
                  onUpdate={setCompanyData}
                  onNext={() => setCurrentStep('goals')}
                />
              )}

              {currentStep === 'goals' && (
                <GoalsPage
                  goals={goals}
                  onUpdate={setGoals}
                  onNext={() => setCurrentStep('todos')}
                />
              )}

              {currentStep === 'todos' && (
                <ToDosPage
                  todos={todos}
                  onUpdate={setTodos}
                  onAnalyze={() => setCurrentStep('analysis')}
                />
              )}

              {currentStep === 'analysis' && (
                <AnalysisPage
                  goals={goals}
                  todos={todos}
                  timeframe={companyData.timeframe}
                  companyData={{
                    name: companyData.name,
                    businessModel: companyData.businessModel,
                    teamSize: companyData.teamSize,
                  }}
                  onNewWeek={handleNewWeek}
                />
              )}
            </>
          )}

          {currentPage === 'profile' && (
            <ProfilePage
              onBack={() => setCurrentPage('main')}
              userProfile={userProfile}
              companyData={companyData}
              onSave={handleSaveProfile}
            />
          )}

          {currentPage === 'settings' && (
            <ProfileSettingsPage
              onBack={() => setCurrentPage('main')}
              userProfile={userProfile}
              onSave={handleSaveProfile}
            />
          )}

          {currentPage === 'past-analyses' && (
            <PastAnalysesPage
              onBack={() => setCurrentPage('main')}
              analyses={pastAnalyses}
              onViewAnalysis={handleViewAnalysis}
            />
          )}

          {currentPage === 'how-it-works' && (
            <HowItWorksPage
              onBack={() => setCurrentPage('main')}
              onStartSetup={handleStartSetup}
            />
          )}
        </div>
      </div>
    </div>
  );
}