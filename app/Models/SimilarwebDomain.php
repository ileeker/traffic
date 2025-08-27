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

}