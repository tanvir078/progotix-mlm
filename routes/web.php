<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EarningsController;
use App\Http\Controllers\BinaryTreeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\Admin\AdminBinaryTreeController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\InvoiceManagementController;
use App\Http\Controllers\Admin\MemberCrudController;
use App\Http\Controllers\Admin\MemberManagementController;
use App\Http\Controllers\Admin\PayoutExportController;
use App\Http\Controllers\Admin\PlanManagementController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\WithdrawalManagementController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('network', NetworkController::class)->name('mlm.network');
    Route::get('binary-tree', BinaryTreeController::class)->name('mlm.binary-tree');
    Route::get('earnings', EarningsController::class)->name('mlm.earnings');
    Route::get('plans', [PlanController::class, 'index'])->name('mlm.plans.index');
    Route::post('plans/{plan}/subscribe', [PlanController::class, 'store'])->name('mlm.plans.subscribe');
    Route::get('withdrawals', [WithdrawalController::class, 'index'])->name('mlm.withdrawals.index');
    Route::post('withdrawals', [WithdrawalController::class, 'store'])->name('mlm.withdrawals.store');
    Route::get('invoices', InvoiceController::class)->name('mlm.invoices');
    Route::get('invoices/{invoice}/pdf', InvoicePdfController::class)->name('mlm.invoices.pdf');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', AdminDashboardController::class)->name('dashboard');
    Route::get('members', MemberManagementController::class)->name('members');
    Route::patch('members/{user}', [MemberCrudController::class, 'update'])->name('members.update');
    Route::delete('members/{user}', [MemberCrudController::class, 'destroy'])->name('members.destroy');
    Route::get('binary-tree/{user?}', AdminBinaryTreeController::class)->name('binary-tree');
    Route::get('plans', [PlanManagementController::class, 'index'])->name('plans');
    Route::post('plans', [PlanManagementController::class, 'store'])->name('plans.store');
    Route::patch('plans/{plan}', [PlanManagementController::class, 'update'])->name('plans.update');
    Route::delete('plans/{plan}', [PlanManagementController::class, 'destroy'])->name('plans.destroy');
    Route::get('withdrawals', [WithdrawalManagementController::class, 'index'])->name('withdrawals');
    Route::patch('withdrawals/{withdrawalRequest}', [WithdrawalManagementController::class, 'update'])->name('withdrawals.update');
    Route::get('withdrawals-export', PayoutExportController::class)->name('withdrawals.export');
    Route::get('invoices', InvoiceManagementController::class)->name('invoices');
    Route::get('reports', ReportController::class)->name('reports');
});

require __DIR__.'/settings.php';
