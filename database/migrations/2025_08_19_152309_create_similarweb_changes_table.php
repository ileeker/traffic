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
        Schema::create('similarweb_changes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('domain', 255)->unique('domain_unique');
            $table->string('record_month', 7)->comment('记录月份 YYYY-MM');
            
            // 当前数据（上个月的最新数据）
            $table->bigInteger('current_emv')->nullable()->comment('上个月EMV');
            
            // 月度变化 (1个月前对比)
            $table->bigInteger('month_emv_change')->nullable()->comment('EMV月度变化');
            $table->enum('month_emv_trend', ['up', 'down', 'stable'])->nullable();
            
            // 季度变化 (3个月前对比)
            $table->bigInteger('quarter_emv_change')->nullable()->comment('EMV季度变化');
            $table->enum('quarter_emv_trend', ['up', 'down', 'stable'])->nullable();
            
            // 半年变化 (6个月前对比)
            $table->bigInteger('halfyear_emv_change')->nullable()->comment('EMV半年变化');
            $table->enum('halfyear_emv_trend', ['up', 'down', 'stable'])->nullable();
            
            // 年度变化 (12个月前对比)
            $table->bigInteger('year_emv_change')->nullable()->comment('EMV年度变化');
            $table->enum('year_emv_trend', ['up', 'down', 'stable'])->nullable();
            
            $table->timestamps();

            // 索引优化 - 针对月度数据的查询优化
            $table->index('record_month', 'idx_record_month');
            
            // EMV变化索引
            $table->index(['record_month', 'month_emv_change'], 'idx_month_emv_change');
            $table->index(['record_month', 'month_emv_trend'], 'idx_month_emv_trend');
            $table->index(['record_month', 'quarter_emv_change'], 'idx_quarter_emv_change');
            $table->index(['record_month', 'year_emv_change'], 'idx_year_emv_change');
            
            // 单独的变化值索引用于排序
            $table->index('month_emv_change', 'idx_m_emv_change');
            $table->index('quarter_emv_change', 'idx_q_emv_change');
            $table->index('year_emv_change', 'idx_y_emv_change');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('similarweb_changes');
    }
};