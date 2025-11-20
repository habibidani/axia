<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\Goal;
use App\Models\GoalKpi;
use App\Services\WebhookAiService;
use Livewire\Component;

class Onboarding extends Component
{
    public $step = 1; // Current step (1, 2, or 3)
    
    // Step 1: User Profile
    public $first_name;
    public $last_name;
    public $email;
    
    // Step 2: Company fields
    public $companyMode = 'manual'; // 'manual' or 'smart'
    public $companySmartText = '';
    public $companyExtracting = false;
    
    public $name;
    public $business_model;
    public $team_cofounders;
    public $team_employees;
    public $user_position;
    public $companyAdditionalInformation;

    // Step 3: Goals
    public $goalsMode = 'manual'; // 'manual' or 'smart'
    public $goalsSmartText = '';
    public $goalsExtracting = false;
    
    public $goals = [];
    public $standaloneKpis = [];

    public function mount()
    {
        $user = auth()->user();
        
        // Pre-fill user info
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        
        // Pre-fill if company exists
        $company = $user->company;
        if ($company) {
            $this->name = $company->name;
            $this->business_model = $company->business_model;
            $this->team_cofounders = $company->team_cofounders;
            $this->team_employees = $company->team_employees;
            $this->user_position = $company->user_position;

            // Load existing goals
            $this->goals = $company->goals()->with('kpis')->get()->map(function ($goal) {
                return [
                    'id' => $goal->id,
                    'title' => $goal->title,
                    'kpis' => $goal->kpis->map(function ($kpi) {
                        return [
                            'id' => $kpi->id,
                            'name' => $kpi->name,
                            'current_value' => $kpi->current_value,
                            'target_value' => $kpi->target_value,
                            'unit' => $kpi->unit,
                            'is_top_kpi' => $kpi->is_top_kpi,
                            'additional_information' => $kpi->additional_information,
                        ];
                    })->toArray(),
                    'additional_information' => $goal->additional_information,
                ];
            })->toArray();
        }

        // Add empty goal if none exist
        if (empty($this->goals)) {
            $this->addGoal();
        }

        // Load company additional information
        if ($company) {
            $this->companyAdditionalInformation = $company->additional_information;
        }
    }
    
    public function extractCompanyInfo()
    {
        $this->validate([
            'companySmartText' => 'required|string|min:10',
        ]);

        $this->companyExtracting = true;

        try {
            $service = new WebhookAiService();
            $result = $service->extractCompanyInfo($this->companySmartText);
            
            $this->name = $result['name'] ?? null;
            $this->business_model = $result['business_model'] ?? null;
            $this->team_cofounders = $result['team_cofounders'] ?? null;
            $this->team_employees = $result['team_employees'] ?? null;
            $this->user_position = $result['user_position'] ?? null;
            
            $this->companyMode = 'manual';
            $this->companyExtracting = false;
        } catch (\Exception $e) {
            $this->companyExtracting = false;
            session()->flash('error', 'Could not extract company info: ' . $e->getMessage());
            $this->companyMode = 'manual';
        }
    }

    public function extractGoalsInfo()
    {
        $this->validate([
            'goalsSmartText' => 'required|string|min:10',
        ]);

        $this->goalsExtracting = true;

        try {
            $service = new WebhookAiService();
            $result = $service->extractGoalsAndKpis($this->goalsSmartText);
            
            $this->goals = [];
            if (isset($result['goals'])) {
                foreach ($result['goals'] as $goalData) {
                    $this->goals[] = [
                        'id' => null,
                        'title' => $goalData['title'] ?? '',
                        'kpis' => $goalData['kpis'] ?? [],
                        'additional_information' => null,
                    ];
                }
            }
            
            if (isset($result['standalone_kpis'])) {
                $this->standaloneKpis = $result['standalone_kpis'];
            }
            
            $this->goalsMode = 'manual';
            $this->goalsExtracting = false;
        } catch (\Exception $e) {
            $this->goalsExtracting = false;
            session()->flash('error', 'Could not extract goals: ' . $e->getMessage());
            $this->goalsMode = 'manual';
        }
    }

    public function nextStep()
    {
        if ($this->step === 1) {
            // Save user profile
            $user = auth()->user();
            $user->update([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email ?: $user->email,
            ]);
        }
        
        $this->step++;
    }
    
    public function previousStep()
    {
        $this->step--;
    }

    public function addGoal()
    {
        $this->goals[] = [
            'id' => null,
            'title' => '',
            'kpis' => [],
            'additional_information' => null,
        ];
    }

    public function removeGoal($index)
    {
        unset($this->goals[$index]);
        $this->goals = array_values($this->goals);
    }

    public function addKpi($goalIndex)
    {
        $this->goals[$goalIndex]['kpis'][] = [
            'id' => null,
            'name' => '',
            'current_value' => null,
            'target_value' => null,
            'unit' => '',
            'is_top_kpi' => false,
            'additional_information' => null,
        ];
    }

    public function removeKpi($goalIndex, $kpiIndex)
    {
        unset($this->goals[$goalIndex]['kpis'][$kpiIndex]);
        $this->goals[$goalIndex]['kpis'] = array_values($this->goals[$goalIndex]['kpis']);
    }

