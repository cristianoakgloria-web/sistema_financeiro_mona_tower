<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/dashboard');
});

/*
|--------------------------------------------------------------------------
| Dashboard (todos autenticados com role válida)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:admin, secretaria, financeiro'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Rotas protegidas
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Perfil do Utilizador (todos)
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | 👑 UTILIZADORES (apenas ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')->middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');
    });


    /*
    |--------------------------------------------------------------------------
    | 🧾 ESTUDANTES (admin + secretaria)
    |--------------------------------------------------------------------------
    */
    Route::resource('students', StudentController::class)
        ->middleware('role:admin,secretaria');


    /*
    |--------------------------------------------------------------------------
    | 💰 FATURAS (admin + financeiro)
    |--------------------------------------------------------------------------
    */
    Route::resource('invoices', InvoiceController::class)
        ->middleware('role:admin,financeiro');


    /*
    |--------------------------------------------------------------------------
    | 💳 PAGAMENTOS (admin + financeiro)
    |--------------------------------------------------------------------------
    */

    // Rotas principais (sem create/store)
    Route::resource('payments', PaymentController::class)
        ->middleware('role:admin,financeiro')
        ->except(['create', 'store']);

    // Pagamentos vinculados à fatura
    Route::prefix('invoices/{invoice}')
        ->middleware('role:admin,financeiro')
        ->group(function () {

            Route::get('/payments/create', [PaymentController::class, 'create'])
                ->name('invoices.payments.create');

            Route::post('/payments', [PaymentController::class, 'store'])
                ->name('invoices.payments.store');

            Route::post('/payments/full', [PaymentController::class, 'createFullPayment'])
                ->name('invoices.payments.full');
        });


    /*
    |--------------------------------------------------------------------------
    | 📊 RELATÓRIOS (admin + financeiro)
    |--------------------------------------------------------------------------
    */
    Route::prefix('relatorios')
        ->middleware('role:admin,financeiro')
        ->group(function () {

            Route::get('/financeiro', [ReportController::class, 'financial'])
                ->name('reports.financial');

            Route::get('/estudantes', [ReportController::class, 'students'])
                ->name('reports.students');

            Route::get('/faturas', [ReportController::class, 'invoices'])
                ->name('reports.invoices');
        });

});

/*
|--------------------------------------------------------------------------
| Autenticação (Laravel Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';