<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Http\Requests\Training\QuizQuestionRequest;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizQuestionController extends Controller
{
    /** A "quick" quiz stays quick — cap it at 5 questions. */
    private const MAX_QUESTIONS = 5;

    public function store(QuizQuestionRequest $request, Quiz $quiz): RedirectResponse
    {
        if ($quiz->questions()->count() >= self::MAX_QUESTIONS) {
            throw ValidationException::withMessages([
                'prompt' => __('A quiz can have at most :max questions.', ['max' => self::MAX_QUESTIONS]),
            ]);
        }

        $this->saveQuestion($request, $quiz->id, (int) $quiz->questions()->max('order') + 1);

        return back();
    }

    public function update(QuizQuestionRequest $request, QuizQuestion $question): RedirectResponse
    {
        DB::transaction(function () use ($request, $question): void {
            $question->update(['prompt' => $request->validated('prompt')]);
            $question->options()->delete();
            $this->createOptions($question, $request->validated('options'), (int) $request->validated('correct_index'));
        });

        return back();
    }

    public function destroy(QuizQuestion $question): RedirectResponse
    {
        $question->delete();

        return back();
    }

    private function saveQuestion(QuizQuestionRequest $request, int $quizId, int $order): void
    {
        DB::transaction(function () use ($request, $quizId, $order): void {
            $question = QuizQuestion::create([
                'quiz_id' => $quizId,
                'prompt' => $request->validated('prompt'),
                'order' => $order,
            ]);

            $this->createOptions($question, $request->validated('options'), (int) $request->validated('correct_index'));
        });
    }

    /**
     * @param  array<int, string>  $options
     */
    private function createOptions(QuizQuestion $question, array $options, int $correctIndex): void
    {
        foreach (array_values($options) as $index => $text) {
            $question->options()->create([
                'text' => $text,
                'is_correct' => $index === $correctIndex,
                'order' => $index,
            ]);
        }
    }
}
