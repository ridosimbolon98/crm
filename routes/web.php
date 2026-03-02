<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\PublicComplaintController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicComplaintController::class, 'landing'])->name('landing');
Route::get('/complaint-form', [PublicComplaintController::class, 'create'])->name('public.complaints.create');
Route::post('/complaint-form', [PublicComplaintController::class, 'store'])->name('public.complaints.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/panduan', [GuideController::class, 'index'])->name('guide.index');
    Route::get('/panduan/pdf', [GuideController::class, 'pdf'])->name('guide.pdf');

    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/create', [ComplaintController::class, 'create'])
        ->middleware('role:admin,manager,qa,cs,sales,marketing')
        ->name('complaints.create');
    Route::get('/complaints/export/excel', [ComplaintController::class, 'exportExcel'])->name('complaints.export.excel');
    Route::get('/complaints/export/pdf', [ComplaintController::class, 'exportPdf'])->name('complaints.export.pdf');

    Route::post('/complaints', [ComplaintController::class, 'store'])
        ->middleware('role:admin,manager,qa,cs,sales,marketing')
        ->name('complaints.store');

    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');

    Route::patch('/complaints/{complaint}/status', [ComplaintController::class, 'updateStatus'])
        ->middleware('role:admin,manager,qa,cs,sales,marketing,ppic')
        ->name('complaints.status');
    Route::patch('/complaints/{complaint}/action-type', [ComplaintController::class, 'updateActionType'])
        ->middleware('role:admin,qa')
        ->name('complaints.action_type');
    Route::post('/complaints/{complaint}/replacement-progress', [ComplaintController::class, 'storeReplacementProgress'])
        ->middleware('role:admin,qa,ppic')
        ->name('complaints.replacement_progress.store');

    Route::patch('/complaints/{complaint}/capa/submit', [ComplaintController::class, 'submitCapa'])
        ->middleware('role:admin,qa')
        ->name('complaints.capa.submit');
    Route::patch('/complaints/{complaint}/capa/approve', [ComplaintController::class, 'approveCapa'])
        ->middleware('role:admin,manager')
        ->name('complaints.capa.approve');
    Route::patch('/complaints/{complaint}/capa/reject', [ComplaintController::class, 'rejectCapa'])
        ->middleware('role:admin,manager')
        ->name('complaints.capa.reject');
    Route::patch('/complaints/{complaint}/capa/close', [ComplaintController::class, 'closeCapa'])
        ->middleware('role:admin,manager,qa')
        ->name('complaints.capa.close');

    Route::post('/complaints/{complaint}/notes', [ComplaintController::class, 'storeNote'])
        ->middleware('role:admin,manager,qa,cs,sales,marketing,ppic')
        ->name('complaints.notes');

    Route::post('/complaints/{complaint}/attachments', [ComplaintController::class, 'storeAttachment'])
        ->middleware('role:admin,manager,qa,cs,sales,marketing,ppic')
        ->name('complaints.attachments.store');

    Route::get('/complaints/{complaint}/attachments/{attachment}', [ComplaintController::class, 'downloadAttachment'])
        ->name('complaints.attachments.download');

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/master-data', [MasterDataController::class, 'index'])->name('master.index');

        Route::post('/master-data/brands', [MasterDataController::class, 'storeBrand'])->name('master.brands.store');
        Route::put('/master-data/brands/{brand}', [MasterDataController::class, 'updateBrand'])->name('master.brands.update');
        Route::delete('/master-data/brands/{brand}', [MasterDataController::class, 'deleteBrand'])->name('master.brands.delete');

        Route::post('/master-data/categories', [MasterDataController::class, 'storeCategory'])->name('master.categories.store');
        Route::put('/master-data/categories/{category}', [MasterDataController::class, 'updateCategory'])->name('master.categories.update');
        Route::delete('/master-data/categories/{category}', [MasterDataController::class, 'deleteCategory'])->name('master.categories.delete');

        Route::post('/master-data/severities', [MasterDataController::class, 'storeSeverity'])->name('master.severities.store');
        Route::put('/master-data/severities/{severity}', [MasterDataController::class, 'updateSeverity'])->name('master.severities.update');
        Route::delete('/master-data/severities/{severity}', [MasterDataController::class, 'deleteSeverity'])->name('master.severities.delete');

        Route::post('/master-data/customers', [MasterDataController::class, 'storeCustomer'])->name('master.customers.store');
        Route::put('/master-data/customers/{customer}', [MasterDataController::class, 'updateCustomer'])->name('master.customers.update');
        Route::delete('/master-data/customers/{customer}', [MasterDataController::class, 'deleteCustomer'])->name('master.customers.delete');

        Route::post('/master-data/notification-recipients', [MasterDataController::class, 'storeNotificationRecipient'])->name('master.notification_recipients.store');
        Route::put('/master-data/notification-recipients/{recipient}', [MasterDataController::class, 'updateNotificationRecipient'])->name('master.notification_recipients.update');
        Route::delete('/master-data/notification-recipients/{recipient}', [MasterDataController::class, 'deleteNotificationRecipient'])->name('master.notification_recipients.delete');
    });
});
