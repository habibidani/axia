<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'owner_user_id',
        'name',
        'business_model',
        'team_cofounders',
        'team_employees',
        'user_position',
        'customer_profile',
        'market_insights',
        'website',
    ];

    protected function casts(): array
    {
        return [
            'team_cofounders' => 'integer',
            'team_employees' => 'integer',
        ];
    }

    /**
     * Get the user that owns the company.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Get the goals for the company.
     */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    /**
     * Get the runs for the company.
     */
    public function runs(): HasMany
    {
        return $this->hasMany(Run::class);
    }

    /**
     * Get the top KPI for the company.
     */
    public function getTopKpiAttribute()
    {
        return GoalKpi::whereHas('goal', function($query) {
            $query->where('company_id', $this->id);
        })->where('is_top_kpi', true)->first();
    }
}


