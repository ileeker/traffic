<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ranking_changes', function (Blueprint $table) {
            $table->bigIncrements('id');                               // BIGINT AUTO_INCREMENT
            $table->string('domain', 255)->unique();                    // 与 domains.domain 一一对应（唯一）

            $table->date('record_date');                                // 记录日期（通常为当天）
            $table->integer('current_ranking')->nullable();             // 当前排名

            $table->integer('week_change')->nullable();                 // 周变化（7天前对比）
            $table->enum('week_trend', ['up','down','stable'])->nullable();

            $table->integer('month_change')->nullable();                // 月变化（30天前对比）
            $table->enum('month_trend', ['up','down','stable'])->nullable();

            $table->integer('quarter_change')->nullable();              // 季度变化（90天前对比）
            $table->enum('quarter_trend', ['up','down','stable'])->nullable();

            $table->integer('year_change')->nullable();                 // 年变化（365天前对比）
            $table->enum('year_trend', ['up','down','stable'])->nullable();

            $table->timestamp('created_at')->useCurrent();              // 创建时间（仅 created_at）

            // 性能相关索引（可选但推荐）
            $table->index('record_date');

            // 外键：字符串外键，指向 domains.domain（需保证同字符集/排序规则）
            $table->foreign('domain')
                  ->references('domain')->on('domains')
                  ->onUpdate('cascade')
                  ->onDelete('cascade'); // 删除 domains 记录时，联动删除
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranking_changes');
    }
};
