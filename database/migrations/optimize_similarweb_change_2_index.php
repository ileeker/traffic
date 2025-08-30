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
        Schema::table('similarweb_changes', function (Blueprint $table) {
            // 步骤1: 清理冗余索引
            $this->dropIndexIfExists($table, 'idx_record_month');
            $this->dropIndexIfExists($table, 'idx_m_emv_change');
            $this->dropIndexIfExists($table, 'idx_q_emv_change');
            $this->dropIndexIfExists($table, 'idx_y_emv_change');
            $this->dropIndexIfExists($table, 'idx_hy_emv_change');
            $this->dropIndexIfExists($table, 'idx_current_emv');
            
            // 步骤2: 确保必要的复合索引存在
            // 用于 WHERE record_month = ? ORDER BY domain
            if (!$this->indexExists('similarweb_changes', 'idx_month_domain')) {
                $table->index(['record_month', 'domain'], 'idx_month_domain');
            }
            
            // 步骤3: 为优化后的查询添加特定索引
            // 这些索引支持 WHERE record_month = ? AND field >= ? 或 field <= ? 的查询
            
            // 对于正值和负值的分别查询优化
            // 创建部分索引（如果MySQL 8.0.13+支持）
            if ($this->supportsFunctionalIndex()) {
                // 为正值创建索引
                DB::statement('CREATE INDEX idx_month_positive_month_change ON similarweb_changes (record_month, month_emv_change) WHERE month_emv_change > 0');
                DB::statement('CREATE INDEX idx_month_negative_month_change ON similarweb_changes (record_month, month_emv_change) WHERE month_emv_change < 0');
                
                DB::statement('CREATE INDEX idx_month_positive_quarter_change ON similarweb_changes (record_month, quarter_emv_change) WHERE quarter_emv_change > 0');
                DB::statement('CREATE INDEX idx_month_negative_quarter_change ON similarweb_changes (record_month, quarter_emv_change) WHERE quarter_emv_change < 0');
            }
        });
        
        // 步骤4: 更新表统计信息
        DB::statement('ANALYZE TABLE similarweb_changes');
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
            $this->dropIndexIfExists($table, 'idx_month_domain');
        });
        
        // 删除部分索引（如果存在）
        if ($this->supportsFunctionalIndex()) {
            DB::statement('DROP INDEX IF EXISTS idx_month_positive_month_change ON similarweb_changes');
            DB::statement('DROP INDEX IF EXISTS idx_month_negative_month_change ON similarweb_changes');
            DB::statement('DROP INDEX IF EXISTS idx_month_positive_quarter_change ON similarweb_changes');
            DB::statement('DROP INDEX IF EXISTS idx_month_negative_quarter_change ON similarweb_changes');
        }
    }
    
    /**
     * 检查索引是否存在
     */
    private function indexExists($table, $indexName): bool
    {
        $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return !empty($result);
    }
    
    /**
     * 如果索引存在则删除
     */
    private function dropIndexIfExists($table, $indexName): void
    {
        if ($this->indexExists('similarweb_changes', $indexName)) {
            $table->dropIndex($indexName);
        }
    }
    
    /**
     * 检查是否支持函数索引（MySQL 8.0.13+）
     */
    private function supportsFunctionalIndex(): bool
    {
        $version = DB::selectOne("SELECT VERSION() as version")->version;
        // 简单的版本检查，实际可能需要更复杂的逻辑
        return version_compare($version, '8.0.13', '>=');
    }
};