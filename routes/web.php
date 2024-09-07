<?php

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
        return redirect()->route('admin.dashboard');
    }
});

Auth::routes();

Route::group(['prefix' => '/admin', 'as' => 'admin.', 'middleware' => ['auth']], function () {

    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');

    // Categories
    Route::resource('/categories', \App\Http\Controllers\CategoryController::class);

    // Products
    Route::resource('/products', \App\Http\Controllers\ProductController::class);

    // Transactions
    Route::resource('/transactions', \App\Http\Controllers\TransactionController::class);

});
