<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('vote'); // 'like' or 'dislike'
            $table->string('voter_ip', 45);
            $table->timestamps();
            $table->unique(['question_id', 'voter_ip']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_votes');
    }
};
