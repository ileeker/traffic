<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use InvalidArgumentException;

class SimilarwebChange extends Model
{
    use HasFactory;

    protected $table = 'similarweb_changes';

    protected $fillable = [
        'domain',
        'record_month',
        'current_emv',
        
        // 月度变化
        'month_emv_change',
        'month_emv_trend',
        
        // 季度变化
        'quarter_emv_change',
        'quarter_emv_trend',
        
        // 半年变化
        'halfyear_emv_change',
        'halfyear_emv_trend',
        
        // 年度变化
        'year_emv_change',
        'year_emv_trend',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * 与 SimilarwebDomain 的关系
     */
    public function similarwebDomain()
    {
        return $this->belongsTo(SimilarwebDomain::class, 'domain', 'domain');
    }

    /**
     * 当前月份记录
     */
    public function scopeCurrentMonth($query)
    {
        return $query->where('record_month', now()->format('Y-m'));
    }

    /**
     * 按域名查询
     */
    public function scopeByDomain($query, $domain)
    {
        return $query->where('domain', $domain);
    }

    /**
     * 按记录月份查询
     */
    public function scopeByRecordMonth($query, $month)
    {
        return $query->where('record_month', $month);
    }

    /**
     * EMV月度变化查询
     */
    public function scopeByMonthEmvChange($query, $min = null, $max = null, $trend = null)
    {
        if ($min !== null) {
            $query->where('month_emv_change', '>=', $min);
        }
        if ($max !== null) {
            $query->where('month_emv_change', '<=', $max);
        }
        if ($trend !== null) {
            $query->where('month_emv_trend', $trend);
        }
        return $query;
    }

    /**
     * EMV季度变化查询
     */
    public function scopeByQuarterEmvChange($query, $min = null, $max = null, $trend = null)
    {
        if ($min !== null) {
            $query->where('quarter_emv_change', '>=', $min);
        }
        if ($max !== null) {
            $query->where('quarter_emv_change', '<=', $max);
        }
        if ($trend !== null) {
            $query->where('quarter_emv_trend', $trend);
        }
        return $query;
    }

    /**
     * 年度EMV变化查询
     */
    public function scopeByYearEmvChange($query, $min = null, $max = null, $trend = null)
    {
        if ($min !== null) {
            $query->where('year_emv_change', '>=', $min);
        }
        if ($max !== null) {
            $query->where('year_emv_change', '<=', $max);
        }
        if ($trend !== null) {
            $query->where('year_emv_trend', $trend);
        }
        return $query;
    }

    /**
     * 获取上升趋势的记录
     */
    public function scopeUpTrend($query, $period = 'month')
    {
        $valid_periods = ['month', 'quarter', 'halfyear', 'year'];
        
        if (!in_array($period, $valid_periods)) {
            throw new InvalidArgumentException("Invalid period: {$period}. Valid periods: " . implode(', ', $valid_periods));
        }
        
        return $query->where("{$period}_emv_trend", 'up');
    }

    /**
     * 获取下降趋势的记录
     */
    public function scopeDownTrend($query, $period = 'month')
    {
        $valid_periods = ['month', 'quarter', 'halfyear', 'year'];
        
        if (!in_array($period, $valid_periods)) {
            throw new InvalidArgumentException("Invalid period: {$period}. Valid periods: " . implode(', ', $valid_periods));
        }
        
        return $query->where("{$period}_emv_trend", 'down');
    }

    /**
     * 获取稳定趋势的记录
     */
    public function scopeStableTrend($query, $period = 'month')
    {
        $valid_periods = ['month', 'quarter', 'halfyear', 'year'];
        
        if (!in_array($period, $valid_periods)) {
            throw new InvalidArgumentException("Invalid period: {$period}. Valid periods: " . implode(', ', $valid_periods));
        }
        
        return $query->where("{$period}_emv_trend", 'stable');
    }

    /**
     * 按变化幅度排序
     */
    public function scopeOrderByChange($query, $period = 'month', $direction = 'desc')
    {
        $valid_periods = ['month', 'quarter', 'halfyear', 'year'];
        
        if (!in_array($period, $valid_periods)) {
            throw new InvalidArgumentException("Invalid period: {$period}. Valid periods: " . implode(', ', $valid_periods));
        }
        
        return $query->orderBy("{$period}_emv_change", $direction);
    }

    /**
     * 获取最大变化的记录（绝对值）
     */
    public function scopeOrderByAbsChange($query, $period = 'month', $direction = 'desc')
    {
        $valid_periods = ['month', 'quarter', 'halfyear', 'year'];
        
        if (!in_array($period, $valid_periods)) {
            throw new InvalidArgumentException("Invalid period: {$period}. Valid periods: " . implode(', ', $valid_periods));
        }
        
        return $query->orderByRaw("ABS({$period}_emv_change) {$direction}");
    }

    /**
     * 获取格式化的变化数据
     */
    public function getFormattedChanges($period = 'month')
    {
        $valid_periods = ['month', 'quarter', 'halfyear', 'year'];
        if (!in_array($period, $valid_periods)) {
            throw new InvalidArgumentException("Invalid period: {$period}");
        }
        
        return [
            'emv' => [
                'change' => $this->{"{$period}_emv_change"},
                'trend' => $this->{"{$period}_emv_trend"},
                'percentage' => $this->calculatePercentageChange(
                    $this->current_emv, 
                    $this->{"{$period}_emv_change"}
                )
            ]
        ];
    }

    /**
     * 计算百分比变化
     */
    private function calculatePercentageChange($current, $change)
    {
        if (!$current || !$change) {
            return null;
        }
        
        $previous = $current - $change;
        if ($previous == 0) {
            return null;
        }
        
        return round(($change / $previous) * 100, 2);
    }

    /**
     * 获取所有时间周期的EMV变化摘要
     */
    public function getEmvChangesSummary()
    {
        $periods = ['month', 'quarter', 'halfyear', 'year'];
        $summary = [];
        
        foreach ($periods as $period) {
            $summary[$period] = $this->getFormattedChanges($period);
        }
        
        return $summary;
    }
}