<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\Training\ReportAnalytics;
use App\Services\Training\ReportScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly ReportAnalytics $analytics) {}

    /**
     * The reporting hub. The overview KPIs load eagerly; the heavier per-area
     * datasets are deferred so the page shell paints immediately and the panels
     * stream in.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $scope = $this->analytics->for($user, [
            'store' => $request->integer('store') ?: null,
            'weeks' => $request->integer('weeks') ?: null,
        ]);

        return Inertia::render('reports/index', [
            'isSuperAdmin' => $user->isSuperAdmin(),
            'canChooseStore' => $user->isSuperAdmin(),
            'storeOptions' => $user->isSuperAdmin()
                ? Store::orderBy('name')->get(['id', 'name'])
                : [],
            'weekOptions' => ReportAnalytics::WEEK_OPTIONS,
            'filters' => ['store' => $scope->storeId, 'weeks' => $scope->weeks],
            'overview' => $this->analytics->overview($scope),
            'trend' => Inertia::defer(fn () => $this->analytics->completionTrend($scope), 'reports'),
            'distribution' => Inertia::defer(fn () => $this->analytics->scoreDistribution($scope), 'reports'),
            'storePerformance' => Inertia::defer(fn () => $this->analytics->storePerformance($scope), 'reports'),
            'managerActivity' => Inertia::defer(fn () => $this->analytics->managerActivity($scope), 'reports'),
            'traineeStatus' => Inertia::defer(fn () => $this->analytics->traineeStatus($scope), 'reports'),
            'stationInsights' => Inertia::defer(fn () => $this->analytics->stationInsights($scope), 'reports'),
            'importanceBreakdown' => Inertia::defer(fn () => $this->analytics->importanceBreakdown($scope), 'reports'),
        ]);
    }

    /**
     * CSV / PDF export, respecting the same scope + filters as the hub. CSV
     * downloads a single tabular report; PDF renders a one-page summary.
     */
    public function export(Request $request): HttpResponse
    {
        $request->validate([
            'format' => ['nullable', Rule::in(['csv', 'pdf'])],
            'report' => ['nullable', Rule::in(['trainees', 'stores', 'managers', 'stations'])],
            'store' => ['nullable', 'integer'],
            'weeks' => ['nullable', 'integer'],
        ]);

        $scope = $this->analytics->for($request->user(), [
            'store' => $request->integer('store') ?: null,
            'weeks' => $request->integer('weeks') ?: null,
        ]);

        if ($request->string('format')->toString() === 'pdf') {
            return $this->pdf($scope);
        }

        return $this->csv($scope, $request->string('report')->toString() ?: 'trainees');
    }

    /**
     * Stream a single report as CSV.
     */
    private function csv(ReportScope $scope, string $report): StreamedResponse
    {
        [$name, $headers, $rows] = $this->table($scope, $report);

        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, "training-{$name}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * Render the executive summary as a downloadable PDF.
     */
    private function pdf(ReportScope $scope): HttpResponse
    {
        return Pdf::loadView('reports.summary', [
            'overview' => $this->analytics->overview($scope),
            'stores' => $this->analytics->storePerformance($scope),
            'trainees' => $this->analytics->traineeStatus($scope)['rows'],
            'stations' => $this->analytics->stationInsights($scope)['sections'],
            'generatedAt' => now()->toDayDateTimeString(),
        ])->download('training-report.pdf');
    }

    /**
     * Build the header row + data rows for a CSV report.
     *
     * @return array{0: string, 1: array<int, string>, 2: array<int, array<int, string|int|float|null>>}
     */
    private function table(ReportScope $scope, string $report): array
    {
        return match ($report) {
            'stores' => [
                'stores',
                ['Store', 'Trainees', 'Completion %', 'Avg score'],
                array_map(fn (array $r): array => [
                    $r['name'], $r['trainees'], $r['completion'], $r['average_score'],
                ], $this->analytics->storePerformance($scope)),
            ],
            'managers' => [
                'trainers',
                ['Trainer', 'Store', 'Trainees', 'Evaluations', 'Avg score'],
                array_map(fn (array $r): array => [
                    $r['name'], $r['store'], $r['assigned_trainees'], $r['evaluations_recorded'], $r['average_score'],
                ], $this->analytics->managerActivity($scope)),
            ],
            'stations' => [
                'stations',
                ['Station', 'Completion %', 'Avg score'],
                array_map(fn (array $r): array => [
                    $r['title'], $r['completion'], $r['average_score'],
                ], $this->analytics->stationInsights($scope)['sections']),
            ],
            default => [
                'trainees',
                ['Trainee', 'Position', 'Store', 'Status', 'Completion %', 'Avg score', 'Last activity'],
                array_map(fn (array $r): array => [
                    $r['name'], $r['position'], $r['store'], $r['status'], $r['completion'], $r['average_score'], $r['last_activity'],
                ], $this->analytics->traineeStatus($scope)['rows']),
            ],
        };
    }
}
