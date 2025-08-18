<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Domain extends Model
{
    use HasFactory;

    protected $table = 'domains';

    protected $fillable = [
        'domain',
        'record_date',
        'current_ranking',
        'ranking_data',
        'last_updated',
    ];

    protected $casts = [
        'record_date' => 'date',
        'ranking_data' => 'array',
        'last_updated' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * 与 RankingChange 的一对一关系
     */
    public function rankingChange()
    {
        return $this->hasOne(RankingChange::class, 'domain', 'domain');
    }

    /**
     * 获取今日的排名变化
     */
    public function todayRankingChange()
    {
        return $this->hasOne(RankingChange::class, 'domain', 'domain')
                    ->whereDate('record_date', today());
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
     * 按排名范围查询
     */
    public function scopeByRankingRange($query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->where('current_ranking', '>=', $min);
        }
        if ($max !== null) {
            $query->where('current_ranking', '<=', $max);
        }
        return $query;
    }

    /**
     * 排名排序
     */
    public function scopeOrderByRanking($query, $direction = 'asc')
    {
        return $query->orderBy('current_ranking', $direction);
    }
}