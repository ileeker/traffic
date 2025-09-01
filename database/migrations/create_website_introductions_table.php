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
            $table->timestamp('archived_at')->nullable()->comment('归档时间，null表示未归档');
            $table->timestamp('registered_at')->nullable()->comment('Domain registration date');
            $table->timestamps();

            // 索引
            // 1. 域名索引（已通过unique创建）
            
            // 2. 复合索引：registered_at + domain
            $table->index(['registered_at', 'domain'], 'idx_registered_domain');
            
            // 3. 复合索引：domain + intro（限制intro长度避免索引过大）
            $table->index(['domain', 'intro(100)'], 'idx_domain_intro');
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