<?php

use App\Models\Flat;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->date('payment_date');
            $table->double('total_amount');
            $table->double('amount_paid');
            $table->double('amount_due');

            $table->foreignIdFor(Flat::class)->constrained()->cascadeOnDelete("cascade");
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
