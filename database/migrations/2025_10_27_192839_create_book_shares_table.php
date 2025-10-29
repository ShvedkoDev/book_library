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
        Schema::create('book_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('share_method'); // email, facebook, twitter, whatsapp, clipboard
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('shared_url')->nullable();
            $table->timestamps();

            $table->index(['book_id', 'created_at']);
            $table->index('share_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_shares');
    }
};
