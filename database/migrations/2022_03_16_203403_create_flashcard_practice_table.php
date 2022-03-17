<?php

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
        Schema::create('flashcard_practice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_id')->constrained()->onDelete('cascade');
            $table->foreignId('flashcard_id')->constrained()->onDelete('cascade');
            $table->string('answer')->nullable();
            $table->enum('status', ['Correct', 'Incorrect', 'Not answered'])->default('Not answered');
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
        Schema::dropIfExists('flashcard_practice');
    }
};
