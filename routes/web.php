<?php

use App\Models\ProductCategory;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (auth()->guest()) {
        return redirect()->route('login');
    } else {
        return redirect()->route('admin.products.index');
    }
});

Auth::routes();

Route::group(['prefix' => '/admin', 'as' => 'admin.', 'middleware' => ['auth', 'activation']], function () {

    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');

    // Categories
    Route::get('/categories/export/{type}', [\App\Http\Controllers\CategoryController::class, 'export'])->name('categories.export');
    Route::resource('/categories', \App\Http\Controllers\CategoryController::class);
    Route::post('/categories/{category}/add-product', [\App\Http\Controllers\CategoryController::class, 'add_product'])->name('categories.add_product');
    Route::delete('/categories/{category}/remove-product', [\App\Http\Controllers\CategoryController::class, 'remove_product'])->name('categories.remove_product');

    // Products
    Route::post('/products/import', [\App\Http\Controllers\ProductController::class, 'import'])->name('products.import');
    Route::get('/products/export/{type}', [\App\Http\Controllers\ProductController::class, 'export'])->name('products.export');
    Route::resource('/products', \App\Http\Controllers\ProductController::class);
    Route::get('/products/{product}/export/{type}', [\App\Http\Controllers\ProductController::class, 'export_detail'])->name('product-detail.export');

    // Transactions
    Route::put('/transactions/verify', [\App\Http\Controllers\TransactionController::class, 'verify'])->name('transactions.verify');
    Route::resource('/transactions', \App\Http\Controllers\TransactionController::class);
    Route::get('/transactions/pending/export/{type}', [\App\Http\Controllers\TransactionController::class, 'export_pending'])->name('pending-transactions.export');
    Route::get('/transactions/{transaction}/export', [\App\Http\Controllers\TransactionController::class, 'export_per_transaction'])->name('transactions.export_per_transaction');

    // Histories
    Route::get('/histories/export/{type}', [\App\Http\Controllers\HistoryController::class, 'export'])->name('histories.export');
    Route::resource('/histories', \App\Http\Controllers\HistoryController::class);

    // Check Stock
    Route::resource('/check_stocks', \App\Http\Controllers\CheckStockController::class);

    // Users
    Route::get('/users/export/{type}', [\App\Http\Controllers\UserController::class, 'export'])->name('users.export');
    Route::resource('/users', \App\Http\Controllers\UserController::class);
    Route::match(['GET', 'POST'], '/users/{user}/reset-password', [\App\Http\Controllers\UserController::class, 'reset_password'])->name('users.reset_password');
    Route::match(['GET', 'POST'], '/users/{user}/reset-pin', [\App\Http\Controllers\UserController::class, 'reset_pin'])->name('users.reset_pin');
    Route::post('/users/{user}/activate', [\App\Http\Controllers\UserController::class, 'activate'])->name('users.activate');
    Route::post('/users/check-pin', [\App\Http\Controllers\UserController::class, 'check_pin'])->name('users.check_pin');
    Route::post('/users/close-store', [\App\Http\Controllers\UserController::class, 'close_store'])->name('users.close_store');

    // App Errors
     Route::post('/app_errors/check', [\App\Http\Controllers\AppErrorsController::class, 'check'])->name('app_errors.check');
     Route::post('/app_errors/{type}/solve', [\App\Http\Controllers\AppErrorsController::class, 'solve'])->name('app_errors.solve');
     Route::resource('/app_errors', \App\Http\Controllers\AppErrorsController::class);

});
