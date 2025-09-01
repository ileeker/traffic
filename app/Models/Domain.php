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
     * 与 WebsiteIntroduction 的一对一关系
     */
    public function websiteIntroduction()
    {
        return $this->hasOne(WebsiteIntroduction::class, 'domain', 'domain');
    }

}