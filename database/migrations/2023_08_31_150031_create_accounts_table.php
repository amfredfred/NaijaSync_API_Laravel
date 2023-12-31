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
       Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique(); 
            $table->integer('points')->default(0);
            $table->decimal('bank_account_balance', 10, 2)->default(0.00);
            $table->json('profile_pics')->nullable();
            $table->longText('bio')->nullable();
            $table->string('gender', 100)->nullable();
            $table->json('profile_cover_pics')->nullable();
            $table->string('username', 100)->unique();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
