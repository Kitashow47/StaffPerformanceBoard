<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (! Schema::hasTable('staff_daily_totals')) {
            Schema::create('staff_daily_totals', function (Blueprint $t) {
                $t->id();
                $t->date('date')->index();                  // JSTの日付で集計
                $t->string('staff_code', 191)->index();     // スタッフ識別
                $t->bigInteger('gross_amount')->default(0); // 売上合計
                $t->timestamps();
                $t->unique(['date', 'staff_code']);         // 1日×スタッフで一意
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('staff_daily_totals');
    }
};
