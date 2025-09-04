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
        Schema::table('similarweb_changes', function (Blueprint $table) {
            // 月度增长率 (相对于1个月前的百分比变化)
            $table->decimal('month_emv_growth_rate', 10, 2)->nullable()
                ->after('month_emv_trend')
                ->comment('EMV月度增长率(%)');
            
            // 季度增长率 (相对于3个月前的百分比变化)
            $table->decimal('quarter_emv_growth_rate', 10, 2)->nullable()
                ->after('quarter_emv_trend')
                ->comment('EMV季度增长率(%)');
            
            // 半年增长率 (相对于6个月前的百分比变化)
            $table->decimal('halfyear_emv_growth_rate', 10, 2)->nullable()
                ->after('halfyear_emv_trend')
                ->comment('EMV半年增长率(%)');
            
            // 年度增长率 (相对于12个月前的百分比变化)
            $table->decimal('year_emv_growth_rate', 10, 2)->nullable()
                ->after('year_emv_trend')
                ->comment('EMV年度增长率(%)');
            
            // 为增长率字段添加索引，便于按增长率排序和筛选
            $table->index(['record_month', 'month_emv_growth_rate'], 'idx_month_growth_rate');
            $table->index(['record_month', 'quarter_emv_growth_rate'], 'idx_quarter_growth_rate');
            $table->index(['record_month', 'halfyear_emv_growth_rate'], 'idx_halfyear_growth_rate');
            $table->index(['record_month', 'year_emv_growth_rate'], 'idx_year_growth_rate');
            
            // 单独的增长率索引用于快速排序
            $table->index('month_emv_growth_rate', 'idx_m_growth_rate');
            $table->index('quarter_emv_growth_rate', 'idx_q_growth_rate');
            $table->index('halfyear_emv_growth_rate', 'idx_h_growth_rate');
            $table->index('year_emv_growth_rate', 'idx_y_growth_rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('similarweb_changes', function (Blueprint $table) {
            // 删除索引
            $table->dropIndex('idx_month_growth_rate');
            $table->dropIndex('idx_quarter_growth_rate');
            $table->dropIndex('idx_halfyear_growth_rate');
            $table->dropIndex('idx_year_growth_rate');
            $table->dropIndex('idx_m_growth_rate');
            $table->dropIndex('idx_q_growth_rate');
            $table->dropIndex('idx_h_growth_rate');
            $table->dropIndex('idx_y_growth_rate');
            
            // 删除字段
            $table->dropColumn('month_emv_growth_rate');
            $table->dropColumn('quarter_emv_growth_rate');
            $table->dropColumn('halfyear_emv_growth_rate');
            $table->dropColumn('year_emv_growth_rate');
        });
    }
};