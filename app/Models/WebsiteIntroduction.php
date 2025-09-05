<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class WebsiteIntroduction extends Model
{
    use HasFactory;

    protected $table = 'website_introductions';

    protected $fillable = [
        'domain',
        'intro',
        'registered_at',
        'archived_at',  // 新增字段
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'registered_at' => 'datetime',
        'archived_at' => 'datetime',  // 新增字段的类型转换
    ];

    /**
     * 与 SimilarwebChange 的一对一关系
     */
    public function similarwebChange()
    {
        return $this->hasOne(SimilarwebChange::class, 'domain', 'domain');
    }

}