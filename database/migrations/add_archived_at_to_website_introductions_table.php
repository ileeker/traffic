<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('website_introductions', function (Blueprint $table) {
            // 添加 archived_at 字段
            $table->timestamp('archived_at')
                  ->nullable()
                  ->after('intro')  // 注意：registered_at 字段可能不存在，所以放在 intro 后面
                  ->comment('归档时间，null表示未归档');
            
            // 创建索引 - 这些索引适用于所有MySQL版本
            
            // 1. 单独的 archived_at 索引
            // 用于快速查询归档/未归档记录
            // 查询示例: WHERE archived_at IS NULL 或 WHERE archived_at IS NOT NULL
            $table->index('archived_at', 'idx_archived_at');
            
            // 2. 复合索引：domain + archived_at
            // 用于查询特定域名的归档状态
            // 查询示例: WHERE domain = 'example.com' AND archived_at IS NULL
            $table->index(['domain', 'archived_at'], 'idx_domain_archived');
            
            // 3. 复合索引：archived_at + created_at
            // 用于按时间范围查询归档记录
            // 查询示例: WHERE archived_at BETWEEN '2024-01-01' AND '2024-12-31'
            $table->index(['archived_at', 'created_at'], 'idx_archived_created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('website_introductions', function (Blueprint $table) {
            // 删除索引
            $table->dropIndex('idx_archived_at');
            $table->dropIndex('idx_domain_archived');
            $table->dropIndex('idx_archived_created');
            
            // 删除字段
            $table->dropColumn('archived_at');
        });
    }
};