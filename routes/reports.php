<?php

use App\Http\Controllers\Reports\ReportController;
use Illuminate\Support\Facades\Route;

/*
 * Reporting hub — available to managers (auto-scoped to assigned trainees) and
 * super admins (all trainees, filterable by store).
 */
Route::middleware(['auth', 'verified'])
    ->prefix('reports')
    ->name('reports.')
    ->group(function (): void {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('export', [ReportController::class, 'export'])->name('export');
    });
