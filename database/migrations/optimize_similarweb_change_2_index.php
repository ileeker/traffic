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
        Schema::table('similarweb_changes', function (Blueprint $table) {
            // 1. 删除冗余的单列索引
            // idx_record_month 被所有复合索引的左前缀覆盖
            if (Schema::hasIndex('similarweb_changes', 'idx_record_month')) {
                $table->dropIndex('idx_record_month');
            }
            
            // 这些单列索引在有复合索引的情况下很少被使用
            // 除非你经常不带 record_month 条件直接查询这些字段
            if (Schema::hasIndex('similarweb_changes', 'idx_m_emv_change')) {
                $table->dropIndex('idx_m_emv_change');
            }
            if (Schema::hasIndex('similarweb_changes', 'idx_q_emv_change')) {
                $table->dropIndex('idx_q_emv_change');
            }
            if (Schema::hasIndex('similarweb_changes', 'idx_y_emv_change')) {
                $table->dropIndex('idx_y_emv_change');
            }
            if (Schema::hasIndex('similarweb_changes', 'idx_hy_emv_change')) {
                $table->dropIndex('idx_hy_emv_change');
            }
            
            // idx_current_emv 也可以删除，因为有 idx_month_current_emv
            if (Schema::hasIndex('similarweb_changes', 'idx_current_emv')) {
                $table->dropIndex('idx_current_emv');
            }
            
            // 2. 添加缺失的索引
            // 用于 WHERE record_month = ? ORDER BY domain 的查询
            $table->index(['record_month', 'domain'], 'idx_month_domain');
            
            // 3. 可选：如果你经常查询特定域名的历史记录
            // idx_domain_month 已经存在，保留它
            
            // 4. 可选：添加覆盖索引以减少回表
            // 如果内存充足且对性能要求极高，可以考虑添加包含所有查询字段的覆盖索引
            // 但这会占用大量存储空间，一般不推荐
            // $table->index([
            //     'record_month', 
            //     'current_emv',
            //     'month_emv_change',
            //     'quarter_emv_change',
            //     'halfyear_emv_change',
            //     'year_emv_change'
            // ], 'idx_covering_all');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('similarweb_changes', function (Blueprint $table) {
            // 恢复删除的索引
            $table->index('record_month', 'idx_record_month');
            $table->index('month_emv_change', 'idx_m_emv_change');
            $table->index('quarter_emv_change', 'idx_q_emv_change');
            $table->index('year_emv_change', 'idx_y_emv_change');
            $table->index('halfyear_emv_change', 'idx_hy_emv_change');
            $table->index('current_emv', 'idx_current_emv');
            
            // 删除新增的索引
            $table->dropIndex('idx_month_domain');
        });
    }
};