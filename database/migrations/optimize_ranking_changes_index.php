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
        Schema::table('ranking_changes', function (Blueprint $table) {
            // 为列表页面常用的排序和筛选添加复合索引
            
            // 1. 域名+日期复合索引（用于按域名查询特定日期范围）
            $table->index(['domain', 'record_date'], 'idx_domain_date');
            
            // 2. 当前排名索引（用于排名范围筛选和排序）
            $table->index('current_ranking', 'idx_current_ranking');
            
            // 3. 趋势字段索引（用于筛选特定趋势）
            $table->index('daily_trend', 'idx_daily_trend');
            $table->index('week_trend', 'idx_week_trend');
            $table->index('month_trend', 'idx_month_trend');
            
            // 4. 复合索引优化：日期+趋势（最常用的组合查询）
            $table->index(['record_date', 'daily_trend'], 'idx_date_daily_trend_combo');
            $table->index(['record_date', 'week_trend'], 'idx_date_week_trend_combo');
            
            // 5. 复合索引优化：日期+当前排名（用于特定日期的排名排序）
            $table->index(['record_date', 'current_ranking'], 'idx_date_ranking');
            
            // 6. 为大变化值创建条件索引（MySQL 8.0+支持，如果版本不支持可以注释掉）
            // $table->index(['record_date', 'daily_change'], 'idx_date_big_daily_change')
            //       ->where('daily_change', '>', 10);
            
            // 7. 覆盖索引：包含最常用的字段组合
            // 注意：这个索引会比较大，但可以避免回表查询
            $table->index([
                'record_date', 
                'domain', 
                'current_ranking', 
                'daily_change', 
                'week_change'
            ], 'idx_covering_main_fields');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ranking_changes', function (Blueprint $table) {
            // 删除添加的索引
            $table->dropIndex('idx_domain_date');
            $table->dropIndex('idx_current_ranking');
            $table->dropIndex('idx_daily_trend');
            $table->dropIndex('idx_week_trend');
            $table->dropIndex('idx_month_trend');
            $table->dropIndex('idx_date_daily_trend_combo');
            $table->dropIndex('idx_date_week_trend_combo');
            $table->dropIndex('idx_date_ranking');
            $table->dropIndex('idx_covering_main_fields');
        });
    }
};