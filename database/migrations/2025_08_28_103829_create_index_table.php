<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('similarweb_domains', function (Blueprint $table) {
            // 主要查询索引：按月份和访问量排序
            $table->index(['current_month', 'current_emv'], 'idx_month_emv');
            
            // 按月份和分类排序
            $table->index(['current_month', 'category'], 'idx_month_category');
            
            // 按月份和直接流量排序
            $table->index(['current_month', 'ts_direct'], 'idx_month_direct');
            
            // 按月份和搜索流量排序  
            $table->index(['current_month', 'ts_search'], 'idx_month_search');
            
            // 按月份和推荐流量排序
            $table->index(['current_month', 'ts_referrals'], 'idx_month_referrals');
            
            // 按月份和社交流量排序
            $table->index(['current_month', 'ts_social'], 'idx_month_social');
            
            // 按月份和付费流量排序
            $table->index(['current_month', 'ts_paid_referrals'], 'idx_month_paid');
            
            // 按月份和邮件流量排序
            $table->index(['current_month', 'ts_mail'], 'idx_month_mail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('similarweb_domains', function (Blueprint $table) {
            $table->dropIndex('idx_month_emv');
            $table->dropIndex('idx_month_category');
            $table->dropIndex('idx_month_direct');
            $table->dropIndex('idx_month_search');
            $table->dropIndex('idx_month_referrals');
            $table->dropIndex('idx_month_social');
            $table->dropIndex('idx_month_paid');
            $table->dropIndex('idx_month_mail');
        });
    }
};