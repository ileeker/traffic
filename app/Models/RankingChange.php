<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RankingChange extends Model
{
    use HasFactory;

    protected $table = 'ranking_changes';

    protected $fillable = [
        'domain',
        'record_date',
        'current_ranking',
        'daily_change',          // 新增：昨日变化
        'daily_trend',           // 新增：昨日趋势
        'week_change',
        'week_trend',
        'biweek_change',
        'biweek_trend',
        'triweek_change',
        'triweek_trend',
        'month_change',
        'month_trend',
        'quarter_change',
        'quarter_trend',
        'year_change',
        'year_trend',
    ];

    protected $casts = [
        'record_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 与 Domain 的关系
     */
    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain', 'domain');
    }

}