<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (! Schema::hasTable('contracts')) { // ← 念のため存在チェック
            Schema::create('contracts', function (Blueprint $t) {
                $t->id();
                $t->string('contract_id')->unique();
                $t->json('raw')->nullable();
                $t->timestamps();
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('contracts');
    }
};
