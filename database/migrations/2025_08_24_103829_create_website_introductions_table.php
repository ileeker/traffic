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
        Schema::create('website_introductions', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 255)->unique('domain_unique');
            $table->text('intro')->nullable()->comment('网站介绍内容');
            $table->timestamp('registered_at')->nullable()->comment('Domain registration date');
            $table->timestamps();

            // 索引优化
            // 1. 域名索引（已通过unique创建）
            
            // 2. 介绍内容全文搜索索引（适用于 MySQL 5.7+ 和 InnoDB）
            // 如果您的 MySQL 版本支持，这将大大提升文本搜索性能
            try {
                $table->fullText('intro', 'idx_intro_fulltext');
            } catch (Exception $e) {
                // 如果不支持全文索引，创建普通索引
                $table->index('intro(100)', 'idx_intro_prefix');
            }
            
            // 3. 介绍内容长度索引（用于按长度筛选）
            $table->index([
                DB::raw('(CHAR_LENGTH(intro))')
            ], 'idx_intro_length');

            // 外键约束选项（根据您的需求选择一个或不使用）
            
            // 选项1：与 domains 表建立外键关系
            // 如果您希望介绍记录与 domains 表保持一致性
            // $table->foreign('domain')
            //       ->references('domain')
            //       ->on('domains')
            //       ->onUpdate('cascade')
            //       ->onDelete('cascade');
            
            // 选项2：与 similarweb_domains 表建立外键关系  
            // 如果您希望介绍记录与 similarweb_domains 表保持一致性
            // $table->foreign('domain')
            //       ->references('domain')
            //       ->on('similarweb_domains')
            //       ->onUpdate('cascade')
            //       ->onDelete('cascade');
            
            // 注意：如果同一个域名既在 domains 表又在 similarweb_domains 表中，
            // 建议不设置外键约束，或者只选择一个主表设置外键约束
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('website_introductions');
    }
};