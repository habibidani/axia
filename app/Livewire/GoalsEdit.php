<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\Goal;
use App\Models\GoalKpi;
use App\Services\ExampleContentService;
use App\Services\WebhookAiService;
use Livewire\Component;

class GoalsEdit extends Component
{
    public $mode = 'manual'; // 'manual' or 'smart'
    public $smartText = '';
    public $extracting = false;
    public $extracted = null;
    
    public $goals = [];
    public $standaloneKpis = [];

    public function mount()
    {
        $company = auth()->user()->company;
        
        if ($company) {
            $this->goals = $company->goals()->with('kpis')->get()->map(function ($goal) {
                return [
                    'id' => $goal->id,
                    'title' => $goal->title,
                    'description' => $goal->description,
                    'priority' => $goal->priority,
                    'time_frame' => $goal->time_frame,
                    'is_active' => $goal->is_active,
                    'kpis' => $goal->kpis->map(function ($kpi) {
                        return [
                            'id' => $kpi->id,
                            'name' => $kpi->name,
                            'current_value' => $kpi->current_value,
                            'target_value' => $kpi->target_value,
                            'unit' => $kpi->unit,
                            'time_frame' => $kpi->time_frame,
                            'is_top_kpi' => $kpi->is_top_kpi,
                            'additional_information' => $kpi->additional_information,
                        ];
                    })->toArray(),
                    'additional_information' => $goal->additional_information,
                ];
            })->toArray();
        }

        if (empty($this->goals)) {
            $this->addGoal();
        }

        // Load standalone KPIs
        if ($company) {
            $this->standaloneKpis = $company->kpis->map(function ($kpi) {
                return [
                    'id' => $kpi->id,
                    'name' => $kpi->name,
                    'current_value' => $kpi->current_value,
                    'target_value' => $kpi->target_value,
                    'unit' => $kpi->unit,
                    'is_top_kpi' => $kpi->is_top_kpi,
                    'additional_information' => $kpi->additional_information,
                ];
            })->toArray();
        }
    }

    public function insertExample($index)
    {
        $examples = ExampleContentService::getGoalsExamples();
        if (isset($examples[$index])) {
            $this->smartText = $examples[$index]['content'];
        }
    }

    public function extractInfo()
    {
        $this->validate([
            'smartText' => 'required|string|min:10',
        ]);

        $this->extracting = true;

        try {
            $service = new WebhookAiService();
            $result = $service->extractGoalsAndKpis($this->smartText);
            
            $this->extracted = $result;
            
            // Convert extracted goals to component format
            $this->goals = [];
            if (isset($result['goals'])) {
                foreach ($result['goals'] as $goalData) {
                    $this->goals[] = [
                        'id' => null,
                        'title' => $goalData['title'] ?? '',
                        'description' => $goalData['description'] ?? '',
                        'priority' => $goalData['priority'] ?? 'medium',
                        'time_frame' => $goalData['time_frame'] ?? '',
                        'is_active' => true,
                        'kpis' => $goalData['kpis'] ?? [],
                        'additional_information' => null,
                    ];
                }
            }
            
            // Convert standalone KPIs
            if (isset($result['standalone_kpis'])) {
                $this->standaloneKpis = $result['standalone_kpis'];
            }
            
            $this->mode = 'manual'; // Switch to show extracted data
            $this->extracting = false;
        } catch (\Exception $e) {
            $this->extracting = false;
            session()->flash('error', 'Could not extract goals/KPIs. Please add manually: ' . $e->getMessage());
            $this->mode = 'manual';
        }
    }

    public function addGoal()
    {
        $this->goals[] = [
            'id' => null,
            'title' => '',
            'description' => '',
            'priority' => 'medium',
            'time_frame' => '',
            'is_active' => true,
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
            'time_frame' => '',
            'is_top_kpi' => false,
            'additional_information' => null,
        ];
    }

