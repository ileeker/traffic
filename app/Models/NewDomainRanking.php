<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NewDomainRanking extends Model
{
    use HasFactory;

    protected $table = 'new_domain_rankings';

    protected $fillable = [
        'domain',
        'current_ranking',
        'daily_change',
        'daily_trend',
        'week_change',
        'week_trend',
        'biweek_change',
        'biweek_trend',
        'triweek_change',
        'triweek_trend',
        'registered_at',
        'is_visible',
        'category',
        'language',
        'introduction',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'is_visible' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}