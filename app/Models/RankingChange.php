<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RankingChange extends Model
{
    protected $table = 'ranking_changes';
    public $timestamps = false;         // 只维护 created_at
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'domain', 'record_date', 'current_ranking',
        'week_change', 'week_trend',
        'month_change', 'month_trend',
        'quarter_change', 'quarter_trend',
        'year_change', 'year_trend',
        'created_at',
    ];

    /**
     * 反向关联：属于某个 Domain（字符串键对字符串键）
     * belongsTo(Parent::class, 外键, 关联模型的本地键)
     */
    public function domainModel()
    {
        return $this->belongsTo(Domain::class, 'domain', 'domain');
    }
}
