<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->increments('id');                                 // INT AUTO_INCREMENT
            $table->string('domain', 255)->unique();                   // 域名（唯一）
            $table->integer('current_ranking')->nullable();            // 当前排名
            $table->json('ranking_data')->nullable();                  // 历史数据(JSON)
            $table->timestamp('last_updated')->nullable();             // 最后更新时间
            $table->timestamp('created_at')->useCurrent();             // 创建时间（仅 created_at）
            // 如需 updated_at：$table->timestamps(); 并删除上两行时间列
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
