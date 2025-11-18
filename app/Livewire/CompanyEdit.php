<?php

namespace App\Livewire;

use App\Models\Company;
use Livewire\Component;

class CompanyEdit extends Component
{
    public $name;
    public $business_model;
    public $team_cofounders;
    public $team_employees;
    public $user_position;
    public $customer_profile;
    public $market_insights;
    public $website;

    public function mount()
    {
        $company = auth()->user()->company;
        
        if ($company) {
            $this->name = $company->name;
            $this->business_model = $company->business_model;
            $this->team_cofounders = $company->team_cofounders;
            $this->team_employees = $company->team_employees;
            $this->user_position = $company->user_position;
            $this->customer_profile = $company->customer_profile;
            $this->market_insights = $company->market_insights;
            $this->website = $company->website;
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'nullable|string|max:255',
            'business_model' => 'nullable|in:b2b_saas,b2c,marketplace,agency,other',
            'team_cofounders' => 'nullable|integer|min:0',
            'team_employees' => 'nullable|integer|min:0',
            'user_position' => 'nullable|string|max:255',
            'customer_profile' => 'nullable|string',
            'market_insights' => 'nullable|string',
            'website' => 'nullable|url|max:255',
        ]);

        $company = auth()->user()->company;

        if (!$company) {
            $company = Company::create([
                'owner_user_id' => auth()->id(),
            ]);
        }

        $company->update([
            'name' => $this->name,
            'business_model' => $this->business_model,
            'team_cofounders' => $this->team_cofounders,
            'team_employees' => $this->team_employees,
            'user_position' => $this->user_position,
            'customer_profile' => $this->customer_profile,
            'market_insights' => $this->market_insights,
            'website' => $this->website,
        ]);

        session()->flash('success', 'Company information saved successfully!');
        
        return redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.company-edit')->layout('components.layouts.app');
    }
}

