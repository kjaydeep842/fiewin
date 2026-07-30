<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('game_categories')->onDelete('set null');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique(); // fast_parity, mines, crash, jet, dice, spin_wheel, etc.
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->decimal('min_entry_fee', 12, 2)->default(10.00);
            $table->decimal('max_entry_fee', 12, 2)->default(100000.00);
            $table->decimal('win_multiplier', 8, 2)->default(2.00);
            $table->decimal('rtp_percentage', 5, 2)->default(95.00); // Return To Player %
            $table->boolean('is_active')->default(true);
            $table->text('rules')->nullable();
            $table->text('instruction')->nullable();
            $table->json('config')->nullable(); // Timer settings, multiplier levels, grid settings
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
