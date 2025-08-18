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
        'week_change',
        'week_trend',
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
    ];

    /**
     * 与 Domain 的关系
     */
    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain', 'domain');
    }

    /**
     * 今日记录
     */
    public function scopeToday($query)
    {
        return $query->whereDate('record_date', today());
    }

    /**
     * 按域名查询
     */
    public function scopeByDomain($query, $domain)
    {
        return $query->where('domain', $domain);
    }

    /**
     * 按记录日期查询
     */
    public function scopeByRecordDate($query, $date)
    {
        return $query->whereDate('record_date', $date);
    }

    /**
     * 周变化查询
     */
    public function scopeByWeekChange($query, $min = null, $max = null, $trend = null)
    {
        if ($min !== null) {
            $query->where('week_change', '>=', $min);
        }
        if ($max !== null) {
            $query->where('week_change', '<=', $max);
        }
        if ($trend !== null) {
            $query->where('week_trend', $trend);
        }
        return $query;
    }

    /**
     * 月变化查询
     */
    public function scopeByMonthChange($query, $min = null, $max = null, $trend = null)
    {
        if ($min !== null) {
            $query->where('month_change', '>=', $min);
        }
        if ($max !== null) {
            $query->where('month_change', '<=', $max);
        }
        if ($trend !== null) {
            $query->where('month_trend', $trend);
        }
        return $query;
    }

    /**
     * 季度变化查询
     */
    public function scopeByQuarterChange($query, $min = null, $max = null, $trend = null)
    {
        if ($min !== null) {
            $query->where('quarter_change', '>=', $min);
        }
        if ($max !== null) {
            $query->where('quarter_change', '<=', $max);
        }
        if ($trend !== null) {
            $query->where('quarter_trend', $trend);
        }
        return $query;
    }

    /**
     * 年变化查询
     */
    public function scopeByYearChange($query, $min = null, $max = null, $trend = null)
    {
        if ($min !== null) {
            $query->where('year_change', '>=', $min);
        }
        if ($max !== null) {
            $query->where('year_change', '<=', $max);
        }
        if ($trend !== null) {
            $query->where('year_trend', $trend);
        }
        return $query;
    }

    /**
     * 获取上升趋势的记录
     */
    public function scopeUpTrend($query, $period = 'week')
    {
        return $query->where("{$period}_trend", 'up');
    }

    /**
     * 获取下降趋势的记录
     */
    public function scopeDownTrend($query, $period = 'week')
    {
        return $query->where("{$period}_trend", 'down');
    }

    /**
     * 获取稳定趋势的记录
     */
    public function scopeStableTrend($query, $period = 'week')
    {
        return $query->where("{$period}_trend", 'stable');
    }

    /**
     * 按变化幅度排序
     */
    public function scopeOrderByChange($query, $period = 'week', $direction = 'desc')
    {
        return $query->orderBy("{$period}_change", $direction);
    }
}