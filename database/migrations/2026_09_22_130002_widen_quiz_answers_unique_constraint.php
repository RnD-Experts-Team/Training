<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A multi-choice question now stores one answer row per selected option,
     * so (attempt, question) alone is no longer unique — widen it to include
     * the option so each selection gets its own row.
     */
    public function up(): void
    {
        Schema::table('quiz_answers', function (Blueprint $table): void {
            $table->dropUnique(['quiz_attempt_id', 'quiz_question_id']);
            $table->unique(
                ['quiz_attempt_id', 'quiz_question_id', 'quiz_question_option_id'],
                'quiz_answers_attempt_question_option_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('quiz_answers', function (Blueprint $table): void {
            $table->dropUnique('quiz_answers_attempt_question_option_unique');
            $table->unique(['quiz_attempt_id', 'quiz_question_id']);
        });
    }
};
