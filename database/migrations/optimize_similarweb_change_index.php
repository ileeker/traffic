<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('similarweb_changes', function (Blueprint $table) {
            // 添加 current_emv 索引用于排序
            $table->index('current_emv', 'idx_current_emv');
            
            // 复合索引优化：记录月份 + EMV值 用于筛选和排序
            $table->index(['record_month', 'current_emv'], 'idx_month_current_emv');
            
            // 半年变化的索引（补充缺失的）
            $table->index(['record_month', 'halfyear_emv_change'], 'idx_halfyear_emv_change');
            $table->index(['record_month', 'halfyear_emv_trend'], 'idx_halfyear_emv_trend');
            $table->index('halfyear_emv_change', 'idx_hy_emv_change');
            
            // 域名和记录月份的复合索引
            $table->index(['domain', 'record_month'], 'idx_domain_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('similarweb_changes', function (Blueprint $table) {
            $table->dropIndex('idx_current_emv');
            $table->dropIndex('idx_month_current_emv');
            $table->dropIndex('idx_halfyear_emv_change');
            $table->dropIndex('idx_halfyear_emv_trend');
            $table->dropIndex('idx_hy_emv_change');
            $table->dropIndex('idx_domain_month');
        });
    }
};