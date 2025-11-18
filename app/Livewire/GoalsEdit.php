<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\Goal;
use App\Models\GoalKpi;
use Livewire\Component;

class GoalsEdit extends Component
{
    public $goals = [];

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
                        ];
                    })->toArray(),
                ];
            })->toArray();
        }

        if (empty($this->goals)) {
            $this->addGoal();
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

        // Unset all top KPIs first
        GoalKpi::whereHas('goal', fn($q) => $q->where('company_id', $company->id))
            ->update(['is_top_kpi' => false]);

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
            ]);
            
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

                $kpi->fill([
                    'name' => $kpiData['name'],
                    'current_value' => $kpiData['current_value'],
                    'target_value' => $kpiData['target_value'],
                    'unit' => $kpiData['unit'],
                    'time_frame' => $kpiData['time_frame'],
                    'is_top_kpi' => $kpiData['is_top_kpi'] ?? false,
                ]);
                
                $kpi->goal_id = $goal->id;
                $kpi->save();
            }
        }

        session()->flash('success', 'Goals and KPIs saved successfully!');
        
        return redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.goals-edit')->layout('components.layouts.app');
    }
}

