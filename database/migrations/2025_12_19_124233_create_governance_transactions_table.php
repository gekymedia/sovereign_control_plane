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
        Schema::create('governance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governance_setting_id')->constrained()->onDelete('cascade');
            $table->string('transaction_hash')->nullable()->unique();
            $table->enum('status', ['pending', 'processing', 'success', 'failed'])->default('pending');
            $table->bigInteger('block_number')->nullable();
            $table->bigInteger('gas_used')->nullable();
            $table->text('error_message')->nullable();
            $table->string('applied_by')->nullable(); // Wallet address that applied the settings
            $table->timestamps();

            $table->index('status');
            $table->index('governance_setting_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('governance_transactions');
    }
};
