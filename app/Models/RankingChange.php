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

    /**
     * 查询作用域：获取指定日期的记录
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('record_date', $date);
    }

    /**
     * 查询作用域：获取指定域名的记录
     */
    public function scopeForDomain($query, $domain)
    {
        return $query->where('domain', $domain);
    }

    /**
     * 查询作用域：按日变化排序
     */
    public function scopeOrderByDailyChange($query, $direction = 'desc')
    {
        return $query->orderBy('daily_change', $direction);
    }

    /**
     * 查询作用域：按周变化排序
     */
    public function scopeOrderByWeekChange($query, $direction = 'desc')
    {
        return $query->orderBy('week_change', $direction);
    }

    /**
     * 查询作用域：按月变化排序
     */
    public function scopeOrderByMonthChange($query, $direction = 'desc')
    {
        return $query->orderBy('month_change', $direction);
    }

    /**
     * 查询作用域：获取上升趋势的记录
     */
    public function scopeUpTrend($query, $period = 'daily')
    {
        return $query->where($period . '_trend', 'up');
    }

    /**
     * 查询作用域：获取下降趋势的记录
     */
    public function scopeDownTrend($query, $period = 'daily')
    {
        return $query->where($period . '_trend', 'down');
    }

    /**
     * 查询作用域：获取稳定趋势的记录
     */
    public function scopeStableTrend($query, $period = 'daily')
    {
        return $query->where($period . '_trend', 'stable');
    }

    /**
     * 访问器：格式化日变化显示
     */
    public function getDailyChangeFormattedAttribute()
    {
        if (is_null($this->daily_change)) {
            return 'N/A';
        }
        
        $prefix = $this->daily_change > 0 ? '+' : '';
        return $prefix . $this->daily_change;
    }

    /**
     * 访问器：格式化周变化显示
     */
    public function getWeekChangeFormattedAttribute()
    {
        if (is_null($this->week_change)) {
            return 'N/A';
        }
        
        $prefix = $this->week_change > 0 ? '+' : '';
        return $prefix . $this->week_change;
    }

    /**
     * 访问器：格式化月变化显示
     */
    public function getMonthChangeFormattedAttribute()
    {
        if (is_null($this->month_change)) {
            return 'N/A';
        }
        
        $prefix = $this->month_change > 0 ? '+' : '';
        return $prefix . $this->month_change;
    }

    /**
     * 访问器：获取趋势图标
     */
    public function getDailyTrendIconAttribute()
    {
        return match($this->daily_trend) {
            'up' => '↗️',
            'down' => '↘️',
            'stable' => '→',
            default => '-'
        };
    }

    /**
     * 访问器：获取趋势颜色类
     */
    public function getDailyTrendColorAttribute()
    {
        return match($this->daily_trend) {
            'up' => 'text-green-500',
            'down' => 'text-red-500',
            'stable' => 'text-gray-500',
            default => 'text-gray-400'
        };
    }

    /**
     * 静态方法：获取最新记录日期
     */
    public static function getLatestRecordDate()
    {
        return static::max('record_date');
    }

    /**
     * 静态方法：获取指定域名的最新记录
     */
    public static function getLatestForDomain($domain)
    {
        return static::where('domain', $domain)
            ->orderBy('record_date', 'desc')
            ->first();
    }

    /**
     * 静态方法：获取今日表现最好的域名
     */
    public static function getTopDailyPerformers($limit = 10, $date = null)
    {
        $date = $date ?: static::getLatestRecordDate();
        
        return static::where('record_date', $date)
            ->where('daily_change', '>', 0)
            ->orderBy('daily_change', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 静态方法：获取今日表现最差的域名
     */
    public static function getWorstDailyPerformers($limit = 10, $date = null)
    {
        $date = $date ?: static::getLatestRecordDate();
        
        return static::where('record_date', $date)
            ->where('daily_change', '<', 0)
            ->orderBy('daily_change', 'asc')
            ->limit($limit)
            ->get();
    }
}