    public function setTopKpi($goalIndex, $kpiIndex)
    {
        // Unset all other top KPIs
        foreach ($this->goals as $gIndex => $goal) {
            foreach ($goal['kpis'] as $kIndex => $kpi) {
                $this->goals[$gIndex]['kpis'][$kIndex]['is_top_kpi'] = false;
            }
        }
        
        // Set this as top KPI
        $this->goals[$goalIndex]['kpis'][$kpiIndex]['is_top_kpi'] = true;
    }

    public function save()
    {
        $company = auth()->user()->company;

        if (!$company) {
            $company = Company::create([
                'owner_user_id' => auth()->id(),
            ]);
        }

        // Save company info
        $updateData = [
            'name' => $this->name,
            'business_model' => $this->business_model,
            'team_cofounders' => $this->team_cofounders,
            'team_employees' => $this->team_employees,
            'user_position' => $this->user_position,
            'additional_information' => $this->companyAdditionalInformation,
        ];

        // If extracted from smart text, save original text
        // Check if we're in manual mode after extraction (mode switches to manual after extraction)
        if ($this->companyMode === 'manual' && !empty($this->companySmartText)) {
            $updateData['original_smart_text'] = $this->companySmartText;
            $updateData['extracted_from_text'] = true;
        }

        $company->update($updateData);

        // Unset all top KPIs first (both goal-linked and standalone)
        GoalKpi::where(function($query) use ($company) {
            $query->whereHas('goal', fn($q) => $q->where('company_id', $company->id))
                  ->orWhere('company_id', $company->id);
        })->update(['is_top_kpi' => false]);

        // Save goals and KPIs
        foreach ($this->goals as $goalData) {
            if (empty($goalData['title'])) {
                continue;
            }

            $goal = $goalData['id']
                ? Goal::find($goalData['id'])
                : new Goal(['company_id' => $company->id]);

            $goal->fill([
                'title' => $goalData['title'],
                'is_active' => true,
                'additional_information' => $goalData['additional_information'] ?? null,
            ]);
            
            // If extracted from smart text, save original text
            // Check if we're in manual mode after extraction
            if ($this->goalsMode === 'manual' && !empty($this->goalsSmartText)) {
                $goal->original_smart_text = $this->goalsSmartText;
                $goal->extracted_from_text = true;
            }
            
            $goal->company_id = $company->id;
            $goal->save();

            // Save KPIs
            foreach ($goalData['kpis'] as $kpiData) {
                if (empty($kpiData['name'])) {
                    continue;
                }

                $kpi = $kpiData['id']
                    ? GoalKpi::find($kpiData['id'])
                    : new GoalKpi(['goal_id' => $goal->id]);

                $kpi->name = $kpiData['name'];
                $kpi->current_value = $kpiData['current_value'] ?? null;
                $kpi->target_value = $kpiData['target_value'] ?? null;
                $kpi->unit = $kpiData['unit'] ?? null;
                $kpi->is_top_kpi = isset($kpiData['is_top_kpi']) && $kpiData['is_top_kpi'] ? true : false;
                $kpi->additional_information = $kpiData['additional_information'] ?? null;
                $kpi->goal_id = $goal->id;
                
                // If extracted from smart text, save original text
                // Check if we're in manual mode after extraction
                if ($this->goalsMode === 'manual' && !empty($this->goalsSmartText)) {
                    $kpi->original_smart_text = $this->goalsSmartText;
                    $kpi->extracted_from_text = true;
                }
                
                $kpi->save();
            }
        }

        // Save standalone KPIs
        foreach ($this->standaloneKpis as $kpiData) {
            if (empty($kpiData['name'])) {
                continue;
            }

            $kpi = $kpiData['id']
                ? GoalKpi::find($kpiData['id'])
                : new GoalKpi();

            $kpi->name = $kpiData['name'];
            $kpi->current_value = $kpiData['current_value'] ?? null;
            $kpi->target_value = $kpiData['target_value'] ?? null;
            $kpi->unit = $kpiData['unit'] ?? null;
            $kpi->is_top_kpi = isset($kpiData['is_top_kpi']) && $kpiData['is_top_kpi'] ? true : false;
            $kpi->additional_information = $kpiData['additional_information'] ?? null;
            $kpi->company_id = $company->id;
            $kpi->goal_id = null;
            
            // If extracted from smart text, save original text
            if ($this->goalsExtracting === false && !empty($this->goalsSmartText)) {
                $kpi->original_smart_text = $this->goalsSmartText;
                $kpi->extracted_from_text = true;
            }
            
            $kpi->save();
        }

        session()->flash('success', 'Setup complete! Ready to analyze your tasks.');
        
        return redirect()->route('home');
    }

    public function skipStep()
    {
        if ($this->step < 3) {
            $this->step++;
        } else {
            // On last step, skip means go to home
            return redirect()->route('home');
        }
    }

    public function render()
    {
        return view('livewire.onboarding')->layout('components.layouts.app');
    }
}

