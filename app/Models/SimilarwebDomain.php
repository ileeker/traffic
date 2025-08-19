<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SimilarwebDomain extends Model
{
    use HasFactory;

    protected $table = 'similarweb_domains';

    protected $fillable = [
        'domain',
        'title',
        'description',
        'global_rank',
        'category',
        'category_rank',
        'top_keywords',
        'ts_social',
        'ts_paid_referrals',
        'ts_mail',
        'ts_referrals',
        'ts_search',
        'ts_direct',
        'top_country_shares',
        'engagements_data',
        'traffic_data',
        'current_month',
        'current_emv',
        'current_bounce_rate',
        'current_pages_per_visit',
        'current_time_on_site',
        'last_updated',
    ];

    protected $casts = [
        'top_country_shares' => 'array',
        'engagements_data' => 'array',
        'traffic_data' => 'array',
        'ts_social' => 'decimal:6',
        'ts_paid_referrals' => 'decimal:6',
        'ts_mail' => 'decimal:6',
        'ts_referrals' => 'decimal:6',
        'ts_search' => 'decimal:6',
        'ts_direct' => 'decimal:6',
        'current_bounce_rate' => 'decimal:6',
        'current_pages_per_visit' => 'decimal:4',
        'current_time_on_site' => 'decimal:4',
        'last_updated' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * 与 SimilarwebChange 的一对一关系
     */
    public function similarwebChange()
    {
        return $this->hasOne(SimilarwebChange::class, 'domain', 'domain');
    }

    /**
     * 获取当前月份的变化数据
     */
    public function currentMonthChange()
    {
        return $this->hasOne(SimilarwebChange::class, 'domain', 'domain')
                    ->where('record_month', $this->current_month ?? now()->format('Y-m'));
    }

    /**
     * 按域名查询
     */
    public function scopeByDomain($query, $domain)
    {
        return $query->where('domain', $domain);
    }

    /**
     * 按当前月份查询
     */
    public function scopeByCurrentMonth($query, $month)
    {
        return $query->where('current_month', $month);
    }

    /**
     * 按全球排名范围查询
     */
    public function scopeByGlobalRankRange($query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->where('global_rank', '>=', $min);
        }
        if ($max !== null) {
            $query->where('global_rank', '<=', $max);
        }
        return $query;
    }

    /**
     * 按EMV范围查询
     */
    public function scopeByEmvRange($query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->where('current_emv', '>=', $min);
        }
        if ($max !== null) {
            $query->where('current_emv', '<=', $max);
        }
        return $query;
    }

    /**
     * 按类别查询
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * 按类别排名范围查询
     */
    public function scopeByCategoryRankRange($query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->where('category_rank', '>=', $min);
        }
        if ($max !== null) {
            $query->where('category_rank', '<=', $max);
        }
        return $query;
    }

    /**
     * 全球排名排序
     */
    public function scopeOrderByGlobalRank($query, $direction = 'asc')
    {
        return $query->orderBy('global_rank', $direction);
    }

    /**
     * EMV排序
     */
    public function scopeOrderByEmv($query, $direction = 'desc')
    {
        return $query->orderBy('current_emv', $direction);
    }

    /**
     * 更新EMV数据
     */
    public function updateEmvData($month, $emv)
    {
        $trafficData = $this->traffic_data ?? ['data' => []];
        
        // 查找是否已存在该月份的数据
        $found = false;
        for ($i = 0; $i < count($trafficData['data']); $i++) {
            if (isset($trafficData['data'][$i]['month']) && $trafficData['data'][$i]['month'] === $month) {
                $trafficData['data'][$i] = [
                    'month' => $month,
                    'emv' => $emv
                ];
                $found = true;
                break;
            }
        }
        
        // 如果没找到，添加新数据
        if (!$found) {
            $trafficData['data'][] = [
                'month' => $month,
                'emv' => $emv
            ];
        }
        
        // 按月份排序
        usort($trafficData['data'], function($a, $b) {
            return strcmp($a['month'] ?? '', $b['month'] ?? '');
        });
        
        $this->traffic_data = $trafficData;
    }

    /**
     * 获取流量数据中特定月份的EMV
     */
    public function getEmvForMonth($month)
    {
        $trafficData = $this->traffic_data;
        if (!$trafficData || !isset($trafficData['data'])) {
            return null;
        }

        foreach ($trafficData['data'] as $monthData) {
            if (isset($monthData['month']) && $monthData['month'] === $month) {
                return $monthData['emv'] ?? null;
            }
        }

        return null;
    }

    /**
     * 获取格式化的流量来源数据
     */
    public function getFormattedTrafficSources()
    {
        return [
            'Social' => $this->ts_social * 100,
            'Paid Referrals' => $this->ts_paid_referrals * 100,
            'Mail' => $this->ts_mail * 100,
            'Referrals' => $this->ts_referrals * 100,
            'Search' => $this->ts_search * 100,
            'Direct' => $this->ts_direct * 100,
        ];
    }

    /**
     * 获取格式化的主要国家数据
     */
    public function getFormattedCountryShares()
    {
        if (!$this->top_country_shares) {
            return [];
        }

        $formatted = [];
        foreach ($this->top_country_shares as $country) {
            $formatted[] = [
                'country_code' => $country['CountryCode'] ?? '',
                'country_id' => $country['Country'] ?? '',
                'percentage' => ($country['Value'] ?? 0) * 100,
            ];
        }

        return $formatted;
    }
}