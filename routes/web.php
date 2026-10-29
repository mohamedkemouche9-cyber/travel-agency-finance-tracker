<?php

/**
 * ============================================================================
 *  routes/web.php - the route table of the application.
 * ----------------------------------------------------------------------------
 *  Replaces the hand-written router of the previous version. Laravel's
 *  'guest' middleware protects the auth pages from logged-in users and
 *  'auth' protects the application pages from visitors. Every POST route
 *  is automatically CSRF-protected by the framework's web middleware group.
 * ============================================================================
 */

use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/* ------------------------------ guest pages ------------------------------- */

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/verify', [AuthController::class, 'showVerify'])->name('verify');
    Route::post('/verify', [AuthController::class, 'verify']);
    Route::post('/resend', [AuthController::class, 'resend'])->name('resend');

    Route::get('/forgot', [AuthController::class, 'showForgot'])->name('forgot');
    Route::post('/forgot', [AuthController::class, 'forgot']);

    Route::get('/reset', [AuthController::class, 'showReset'])->name('reset');
    Route::post('/reset', [AuthController::class, 'reset']);
});

/* --------------------------- authenticated pages -------------------------- */

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Transactions - full CRUD + CSV export
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::get('/transactions/add', [TransactionController::class, 'create'])->name('transactions.add');
    Route::post('/transactions/add', [TransactionController::class, 'store']);
    Route::get('/transactions/edit/{id}', [TransactionController::class, 'edit'])->name('transactions.edit');
    Route::post('/transactions/edit/{id}', [TransactionController::class, 'update']);
    Route::post('/transactions/delete/{id}', [TransactionController::class, 'destroy'])->name('transactions.delete');
    Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
    Route::post('/categories/add', [CategoryController::class, 'store'])->name('categories.add');
    Route::post('/categories/update/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::post('/categories/delete/{id}', [CategoryController::class, 'destroy'])->name('categories.delete');

    // Budgets
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets');
    Route::post('/budgets/save', [BudgetController::class, 'save'])->name('budgets.save');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
    Route::post('/settings/password', [SettingsController::class, 'password'])->name('settings.password');
    Route::post('/settings/delete', [SettingsController::class, 'destroy'])->name('settings.delete');

    // JSON endpoints consumed by the dashboard charts
    Route::get('/api/cashflow', [ApiController::class, 'cashflow'])->name('api.cashflow');
    Route::get('/api/categories', [ApiController::class, 'categories'])->name('api.categories');
    Route::get('/api/forecast', [ApiController::class, 'forecast'])->name('api.forecast');
});
