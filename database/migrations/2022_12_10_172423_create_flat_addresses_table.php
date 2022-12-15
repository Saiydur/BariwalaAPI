<?php

use App\Models\Flat;
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
        Schema::create('flat_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('street');
            $table->string('road');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');

            $table->foreignIdFor(Flat::class)->constrained()->cascadeOnDelete("cascade");
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
        Schema::dropIfExists('flat_addresses');
    }
};
