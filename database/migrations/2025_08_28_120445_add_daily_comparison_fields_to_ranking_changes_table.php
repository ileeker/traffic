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
            // 昨日变化字段 (添加在合适的位置，比如在某个字段之后)
            $table->integer('daily_change')->nullable()->after('record_date'); // 根据你的表结构调整位置
            $table->enum('daily_trend', ['up', 'down', 'stable'])->nullable()->after('daily_change');
            
            // 添加相应的索引以优化查询性能
            $table->index(['record_date', 'daily_change'], 'idx_date_daily_change');
            $table->index(['record_date', 'daily_trend'], 'idx_date_daily_trend');
            $table->index('daily_change', 'idx_daily_change');
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
            // 删除索引
            $table->dropIndex('idx_date_daily_change');
            $table->dropIndex('idx_date_daily_trend');
            $table->dropIndex('idx_daily_change');
            
            // 删除字段
            $table->dropColumn([
                'daily_change',
                'daily_trend'
            ]);
        });
    }
};