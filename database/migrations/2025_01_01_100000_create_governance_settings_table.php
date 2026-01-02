<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('governance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('overlay_tier')->default('none');
            $table->decimal('expected_rate', 20, 4)->default(0);
            $table->integer('max_oracle_delay')->default(300);
            $table->boolean('use_curve')->default(false);
            $table->integer('uni_fee_tier')->default(3000);
            $table->integer('curve_index_in')->default(1);
            $table->integer('curve_index_out')->default(0);
            $table->string('loan_asset')->nullable();
            $table->string('profit_wallet')->nullable();
            $table->string('fallback_wallet')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('governance_settings');
    }
};