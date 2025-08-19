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
        Schema::create('similarweb_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 255)->unique('domain_unique');
            $table->string('title', 500)->nullable();
            $table->text('description')->nullable();
            $table->integer('global_rank')->nullable();
            $table->string('category', 255)->nullable();
            $table->integer('category_rank')->nullable();
            $table->text('top_keywords')->nullable(); // 存储关键词字符串
            
            // 流量来源分析 (Traffic Sources)
            $table->decimal('ts_social', 8, 6)->nullable()->comment('社交媒体流量占比');
            $table->decimal('ts_paid_referrals', 8, 6)->nullable()->comment('付费引荐流量占比');
            $table->decimal('ts_mail', 8, 6)->nullable()->comment('邮件流量占比');
            $table->decimal('ts_referrals', 8, 6)->nullable()->comment('引荐流量占比');
            $table->decimal('ts_search', 8, 6)->nullable()->comment('搜索流量占比');
            $table->decimal('ts_direct', 8, 6)->nullable()->comment('直接访问流量占比');
            
            // JSON 数据字段
            $table->json('top_country_shares')->nullable()->comment('主要国家流量分布');
            $table->json('engagements_data')->nullable()->comment('用户参与度数据');
            $table->json('traffic_data')->nullable()->comment('月度EMV数据 {"data":[{"month":"2025-05","emv":123456}]}');
            
            // 上个月数据快照（Similarweb最新数据）
            $table->string('current_month', 7)->nullable()->comment('上个月月份 YYYY-MM (Similarweb最新数据)');
            $table->bigInteger('current_emv')->nullable()->comment('上个月EMV');
            $table->decimal('current_bounce_rate', 8, 6)->nullable()->comment('上个月跳出率');
            $table->decimal('current_pages_per_visit', 8, 4)->nullable()->comment('上个月页面/访问');
            $table->decimal('current_time_on_site', 10, 4)->nullable()->comment('上个月网站停留时间(秒)');
            
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();

            // 索引优化
            $table->index('global_rank', 'idx_global_rank');
            $table->index('category', 'idx_category');
            $table->index('category_rank', 'idx_category_rank');
            $table->index('current_month', 'idx_current_month');
            $table->index('current_visits', 'idx_current_visits');
            $table->index('current_emv', 'idx_current_emv');
            $table->index(['category', 'category_rank'], 'idx_category_ranking');
            $table->index(['global_rank', 'current_visits'], 'idx_ranking_visits');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('similarweb_domains');
    }
};