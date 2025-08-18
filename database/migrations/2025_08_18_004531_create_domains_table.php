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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 255)->unique('domain_unique');
            $table->date('record_date');
            $table->integer('current_ranking');
            $table->json('ranking_data')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();

            // 索引优化
            // 1. 按域名查询的索引（已通过unique创建）
            // 2. 按record_date排名查询的复合索引
            $table->index(['record_date', 'current_ranking'], 'idx_record_date_ranking');
            // 3. 单独的record_date索引（用于日期范围查询）
            $table->index('record_date', 'idx_record_date');
            // 4. 单独的current_ranking索引（用于排名范围查询）
            $table->index('current_ranking', 'idx_current_ranking');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('domains');
    }
};