<?php

namespace Tests\Feature\Training;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Section;
use App\Models\Store;
use App\Models\Trainee;
use App\Models\User;
use App\Services\Training\TraineeProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    // --- Authoring -----------------------------------------------------

    public function test_super_admin_can_add_a_quiz_to_a_section(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $section = Section::factory()->create();

        $this->actingAs($admin)
            ->post(route('training.quizzes.store', $section))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('quizzes', ['section_id' => $section->id]);
    }

    public function test_manager_cannot_manage_quiz_content(): void
    {
        $manager = User::factory()->manager()->create();
        $section = Section::factory()->create();
        $quiz = Quiz::factory()->create(['section_id' => $section->id]);

        $this->actingAs($manager)->post(route('training.quizzes.store', $section))->assertForbidden();
        $this->actingAs($manager)->post(route('training.quiz-questions.store', $quiz), [
            'prompt' => 'x?',
            'options' => ['a', 'b', 'c', 'd'],
            'correct_index' => 0,
        ])->assertForbidden();
    }

    public function test_a_quiz_question_needs_exactly_four_options_and_one_correct_index(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $quiz = Quiz::factory()->create();

        $this->actingAs($admin)->post(route('training.quiz-questions.store', $quiz), [
            'prompt' => 'What comes first?',
            'options' => ['Wash hands', 'Wear gloves', 'Preheat oven'],
            'correct_index' => 0,
        ])->assertSessionHasErrors('options');

        $this->actingAs($admin)->post(route('training.quiz-questions.store', $quiz), [
            'prompt' => 'What comes first?',
            'options' => ['Wash hands', 'Wear gloves', 'Preheat oven', 'Clock in'],
            'correct_index' => 0,
        ])->assertSessionHasNoErrors();

        $question = QuizQuestion::firstWhere('prompt', 'What comes first?');
        $this->assertCount(4, $question->options);
        $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        $this->assertTrue($question->options()->orderBy('order')->first()->is_correct);
    }

    public function test_a_quiz_is_capped_at_five_questions(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $quiz = Quiz::factory()->create();
        QuizQuestion::factory()->count(5)->withOptions()->create(['quiz_id' => $quiz->id]);

        $this->actingAs($admin)
            ->post(route('training.quiz-questions.store', $quiz), [
                'prompt' => 'One too many?',
                'options' => ['a', 'b', 'c', 'd'],
                'correct_index' => 0,
            ])
            ->assertSessionHasErrors('prompt');

        $this->assertSame(5, $quiz->questions()->count());
    }

    // --- Sending ---------------------------------------------------------

    public function test_assigned_manager_can_send_a_quiz_and_reuses_the_existing_link(): void
    {
        $store = Store::factory()->create();
        $manager = User::factory()->manager($store)->create();
        $trainee = Trainee::factory()->forStore($store)->create();
        $trainee->managers()->attach($manager);
        $quiz = Quiz::factory()->withQuestions()->create();

        $this->actingAs($manager)
            ->post(route('trainees.quiz-attempts.store', $trainee), ['quiz_id' => $quiz->id])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('quiz_attempts', 1);
        $firstToken = QuizAttempt::sole()->token;

        // Sending again (e.g. re-opening the page) must not invalidate the
        // link already shared with the employee.
        $this->actingAs($manager)
            ->post(route('trainees.quiz-attempts.store', $trainee), ['quiz_id' => $quiz->id])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('quiz_attempts', 1);
        $this->assertSame($firstToken, QuizAttempt::sole()->token);
    }

    public function test_unassigned_manager_cannot_send_a_quiz(): void
    {
        $manager = User::factory()->manager()->create();
        $trainee = Trainee::factory()->create();
        $quiz = Quiz::factory()->withQuestions()->create();

        $this->actingAs($manager)
            ->post(route('trainees.quiz-attempts.store', $trainee), ['quiz_id' => $quiz->id])
            ->assertForbidden();
    }

    // --- Trainee-page status (must never leak the score) -----------------

    public function test_trainee_progress_reports_quiz_status_without_ever_exposing_the_score(): void
    {
        $section = Section::factory()->published()->create();
        $quiz = Quiz::factory()->withQuestions()->create(['section_id' => $section->id]);
        $trainee = Trainee::factory()->create();

        $notSent = app(TraineeProgress::class)->detail($trainee)['sections'][0]['quiz'];
        $this->assertSame($quiz->id, $notSent['id']);
        $this->assertNull($notSent['attempt']);

        $attempt = QuizAttempt::factory()->create(['quiz_id' => $quiz->id, 'trainee_id' => $trainee->id]);
        $sent = app(TraineeProgress::class)->detail($trainee)['sections'][0]['quiz'];
        $this->assertSame('sent', $sent['attempt']['status']);
        $this->assertStringContainsString($attempt->token, $sent['attempt']['link']);
        $this->assertArrayNotHasKey('score', $sent['attempt']);

        $attempt->update(['completed_at' => now(), 'score' => 100]);
        $completed = app(TraineeProgress::class)->detail($trainee)['sections'][0]['quiz'];
        $this->assertSame('completed', $completed['attempt']['status']);
        $this->assertNull($completed['attempt']['link']);
        $this->assertArrayNotHasKey('score', $completed['attempt']);
    }

    // --- Public quiz page --------------------------------------------------

    public function test_public_quiz_page_shows_questions_for_a_pending_token(): void
    {
        $this->withoutVite();
        $quiz = Quiz::factory()->withQuestions(3)->create();
        $trainee = Trainee::factory()->create(['name' => 'Sam Crew']);
        $attempt = QuizAttempt::factory()->create(['quiz_id' => $quiz->id, 'trainee_id' => $trainee->id]);

        $this->get(route('quiz.show', $attempt->token))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('quiz/show')
                ->where('traineeName', 'Sam Crew')
                ->where('completed', false)
                ->has('questions', 3)
                ->has('questions.0.options', 4)
            );
    }

    public function test_public_quiz_page_hides_questions_once_completed(): void
    {
        $this->withoutVite();
        $quiz = Quiz::factory()->withQuestions()->create();
        $attempt = QuizAttempt::factory()->completed()->create(['quiz_id' => $quiz->id]);

        $this->get(route('quiz.show', $attempt->token))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('completed', true)
                ->has('questions', 0)
            );
    }

    public function test_an_unknown_token_404s(): void
    {
        $this->get(route('quiz.show', 'not-a-real-token'))->assertNotFound();
    }

    public function test_submitting_the_quiz_scores_it_and_locks_the_link(): void
    {
        $quiz = Quiz::factory()->withQuestions(2)->create();
        $attempt = QuizAttempt::factory()->create(['quiz_id' => $quiz->id]);
        $questions = $quiz->questions()->with('options')->get();

        // Answer the first question correctly, the second incorrectly.
        $answers = [
            $questions[0]->id => $questions[0]->options->firstWhere('is_correct', true)->id,
            $questions[1]->id => $questions[1]->options->firstWhere('is_correct', false)->id,
        ];

        $this->post(route('quiz.store', $attempt->token), ['answers' => $answers])
            ->assertRedirect(route('quiz.show', $attempt->token));

        $attempt->refresh();
        $this->assertTrue($attempt->isCompleted());
        $this->assertSame(50, $attempt->score);
        $this->assertDatabaseCount('quiz_answers', 2);

        // The link is now inert — no re-submitting over a scored attempt.
        $this->post(route('quiz.store', $attempt->token), $answers)->assertNotFound();
    }

    public function test_submitting_rejects_an_option_that_belongs_to_a_different_question(): void
    {
        $quiz = Quiz::factory()->withQuestions(2)->create();
        $attempt = QuizAttempt::factory()->create(['quiz_id' => $quiz->id]);
        $questions = $quiz->questions()->with('options')->get();

        $this->post(route('quiz.store', $attempt->token), [
            'answers' => [
                // Swapped: question 0 answered with question 1's option id.
                $questions[0]->id => $questions[1]->options->first()->id,
                $questions[1]->id => $questions[1]->options->first()->id,
            ],
        ])->assertSessionHasErrors("answers.{$questions[0]->id}");

        $this->assertFalse($attempt->fresh()->isCompleted());
    }

    // --- Quiz Results (training team only) --------------------------------

    public function test_manager_cannot_view_quiz_results(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)->get(route('training.quiz-results.index'))->assertForbidden();
    }

    public function test_super_admin_sees_full_answers_and_score_in_quiz_results(): void
    {
        $this->withoutVite();
        $admin = User::factory()->superAdmin()->create();
        $quiz = Quiz::factory()->withQuestions(1)->create();
        $trainee = Trainee::factory()->create(['name' => 'Jordan Lee']);
        $attempt = QuizAttempt::factory()->completed(75)->create([
            'quiz_id' => $quiz->id,
            'trainee_id' => $trainee->id,
        ]);
        $question = $quiz->questions->first();
        $chosen = $question->options->firstWhere('is_correct', false);
        $attempt->answers()->create([
            'quiz_question_id' => $question->id,
            'quiz_question_option_id' => $chosen->id,
        ]);

        $this->actingAs($admin)
            ->get(route('training.quiz-results.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('attempts', 1)
                ->where('attempts.0.trainee.name', 'Jordan Lee')
                ->where('attempts.0.score', 75)
            );

        $this->actingAs($admin)
            ->get(route('training.quiz-results.show', $attempt))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('attempt.score', 75)
                ->has('questions.0.options', 4)
                ->where('questions.0.options', fn ($options) => collect($options)
                    ->firstWhere('id', $chosen->id)['is_chosen'] === true)
            );
    }

    public function test_resetting_an_attempt_allows_sending_the_quiz_again(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $quiz = Quiz::factory()->withQuestions()->create();
        $attempt = QuizAttempt::factory()->completed()->create(['quiz_id' => $quiz->id]);

        $this->actingAs($admin)
            ->delete(route('training.quiz-results.destroy', $attempt))
            ->assertRedirect(route('training.quiz-results.index'));

        $this->assertDatabaseMissing('quiz_attempts', ['id' => $attempt->id]);
    }
}
