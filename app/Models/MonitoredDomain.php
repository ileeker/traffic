<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MonitoredDomain extends Model
{
    use HasFactory;

    protected $table = 'monitored_domains';

    protected $fillable = [
        'domain',
        'description',
        'registered_at',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'registered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 与 Domain 的一对一关系
     */
    public function domainData()
    {
        return $this->hasOne(Domain::class, 'domain', 'domain');
    }

    /**
     * 与 SimilarwebDomain 的一对一关系
     */
    public function similarwebData()
    {
        return $this->hasOne(SimilarwebDomain::class, 'domain', 'domain');
    }
}