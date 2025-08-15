<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (! Schema::hasTable('webhook_events')) { // ← 念のため存在チェック
            Schema::create('webhook_events', function (Blueprint $t) {
                $t->id();
                $t->string('source', 50);
                $t->string('event_id', 191)->nullable();
                $t->string('fingerprint', 191)->unique();
                $t->json('payload');
                $t->timestamps();
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('webhook_events');
    }
};
