<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalKpi extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'goal_id',
        'name',
        'current_value',
        'target_value',
        'unit',
        'time_frame',
        'is_top_kpi',
    ];

    protected function casts(): array
    {
        return [
            'current_value' => 'decimal:2',
            'target_value' => 'decimal:2',
            'is_top_kpi' => 'boolean',
        ];
    }

    /**
     * Get the goal that owns the KPI.
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }
}

