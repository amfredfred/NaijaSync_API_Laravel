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
            $table->unsignedBigInteger('owner_id'); // User who owns the post
            $table->string('puid', 100)->unique();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('file_url')->unique();
            $table->string('thumbnail_url')->unique()->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('downloads')->default(0);
            $table->unsignedBigInteger('likes')->default(0);
            $table->unsignedBigInteger('duration')->nullable(); // in seconds
            $table->string('mime_type')->nullable(); 
            $table->string('file_type')->nullable();
            $table->json('source_qualities')->nullable(); // JSON array of quality options
            $table->string('location_view')->nullable(); // Location where it can be viewed
            $table->string('location_download')->nullable(); // Location where it can be downloaded
            $table->json('tags')->nullable(); 
            $table->json('post_genre')->nullable(); 
            $table->json('ratings')->default('null'); // Average rating
            $table->decimal('price', 10, 2)->nullable(); 
            $table->decimal('rewards', 10, 2)->nullable(); 
            $table->boolean('downloadable')->default(true); // Is the file downloadable?
            $table->unsignedBigInteger('playtime')->default(0); // Playtime (for videos)
            $table->enum('post_type', PostTypes::class::getValues())->nullable()->default(PostTypes::STATUS);
            $table->timestamps();

            // Define foreign key relationship with users table
            $table->foreign('owner_id')->references('id')->on('users');
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
