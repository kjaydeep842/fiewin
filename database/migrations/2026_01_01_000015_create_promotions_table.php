<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('banner')->nullable();
            $table->text('description')->nullable();
            $table->string('code')->nullable();
            $table->enum('bonus_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('bonus_amount', 14, 2);
            $table->decimal('min_deposit', 14, 2)->default(0.00);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
