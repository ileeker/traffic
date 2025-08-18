<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $table = 'domains';
    public $timestamps = false;                 // 只有 created_at / last_updated，未使用默认的 updated_at
    protected $fillable = [
        'domain', 'current_ranking', 'ranking_data', 'last_updated', 'created_at'
    ];

    /**
     * 一对一：通过字符串列 domain 关联 RankingChange
     * hasOne(Related::class, 外键, 本地键)
     */
    public function rankingChange()
    {
        return $this->hasOne(RankingChange::class, 'domain', 'domain');
    }
}
