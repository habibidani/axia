<?php

namespace App\Livewire;

use App\Models\Company;
use App\Services\ExampleContentService;
use App\Services\WebhookAiService;
use Livewire\Component;

class CompanyEdit extends Component
{
    public $mode = 'manual'; // 'manual' or 'smart'
    public $smartText = '';
    public $extracting = false;
    public $extracted = null;
    
    public $name;
    public $business_model;
    public $team_cofounders;
    public $team_employees;
    public $user_position;
    public $customer_profile;
    public $market_insights;
    public $website;
    public $additional_information;

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
            $this->additional_information = $company->additional_information;
        }
    }

    public function insertExample($index)
    {
        $examples = ExampleContentService::getCompanyExamples();
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
            $this->extracted = $service->extractCompanyInfo($this->smartText);
            
            // Auto-fill fields with extracted data
            $this->name = $this->extracted['name'] ?? null;
            $this->business_model = $this->extracted['business_model'] ?? null;
            $this->team_cofounders = $this->extracted['team_cofounders'] ?? null;
            $this->team_employees = $this->extracted['team_employees'] ?? null;
            $this->user_position = $this->extracted['user_position'] ?? null;
            $this->customer_profile = $this->extracted['customer_profile'] ?? null;
            $this->market_insights = $this->extracted['market_insights'] ?? null;
            $this->website = $this->extracted['website'] ?? null;
            
            // Store original smart text for later saving
            // We'll save it in the save() method
            
            $this->mode = 'manual'; // Switch to manual mode to show extracted fields
            $this->extracting = false;
        } catch (\Exception $e) {
            $this->extracting = false;
            session()->flash('error', 'Could not extract information. Please fill manually: ' . $e->getMessage());
            $this->mode = 'manual';
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
            'additional_information' => 'nullable|string',
        ]);

        $company = auth()->user()->company;

        if (!$company) {
            $company = Company::create([
                'owner_user_id' => auth()->id(),
            ]);
        }

        $updateData = [
            'name' => $this->name,
            'business_model' => $this->business_model,
            'team_cofounders' => $this->team_cofounders,
            'team_employees' => $this->team_employees,
            'user_position' => $this->user_position,
            'customer_profile' => $this->customer_profile,
            'market_insights' => $this->market_insights,
            'website' => $this->website,
            'additional_information' => $this->additional_information,
        ];

        // If we extracted from smart text, save the original text and mark as extracted
        if ($this->extracted && !empty($this->smartText)) {
            $updateData['original_smart_text'] = $this->smartText;
            $updateData['extracted_from_text'] = true;
        }

        $company->update($updateData);

        session()->flash('success', 'Company information saved successfully!');
        
        return redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.company-edit')->layout('components.layouts.app');
    }
}

