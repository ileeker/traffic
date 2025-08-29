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

}