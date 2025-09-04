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
        'month_emv_growth_rate',
        
        // 季度变化
        'quarter_emv_change',
        'quarter_emv_trend',
        'quarter_emv_growth_rate',
        
        // 半年变化
        'halfyear_emv_change',
        'halfyear_emv_trend',
        'halfyear_emv_growth_rate',
        
        // 年度变化
        'year_emv_change',
        'year_emv_trend',
        'year_emv_growth_rate',
    ];

    protected $casts = [
        'current_emv' => 'integer',
        'month_emv_change' => 'integer',
        'quarter_emv_change' => 'integer',
        'halfyear_emv_change' => 'integer',
        'year_emv_change' => 'integer',
        'month_emv_growth_rate' => 'float',
        'quarter_emv_growth_rate' => 'float',
        'halfyear_emv_growth_rate' => 'float',
        'year_emv_growth_rate' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 与 SimilarwebDomain 的关系
     */
    public function similarwebDomain()
    {
        return $this->hasOne(SimilarwebDomain::class, 'domain', 'domain');
    }

    /**
     * 与 WebsiteIntroduction 的一对一关系
     */
    public function websiteIntroduction()
    {
        return $this->hasOne(WebsiteIntroduction::class, 'domain', 'domain');
    }

}