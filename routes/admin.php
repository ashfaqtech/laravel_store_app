<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController;

// Admin Authentication Routes
Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [LoginController::class, 'login'])->name('admin.login.submit');
    Route::post('logout', [LoginController::class, 'logout'])->name('admin.logout');

    // Protected Admin Routes
    Route::group(['middleware' => 'admin'], function () {
        Route::get('dashboard', 'DashboardController@index')->name('admin.dashboard');
        
        // Products Management
        Route::resource('products', 'ProductController');
        
        // Categories Management
        Route::resource('categories', 'CategoryController');
        
        // Orders Management
        Route::resource('orders', 'OrderController');
        
        // Users Management
        Route::resource('users', 'UserController');
    });
});
