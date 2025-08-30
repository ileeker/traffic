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
        // 获取现有索引列表
        $existingIndexes = $this->getExistingIndexes('similarweb_changes');
        
        Schema::table('similarweb_changes', function (Blueprint $table) use ($existingIndexes) {
            // 删除冗余的单列索引（如果存在）
            $indexesToDrop = [
                'idx_record_month',      // 被复合索引覆盖
                'idx_m_emv_change',       // 单列索引，很少单独使用
                'idx_q_emv_change',       // 单列索引，很少单独使用
                'idx_y_emv_change',       // 单列索引，很少单独使用
                'idx_hy_emv_change',      // 单列索引，很少单独使用
                'idx_current_emv'         // 被 idx_month_current_emv 覆盖
            ];
            
            foreach ($indexesToDrop as $indexName) {
                if (in_array($indexName, $existingIndexes)) {
                    $table->dropIndex($indexName);
                }
            }
            
            // 添加缺失的索引
            if (!in_array('idx_month_domain', $existingIndexes)) {
                $table->index(['record_month', 'domain'], 'idx_month_domain');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $existingIndexes = $this->getExistingIndexes('similarweb_changes');
        
        Schema::table('similarweb_changes', function (Blueprint $table) use ($existingIndexes) {
            // 删除新增的索引
            if (in_array('idx_month_domain', $existingIndexes)) {
                $table->dropIndex('idx_month_domain');
            }
            
            // 恢复删除的索引
            $indexesToRestore = [
                'idx_record_month' => ['record_month'],
                'idx_m_emv_change' => ['month_emv_change'],
                'idx_q_emv_change' => ['quarter_emv_change'],
                'idx_y_emv_change' => ['year_emv_change'],
                'idx_hy_emv_change' => ['halfyear_emv_change'],
                'idx_current_emv' => ['current_emv']
            ];
            
            foreach ($indexesToRestore as $indexName => $columns) {
                if (!in_array($indexName, $existingIndexes)) {
                    $table->index($columns, $indexName);
                }
            }
        });
    }
    
    /**
     * 获取表的所有索引名称
     *
     * @param string $tableName
     * @return array
     */
    private function getExistingIndexes(string $tableName): array
    {
        $indexes = [];
        
        try {
            $results = DB::select("SHOW INDEX FROM {$tableName}");
            foreach ($results as $index) {
                $indexes[] = $index->Key_name;
            }
            // 去重
            $indexes = array_unique($indexes);
        } catch (\Exception $e) {
            // 如果查询失败，返回空数组
            \Log::warning("Could not retrieve indexes for table {$tableName}: " . $e->getMessage());
        }
        
        return $indexes;
    }
};