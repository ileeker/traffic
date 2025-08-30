<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 获取现有索引
        $existingIndexes = $this->getExistingIndexes('ranking_changes');
        
        Schema::table('ranking_changes', function (Blueprint $table) use ($existingIndexes) {
            // ===== 第一步：删除冗余索引 =====
            
            // 1. 删除单列索引（被复合索引覆盖）
            $singleColumnIndexesToDrop = [
                'idx_record_date',           // 被所有 (record_date, ...) 复合索引覆盖
                'idx_week_change',            // 很少单独使用
                'idx_month_change',           // 很少单独使用
                'idx_quarter_change',         // 很少单独使用
                'idx_year_change',            // 很少单独使用
                'idx_biweek_change',          // 很少单独使用
                'idx_triweek_change',         // 很少单独使用
                'idx_daily_change',           // 很少单独使用
                'idx_daily_trend',            // 很少单独使用
                'idx_week_trend',             // 很少单独使用
                'idx_month_trend',            // 很少单独使用
            ];
            
            // 2. 删除重复的复合索引
            $duplicateIndexesToDrop = [
                'idx_date_daily_trend_combo',  // 与 idx_date_daily_trend 重复
                'idx_date_week_trend_combo',   // 与 idx_date_week_trend 重复
                'idx_covering_main_fields',    // 覆盖索引太大，维护成本高
            ];
            
            // 执行删除
            $allIndexesToDrop = array_merge($singleColumnIndexesToDrop, $duplicateIndexesToDrop);
            foreach ($allIndexesToDrop as $indexName) {
                if (in_array($indexName, $existingIndexes)) {
                    $table->dropIndex($indexName);
                }
            }
            
            // ===== 第二步：添加必要的索引 =====
            
            // 1. 日期+排名（最常用的查询和排序）
            if (!in_array('idx_date_ranking', $existingIndexes)) {
                $table->index(['record_date', 'current_ranking'], 'idx_date_ranking');
            }
            
            // 2. 域名+日期（查询特定域名的历史记录）
            if (!in_array('idx_domain_date', $existingIndexes)) {
                $table->index(['domain', 'record_date'], 'idx_domain_date');
            }
            
            // 3. 单独的日期索引（如果不存在）
            // 虽然被复合索引覆盖，但单独的日期查询很频繁
            if (!in_array('idx_record_date_single', $existingIndexes)) {
                $table->index('record_date', 'idx_record_date_single');
            }
            
            // 4. 当前排名索引（用于排名筛选）
            if (!in_array('idx_current_ranking', $existingIndexes)) {
                $table->index('current_ranking', 'idx_current_ranking');
            }
        });
        
        // ===== 第三步：验证必要的复合索引存在 =====
        $this->ensureRequiredIndexes();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $existingIndexes = $this->getExistingIndexes('ranking_changes');
        
        Schema::table('ranking_changes', function (Blueprint $table) use ($existingIndexes) {
            // 删除新增的索引
            $newIndexes = [
                'idx_date_ranking',
                'idx_domain_date',
                'idx_record_date_single',
                'idx_current_ranking'
            ];
            
            foreach ($newIndexes as $indexName) {
                if (in_array($indexName, $existingIndexes)) {
                    $table->dropIndex($indexName);
                }
            }
            
            // 恢复删除的索引（可选）
            // 注意：通常不建议在 down() 中恢复所有索引，因为可能已经有了更好的替代
        });
    }
    
    /**
     * 获取表的所有索引名称
     */
    private function getExistingIndexes(string $tableName): array
    {
        $indexes = [];
        
        try {
            $results = DB::select("SHOW INDEX FROM {$tableName}");
            foreach ($results as $index) {
                $indexes[] = $index->Key_name;
            }
            $indexes = array_unique($indexes);
        } catch (\Exception $e) {
            \Log::warning("Could not retrieve indexes for table {$tableName}: " . $e->getMessage());
        }
        
        return $indexes;
    }
    
    /**
     * 确保必要的复合索引存在
     */
    private function ensureRequiredIndexes(): void
    {
        // 必要的复合索引列表
        $requiredIndexes = [
            'idx_date_daily_change' => ['record_date', 'daily_change'],
            'idx_date_week_change' => ['record_date', 'week_change'],
            'idx_date_biweek_change' => ['record_date', 'biweek_change'],
            'idx_date_triweek_change' => ['record_date', 'triweek_change'],
            'idx_date_month_change' => ['record_date', 'month_change'],
            'idx_date_quarter_change' => ['record_date', 'quarter_change'],
            'idx_date_year_change' => ['record_date', 'year_change'],
        ];
        
        $existingIndexes = $this->getExistingIndexes('ranking_changes');
        
        foreach ($requiredIndexes as $indexName => $columns) {
            if (!in_array($indexName, $existingIndexes)) {
                \Log::warning("Required index {$indexName} is missing. Consider adding it.");
            }
        }
    }
};