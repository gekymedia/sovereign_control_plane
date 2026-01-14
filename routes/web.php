<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GovernanceController;
use App\Http\Controllers\ScenarioController;

// Authentication routes with rate limiting
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,15'); // 5 attempts per 15 minutes
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// OAuth routes with rate limiting
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google')->middleware('throttle:10,1');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback')->middleware('throttle:10,1');
Route::get('/auth/github', [AuthController::class, 'redirectToGithub'])->name('auth.github')->middleware('throttle:10,1');
Route::get('/auth/github/callback', [AuthController::class, 'handleGithubCallback'])->name('auth.github.callback')->middleware('throttle:10,1');

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Governance panel - only admins can manage governance settings
    Route::prefix('governance')->middleware('role:admin')->group(function () {
        Route::get('/', [GovernanceController::class, 'index'])->name('governance.index');
        Route::post('/', [GovernanceController::class, 'update'])->name('governance.update');
    });

    // Scenario runner - oracles manage scenarios
    Route::prefix('scenarios')->middleware('role:oracle')->group(function () {
        Route::get('/', [ScenarioController::class, 'index'])->name('scenarios.index');
        Route::post('/upload', [ScenarioController::class, 'upload'])->name('scenarios.upload');
        Route::post('/run', [ScenarioController::class, 'run'])->name('scenarios.run');
        Route::delete('/{scenario}', [ScenarioController::class, 'destroy'])->name('scenarios.destroy');
    });
});
