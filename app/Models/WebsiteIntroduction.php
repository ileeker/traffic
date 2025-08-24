<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WebsiteIntroduction extends Model
{
    use HasFactory;

    protected $table = 'website_introductions';

    protected $fillable = [
        'domain',
        'intro',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 与 Domain 的关系（多对一）
     * 一个域名可以有一个介绍
     */
    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain', 'domain');
    }

    /**
     * 与 SimilarwebDomain 的关系（多对一）
     * 一个域名可以有一个介绍
     */
    public function similarwebDomain()
    {
        return $this->belongsTo(SimilarwebDomain::class, 'domain', 'domain');
    }

    /**
     * 按域名查询
     */
    public function scopeByDomain($query, $domain)
    {
        return $query->where('domain', $domain);
    }

    /**
     * 查询有介绍的记录
     */
    public function scopeHasIntro($query)
    {
        return $query->whereNotNull('intro')
                    ->where('intro', '!=', '');
    }

    /**
     * 查询没有介绍的记录
     */
    public function scopeWithoutIntro($query)
    {
        return $query->where(function($q) {
            $q->whereNull('intro')
              ->orWhere('intro', '');
        });
    }

    /**
     * 按介绍内容搜索
     */
    public function scopeSearchIntro($query, $search)
    {
        return $query->where('intro', 'LIKE', "%{$search}%");
    }

    /**
     * 按介绍长度过滤
     */
    public function scopeByIntroLength($query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->whereRaw('CHAR_LENGTH(intro) >= ?', [$min]);
        }
        if ($max !== null) {
            $query->whereRaw('CHAR_LENGTH(intro) <= ?', [$max]);
        }
        return $query;
    }

    /**
     * 获取介绍文本的长度
     */
    public function getIntroLengthAttribute()
    {
        return $this->intro ? mb_strlen($this->intro) : 0;
    }

    /**
     * 获取介绍文本的摘要
     */
    public function getIntroSummaryAttribute($length = 100)
    {
        if (!$this->intro) {
            return '';
        }
        
        return mb_strlen($this->intro) > $length 
            ? mb_substr($this->intro, 0, $length) . '...' 
            : $this->intro;
    }

    /**
     * 检查是否有介绍
     */
    public function hasIntroduction()
    {
        return !empty($this->intro);
    }
}