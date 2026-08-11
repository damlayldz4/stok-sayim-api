<?php

use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\BranchController;
use App\Http\Controllers\Web\CompanyController;
use App\Http\Controllers\Web\CompanySettingsController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\ShelfController;
use App\Http\Controllers\Web\StockCountController;
use App\Http\Controllers\Web\StockCountReportController;
use App\Http\Controllers\Web\StockImportController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\WarehouseController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});


Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:system_admin')->group(function () {
        Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
        Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
        Route::post('/companies/{company}/toggle', [CompanyController::class, 'toggle'])->name('companies.toggle');
    });

    Route::middleware('role:company_admin')->group(function () {
        Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('/branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('/branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::post('/branches/{branch}/toggle', [BranchController::class, 'toggle'])->name('branches.toggle');

        Route::get('/branches/{branch}/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('/branches/{branch}/warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('/branches/{branch}/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('/branches/{branch}/warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::put('/branches/{branch}/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
        Route::post('/branches/{branch}/warehouses/{warehouse}/toggle', [WarehouseController::class, 'toggle'])->name('warehouses.toggle');

        Route::get('/branches/{branch}/warehouses/{warehouse}/shelves', [ShelfController::class, 'index'])->name('shelves.index');
        Route::post('/branches/{branch}/warehouses/{warehouse}/shelves', [ShelfController::class, 'store'])->name('shelves.store');
        Route::get('/branches/{branch}/warehouses/{warehouse}/shelves/{shelf}/edit', [ShelfController::class, 'edit'])->name('shelves.edit');
        Route::put('/branches/{branch}/warehouses/{warehouse}/shelves/{shelf}', [ShelfController::class, 'update'])->name('shelves.update');
        Route::post('/branches/{branch}/warehouses/{warehouse}/shelves/{shelf}/toggle', [ShelfController::class, 'toggle'])->name('shelves.toggle');

        Route::get('/products', [ProductController::class, 'index'])->name('products.index');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

        Route::get('/imports', [StockImportController::class, 'index'])->name('imports.index');
        Route::post('/imports', [StockImportController::class, 'store'])->name('imports.store');
        Route::post('/imports/confirm', [StockImportController::class, 'confirm'])->name('imports.confirm');

        Route::get('/stock-counts', [StockCountController::class, 'index'])->name('stock-counts.index');
        Route::get('/stock-counts/create', [StockCountController::class, 'create'])->name('stock-counts.create');
        Route::post('/stock-counts', [StockCountController::class, 'store'])->name('stock-counts.store');
        Route::get('/stock-counts/{stockCount}', [StockCountController::class, 'show'])->name('stock-counts.show');
        Route::post('/stock-counts/{stockCount}/start', [StockCountController::class, 'start'])->name('stock-counts.start');
        Route::post('/stock-counts/{stockCount}/complete', [StockCountController::class, 'complete'])->name('stock-counts.complete');

        Route::get('/stock-counts/{stockCount}/report', [StockCountReportController::class, 'index'])->name('stock-counts.report');
        Route::get('/stock-counts/{stockCount}/report/export', [StockCountReportController::class, 'export'])->name('stock-counts.report.export');

        Route::get('/settings', [CompanySettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [CompanySettingsController::class, 'update'])->name('settings.update');
    });

    // Bu katmanla web paneli ekranları tamamlandı.
});