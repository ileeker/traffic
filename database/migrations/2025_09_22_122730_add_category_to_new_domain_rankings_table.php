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
        Schema::table('new_domain_rankings', function (Blueprint $table) {
            $table->string('category')->nullable()->after('domain');
            // 或者如果你想要设置默认值
            // $table->string('category')->default('general')->after('domain');
            
            // 如果需要索引（用于查询优化）
            $table->index('category');
            // 可选：如果经常按category筛选可见的记录，还可以添加
            $table->index(['is_visible', 'category'], 'idx_visible_category');
            // 复合索引（如果经常同时查询这两个字段）
            $table->index(['category', 'is_visible'], 'idx_category_visible');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('new_domain_rankings', function (Blueprint $table) {
            $table->dropIndex(['category']); // 如果添加了索引，先删除索引
            $table->dropColumn('category');
        });
    }
};