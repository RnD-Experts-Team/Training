<?php

namespace App\Http\Controllers;

use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The employee-facing quiz — reached via an unguessable token link, no
 * account or login involved. Deliberately outside the `auth` middleware.
 */
class PublicQuizController extends Controller
{
    public function show(string $token): Response
    {
        $attempt = QuizAttempt::where('token', $token)->firstOrFail();
        $attempt->load('quiz.questions.options', 'trainee:id,name');

        return Inertia::render('quiz/show', [
            'token' => $token,
            'traineeName' => $attempt->trainee->name,
            'sectionTitle' => $attempt->quiz->section?->title,
            'completed' => $attempt->isCompleted(),
            'questions' => $attempt->isCompleted()
                ? []
                : $attempt->quiz->questions->map(fn ($question): array => [
                    'id' => $question->id,
                    'prompt' => $question->prompt,
                    'options' => $question->options->map(fn ($option): array => [
                        'id' => $option->id,
                        'text' => $option->text,
                    ])->values(),
                ])->values(),
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $attempt = QuizAttempt::where('token', $token)->whereNull('completed_at')->firstOrFail();
        $attempt->load('quiz.questions.options');

        $rules = [];
        foreach ($attempt->quiz->questions as $question) {
            $rules["answers.{$question->id}"] = ['required', Rule::in($question->options->pluck('id'))];
        }

        $data = $request->validate($rules);

        $correct = 0;
        $total = $attempt->quiz->questions->count();

        DB::transaction(function () use ($attempt, $data, &$correct): void {
            foreach ($attempt->quiz->questions as $question) {
                $option = $question->options->firstWhere('id', $data['answers'][$question->id]);

                if ($option->is_correct) {
                    $correct++;
                }

                QuizAnswer::create([
                    'quiz_attempt_id' => $attempt->id,
                    'quiz_question_id' => $question->id,
                    'quiz_question_option_id' => $option->id,
                ]);
            }
        });

        $attempt->update([
            'completed_at' => now(),
            'score' => $total > 0 ? (int) round($correct / $total * 100) : 0,
        ]);

        return to_route('quiz.show', $token);
    }
}
