<?php

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
    * Run the migrations.
    */

    public function up() {
        Schema::create( 'transactions', function ( Blueprint $table )
         {
            $table->id();
            // $table->unsignedBigInteger( 'user_id' );
            $table->enum( 'transaction_type', TransactionType::class::getValues())->nullable()->default(TransactionType::POINTS_EARNED );
            $table->enum('status', TransactionStatus::class::getValues())->default(TransactionStatus::Pending); 
            $table->unsignedBigInteger( 'from_account_id' )->nullable();
            $table->unsignedBigInteger( 'to_account_id' )->nullable();
            $table->decimal( 'amount', 10, 2 );
            $table->text( 'description' )->nullable();
            $table->string( 'recipient_name' )->nullable();
            $table->string('transaction_reference')->unique();
        $table->string('bank_name')->nullable();  
        $table->string('bank_account_number')->nullable(); 
        $table->timestamps();
        // $table->foreign('user_id')->references('id')->on('users');
        $table->foreign('from_account_id')->references('id')->on('accounts');
        $table->foreign('to_account_id')->references('id')->on('accounts');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions' );
        }
    }
    ;
