<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('contracts', function (Blueprint $t) {
            $t->id();
            $t->string('contract_id')->unique();
            $t->json('raw')->nullable(); // 受信ペイロードの原文
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('contracts'); }
};
