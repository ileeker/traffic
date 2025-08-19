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
            // 2周变化字段 (添加在 week_trend 之后)
            $table->integer('biweek_change')->nullable()->after('week_trend');
            $table->enum('biweek_trend', ['up', 'down', 'stable'])->nullable()->after('biweek_change');
            
            // 3周变化字段 (添加在 biweek_trend 之后)
            $table->integer('triweek_change')->nullable()->after('biweek_trend');
            $table->enum('triweek_trend', ['up', 'down', 'stable'])->nullable()->after('triweek_change');
            
            // 添加相应的索引以优化查询性能
            $table->index(['record_date', 'biweek_change'], 'idx_date_biweek_change');
            $table->index(['record_date', 'biweek_trend'], 'idx_date_biweek_trend');
            $table->index(['record_date', 'triweek_change'], 'idx_date_triweek_change');
            $table->index(['record_date', 'triweek_trend'], 'idx_date_triweek_trend');
            
            // 单独的变化值索引（用于排序和范围查询）
            $table->index('biweek_change', 'idx_biweek_change');
            $table->index('triweek_change', 'idx_triweek_change');
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
            $table->dropIndex('idx_date_biweek_change');
            $table->dropIndex('idx_date_biweek_trend');
            $table->dropIndex('idx_date_triweek_change');
            $table->dropIndex('idx_date_triweek_trend');
            $table->dropIndex('idx_biweek_change');
            $table->dropIndex('idx_triweek_change');
            
            // 删除字段
            $table->dropColumn([
                'biweek_change',
                'biweek_trend',
                'triweek_change',
                'triweek_trend'
            ]);
        });
    }
};