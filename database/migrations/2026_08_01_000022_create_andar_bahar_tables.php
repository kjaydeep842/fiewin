<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('andar_bahar_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('round_seconds')->default(60);
            $table->integer('betting_seconds')->default(45);
            $table->integer('animation_seconds')->default(15);
            $table->decimal('min_bet', 12, 2)->default(10.00);
            $table->decimal('max_bet', 12, 2)->default(50000.00);
            $table->decimal('rtp_percentage', 5, 2)->default(96.00);
            $table->decimal('andar_odds', 8, 2)->default(2.00);
            $table->decimal('bahar_odds', 8, 2)->default(2.00);
            $table->decimal('tie_odds', 8, 2)->default(9.00);
            $table->boolean('is_active')->default(true);
            $table->string('manual_override_winner')->nullable(); // 'andar', 'bahar', 'tie'
            $table->timestamps();
        });

        Schema::create('andar_bahar_rounds', function (Blueprint $table) {
            $table->id();
            $table->string('period_number')->unique();
            $table->string('open_card');
            $table->string('winner')->nullable(); // 'andar', 'bahar', 'tie'
            $table->json('deal_sequence')->nullable();
            $table->string('matching_card')->nullable();
            $table->integer('deal_count')->default(0);
            $table->string('status')->default('betting'); // betting, dealing, settled
            $table->boolean('manual_override')->default(false);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('andar_bahar_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('andar_bahar_round_id')->nullable()->constrained('andar_bahar_rounds')->onDelete('cascade');
            $table->string('period_number');
            $table->string('bet_option'); // andar, bahar, tie
            $table->decimal('bet_amount', 12, 2);
            $table->decimal('win_amount', 12, 2)->default(0.00);
            $table->decimal('multiplier', 8, 2)->default(0.00);
            $table->string('status')->default('pending'); // pending, won, lost
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });

        Schema::create('andar_bahar_results', function (Blueprint $table) {
            $table->id();
            $table->string('period_number')->unique();
            $table->string('open_card');
            $table->string('winner'); // andar, bahar, tie
            $table->integer('deal_count')->default(0);
            $table->string('winning_card');
            $table->json('result_data')->nullable();
            $table->string('provably_fair_hash')->nullable();
            $table->string('seed')->nullable();
            $table->boolean('manual_override')->default(false);
            $table->timestamp('settled_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('andar_bahar_results');
        Schema::dropIfExists('andar_bahar_bets');
        Schema::dropIfExists('andar_bahar_rounds');
        Schema::dropIfExists('andar_bahar_settings');
    }
};
