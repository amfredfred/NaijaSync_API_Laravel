<?php

use App\Enums\PostTypes;
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
         Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('puid', 100)->unique();
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->string('file_url')->unique();
            $table->string('thumbnail_url')->unique()->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('downloads')->default(0);
            $table->unsignedBigInteger('likes')->default(0);
            $table->unsignedBigInteger('duration')->nullable();
            $table->string('mime_type')->nullable(); 
            $table->string('file_type')->nullable();
            $table->json('source_qualities')->nullable(); 
            $table->string('location_view')->nullable(); 
            $table->string('location_download')->nullable(); 
            $table->json('tags')->nullable(); 
            $table->json('post_genre')->nullable(); 
            $table->json('ratings')->default('null');
            $table->decimal('price', 10, 2)->nullable(); 
            $table->decimal('rewards', 10, 2)->nullable(); 
            $table->boolean('downloadable')->default(true); 

            $table->string('artist')->nullable();
            $table->string('album')->nullable();
            $table->json('genre')->nullable();
            $table->integer('year')->nullable();

            $table->unsignedBigInteger('playtime')->default(0); 
            $table->enum('post_type', PostTypes::class::getValues())->nullable()->default(PostTypes::STATUS);
            $table->timestamps();
            $table->foreign('account_id')->references('id')->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
