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
            // 添加 archived 字段
            $table->timestamp('archived_at')
                  ->nullable()
                  ->after('registered_at')
                  ->comment('归档时间，null表示未归档');
            
            // 索引设置
            // 1. 单独的 archived_at 索引，用于快速查询归档/未归档记录
            $table->index('archived_at', 'idx_archived_at');
            
            // 2. 复合索引：domain + archived_at
            // 用于查询特定域名的归档状态
            $table->index(['domain', 'archived_at'], 'idx_domain_archived');
            
            // 3. 复合索引：archived_at + created_at
            // 用于按时间范围查询归档记录
            $table->index(['archived_at', 'created_at'], 'idx_archived_created');
            
            // 4. 部分索引（MySQL 8.0+）或条件索引
            // 如果你的 MySQL 版本支持，可以创建只包含未归档记录的索引
            // 这对于经常查询活跃（未归档）记录的场景非常有用
            if (DB::connection()->getDriverName() === 'mysql') {
                $version = DB::select('SELECT VERSION() as version')[0]->version;
                if (version_compare($version, '8.0.0', '>=')) {
                    // MySQL 8.0+ 支持函数索引
                    DB::statement('CREATE INDEX idx_active_domains ON website_introductions ((archived_at IS NULL), domain)');
                }
            }
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
            
            // 尝试删除条件索引（如果存在）
            try {
                DB::statement('DROP INDEX idx_active_domains ON website_introductions');
            } catch (\Exception $e) {
                // 索引可能不存在，忽略错误
            }
            
            // 删除字段
            $table->dropColumn('archived_at');
        });
    }
};