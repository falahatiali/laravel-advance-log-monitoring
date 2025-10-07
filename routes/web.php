<?php

use Simorgh\Logger\Http\Controllers\DashboardController;
use Simorgh\Logger\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Advanced Logger Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the AdvancedLoggerServiceProvider.
| They are protected by the middleware specified in the config.
| Middleware and prefix are applied in the ServiceProvider.
|
*/

// Dashboard Routes
Route::get('/', [DashboardController::class, 'index'])->name('advanced-logger.dashboard');
Route::get('/logs', [DashboardController::class, 'logs'])->name('advanced-logger.logs');
Route::get('/stats', [DashboardController::class, 'stats'])->name('advanced-logger.stats');
Route::get('/alerts', [DashboardController::class, 'alerts'])->name('advanced-logger.alerts');

// Log Management Routes
Route::post('/logs/{log}/resolve', [DashboardController::class, 'resolveLog'])->name('advanced-logger.resolve');
Route::post('/logs/{log}/unresolve', [DashboardController::class, 'unresolveLog'])->name('advanced-logger.unresolve');
Route::delete('/logs/{log}', [DashboardController::class, 'deleteLog'])->name('advanced-logger.delete');

// Export Routes
Route::get('/export/json', [DashboardController::class, 'exportJson'])->name('advanced-logger.export.json');
Route::get('/export/csv', [DashboardController::class, 'exportCsv'])->name('advanced-logger.export.csv');
Route::get('/export/xml', [DashboardController::class, 'exportXml'])->name('advanced-logger.export.xml');

// Settings Routes
Route::get('/settings', [DashboardController::class, 'settings'])->name('advanced-logger.settings');
Route::post('/settings', [DashboardController::class, 'updateSettings'])->name('advanced-logger.settings.update');

// Test Alert Routes
Route::post('/test-alerts', [DashboardController::class, 'testAlerts'])->name('advanced-logger.test-alerts');

// API Routes (for AJAX requests)
Route::prefix('api')->group(function () {
    Route::get('/logs', [ApiController::class, 'logs'])->name('advanced-logger.api.logs');
    Route::get('/stats', [ApiController::class, 'stats'])->name('advanced-logger.api.stats');
    Route::get('/real-time', [ApiController::class, 'realTime'])->name('advanced-logger.api.real-time');
});