    public function removeKpi($goalIndex, $kpiIndex)
    {
        unset($this->goals[$goalIndex]['kpis'][$kpiIndex]);
        $this->goals[$goalIndex]['kpis'] = array_values($this->goals[$goalIndex]['kpis']);
    }

    public function addStandaloneKpi()
    {
        $this->standaloneKpis[] = [
            'id' => null,
            'name' => '',
            'current_value' => null,
            'target_value' => null,
            'unit' => '',
            'is_top_kpi' => false,
            'additional_information' => null,
        ];
    }

    public function removeStandaloneKpi($index)
    {
        unset($this->standaloneKpis[$index]);
        $this->standaloneKpis = array_values($this->standaloneKpis);
    }

    public function setTopKpi($goalIndex, $kpiIndex)
    {
        // Unset all other top KPIs (in goals)
        foreach ($this->goals as $gIndex => $goal) {
            foreach ($goal['kpis'] as $kIndex => $kpi) {
                $this->goals[$gIndex]['kpis'][$kIndex]['is_top_kpi'] = false;
            }
        }
        
        // Unset all standalone KPIs
        foreach ($this->standaloneKpis as $index => $kpi) {
            $this->standaloneKpis[$index]['is_top_kpi'] = false;
        }
        
        // Set this as top KPI
        $this->goals[$goalIndex]['kpis'][$kpiIndex]['is_top_kpi'] = true;
    }

    public function setTopKpiStandalone($kpiIndex)
    {
        // Unset all other top KPIs
        foreach ($this->goals as $gIndex => $goal) {
            foreach ($goal['kpis'] as $kIndex => $kpi) {
                $this->goals[$gIndex]['kpis'][$kIndex]['is_top_kpi'] = false;
            }
        }
        
        foreach ($this->standaloneKpis as $index => $kpi) {
            $this->standaloneKpis[$index]['is_top_kpi'] = false;
        }
        
        // Set this standalone KPI as top
        $this->standaloneKpis[$kpiIndex]['is_top_kpi'] = true;
    }

    public function save()
    {
        $company = auth()->user()->company;

        if (!$company) {
            $company = Company::create([
                'owner_user_id' => auth()->id(),
            ]);
        }

        // Unset all top KPIs first (both goal-linked and standalone)
        GoalKpi::where(function($query) use ($company) {
            $query->whereHas('goal', fn($q) => $q->where('company_id', $company->id))
                  ->orWhere('company_id', $company->id);
        })->update(['is_top_kpi' => false]);

        foreach ($this->goals as $goalData) {
            if (empty($goalData['title'])) {
                continue;
            }

            $goal = $goalData['id']
                ? Goal::find($goalData['id'])
                : new Goal(['company_id' => $company->id]);

            $goal->fill([
                'title' => $goalData['title'],
                'description' => $goalData['description'],
                'priority' => $goalData['priority'],
                'time_frame' => $goalData['time_frame'],
                'is_active' => $goalData['is_active'] ?? true,
                'additional_information' => $goalData['additional_information'] ?? null,
            ]);
            
            // If extracted from smart text, save original text
            if ($this->extracted && !empty($this->smartText)) {
                $goal->original_smart_text = $this->smartText;
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
                $kpi->time_frame = $kpiData['time_frame'] ?? null;
                $kpi->is_top_kpi = isset($kpiData['is_top_kpi']) && $kpiData['is_top_kpi'] ? true : false;
                $kpi->additional_information = $kpiData['additional_information'] ?? null;
                $kpi->goal_id = $goal->id;
                
                // If extracted from smart text, save original text
                if ($this->extracted && !empty($this->smartText)) {
                    $kpi->original_smart_text = $this->smartText;
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
            $kpi->goal_id = null; // Standalone!
            
            // If extracted from smart text, save original text
            if ($this->extracted && !empty($this->smartText)) {
                $kpi->original_smart_text = $this->smartText;
                $kpi->extracted_from_text = true;
            }
            
            $kpi->save();
        }

        session()->flash('success', 'Goals and KPIs saved successfully!');
        
        return redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.goals-edit')->layout('components.layouts.app');
    }
}

