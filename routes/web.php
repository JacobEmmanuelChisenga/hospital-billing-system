<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BillableServiceController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ChargeController;
use App\Http\Controllers\ClinicalNoteController;
use App\Http\Controllers\CompanyAccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\MembershipFeeController;
use App\Http\Controllers\NurseWorkflowController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffUserController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Patient lookup — all operational staff plus admin oversight.
Route::middleware(['auth', 'role:administrator,accounts,registry,nurse'])->group(function () {
    Route::get('patients/search', [PatientController::class, 'search'])->name('patients.search');
    Route::get('patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('patients/{patient}', [PatientController::class, 'show'])->whereNumber('patient')->name('patients.show');
});

// Patient registration — Registry Clerk only.
Route::middleware(['auth', 'role:registry'])->group(function () {
    Route::get('patients/create', [PatientController::class, 'create'])->name('patients.create');
    Route::post('patients', [PatientController::class, 'store'])->name('patients.store');
    Route::get('patients/{patient}/edit', [PatientController::class, 'edit'])->whereNumber('patient')->name('patients.edit');
    Route::put('patients/{patient}', [PatientController::class, 'update'])->whereNumber('patient')->name('patients.update');
    Route::patch('patients/{patient}', [PatientController::class, 'update'])->whereNumber('patient');

    Route::prefix('charges')->name('charges.')->group(function () {
        Route::get('/pending', [ChargeController::class, 'pending'])->name('pending');
        Route::get('/post', [ChargeController::class, 'post'])->name('post');
        Route::get('/history', [ChargeController::class, 'history'])->name('history');
    });
});

// Patient visits — Registry manages; Nurse and Admin can view.
Route::middleware(['auth', 'role:administrator,registry,nurse'])->prefix('visits')->name('visits.')->group(function () {
    Route::get('/', [VisitController::class, 'index'])->name('index');
    Route::get('/{visit}', [VisitController::class, 'show'])->whereNumber('visit')->name('show');
});

// Visit management and billing — Registry Clerk only.
Route::middleware(['auth', 'role:registry'])->prefix('visits')->name('visits.')->group(function () {
    Route::get('/create', [VisitController::class, 'create'])->name('create');
    Route::post('/', [VisitController::class, 'store'])->name('store');
    Route::post('/{visit}/charges', [VisitController::class, 'storeCharge'])->whereNumber('visit')->name('charges.store');
    Route::delete('/{visit}/charges/{chargeLine}', [VisitController::class, 'destroyCharge'])->whereNumber(['visit', 'chargeLine'])->name('charges.destroy');
    Route::post('/{visit}/post-bill', [VisitController::class, 'postBill'])->whereNumber('visit')->name('post-bill');
    Route::post('/{visit}/cancel', [VisitController::class, 'cancel'])->whereNumber('visit')->name('cancel');
});

// Clinical notes — Nurse only.
Route::middleware(['auth', 'role:nurse'])->group(function () {
    Route::get('visits/{visit}/clinical-notes', [ClinicalNoteController::class, 'edit'])->whereNumber('visit')->name('clinical-notes.edit');
    Route::post('visits/{visit}/clinical-notes', [ClinicalNoteController::class, 'store'])->whereNumber('visit')->name('clinical-notes.store');

    Route::prefix('nurse')->name('nurse.')->group(function () {
        Route::get('/queue', [NurseWorkflowController::class, 'queue'])->name('queue');
        Route::get('/active', [NurseWorkflowController::class, 'active'])->name('active');
        Route::get('/consultations', [NurseWorkflowController::class, 'consultations'])->name('consultations');
    });
});

// Financial operations — Accounts Officer only (create/manage money).
Route::middleware(['auth', 'role:accounts'])->group(function () {
    Route::get('deposits', [DepositController::class, 'index'])->name('deposits.index');
    Route::get('deposits/create', [DepositController::class, 'create'])->name('deposits.create');
    Route::post('deposits', [DepositController::class, 'store'])->name('deposits.store');
    Route::get('deposits/{deposit}', [DepositController::class, 'show'])->name('deposits.show');
    Route::get('deposits/{deposit}/receipt', [DepositController::class, 'receipt'])->name('deposits.receipt');
    Route::post('deposits/{deposit}/reverse', [DepositController::class, 'reverse'])->name('deposits.reverse');

    Route::get('company-accounts', [CompanyAccountController::class, 'index'])->name('company-accounts.index');
    Route::get('company-accounts/create', [CompanyAccountController::class, 'create'])->name('company-accounts.create');
    Route::post('company-accounts', [CompanyAccountController::class, 'store'])->name('company-accounts.store');
    Route::get('company-accounts/{company}', [CompanyAccountController::class, 'show'])->name('company-accounts.show');
    Route::get('company-accounts/{company}/edit', [CompanyAccountController::class, 'edit'])->name('company-accounts.edit');
    Route::patch('company-accounts/{company}', [CompanyAccountController::class, 'update'])->name('company-accounts.update');
    Route::post('company-accounts/{company}/suspend', [CompanyAccountController::class, 'suspend'])->name('company-accounts.suspend');
    Route::post('company-accounts/{company}/deposits', [CompanyAccountController::class, 'storeDeposit'])->name('company-accounts.deposits.store');
    Route::post('company-deposits/{companyDeposit}/reverse', [CompanyAccountController::class, 'reverseDeposit'])->name('company-deposits.reverse');

    Route::get('membership-fees', [MembershipFeeController::class, 'index'])->name('membership-fees.index');
    Route::get('membership-fees/create', [MembershipFeeController::class, 'create'])->name('membership-fees.create');
    Route::post('membership-fees', [MembershipFeeController::class, 'store'])->name('membership-fees.store');
    Route::get('membership-fees/{membershipFee}', [MembershipFeeController::class, 'show'])->whereNumber('membershipFee')->name('membership-fees.show');
    Route::get('membership-fees/{membershipFee}/receipt', [MembershipFeeController::class, 'receipt'])->whereNumber('membershipFee')->name('membership-fees.receipt');
});

// Bill view and receipt — Registry (who posts bills) plus Accounts and Administrator.
Route::middleware(['auth', 'role:administrator,accounts,registry'])->group(function () {
    Route::get('billing/{bill}', [BillingController::class, 'show'])->name('billing.show');
    Route::get('billing/{bill}/receipt', [BillingController::class, 'receipt'])->name('billing.receipt');
});

// Receipts list and reports — Accounts and Administrator only.
Route::middleware(['auth', 'role:administrator,accounts'])->group(function () {
    Route::get('billing', [BillingController::class, 'index'])->name('billing.index');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export', [ReportController::class, 'exportSummary'])->name('index.export');
        Route::get('/export/pdf', [ReportController::class, 'exportSummaryPdf'])->name('index.export.pdf');
        Route::get('/transactions', [ReportController::class, 'transactions'])->name('transactions');
        Route::get('/transactions/export', [ReportController::class, 'exportTransactions'])->name('transactions.export');
        Route::get('/transactions/export/pdf', [ReportController::class, 'exportTransactionsPdf'])->name('transactions.export.pdf');
        Route::get('/patients/{patient}/statement', [ReportController::class, 'patientStatement'])->name('patient-statement');
        Route::get('/patients/{patient}/statement/export', [ReportController::class, 'exportPatientStatement'])->name('patient-statement.export');
        Route::get('/patients/{patient}/statement/export/pdf', [ReportController::class, 'exportPatientStatementPdf'])->name('patient-statement.export.pdf');
        Route::get('/member-accounts', [ReportController::class, 'memberAccounts'])->name('member-accounts');
        Route::get('/member-accounts/export', [ReportController::class, 'exportMemberAccounts'])->name('member-accounts.export');
        Route::get('/member-accounts/export/pdf', [ReportController::class, 'exportMemberAccountsPdf'])->name('member-accounts.export.pdf');
        Route::get('/companies', [ReportController::class, 'companies'])->name('companies');
        Route::get('/companies/export', [ReportController::class, 'exportCompanies'])->name('companies.export');
        Route::get('/companies/export/pdf', [ReportController::class, 'exportCompaniesPdf'])->name('companies.export.pdf');
        Route::get('/companies/{company}', [ReportController::class, 'companyShow'])->name('companies.show');
        Route::get('/companies/{company}/export', [ReportController::class, 'exportCompany'])->name('companies.show.export');
        Route::get('/companies/{company}/export/pdf', [ReportController::class, 'exportCompanyPdf'])->name('companies.show.export.pdf');
    });
});

// Registry can void bills on completed visits.
Route::middleware(['auth', 'role:registry'])->group(function () {
    Route::post('billing/{bill}/void', [BillingController::class, 'void'])->name('billing.void');
});

// Audit trail and system admin — Administrator only.
Route::middleware(['auth', 'role:administrator'])->prefix('audit-logs')->name('audit-logs.')->group(function () {
    Route::get('/', [AuditLogController::class, 'index'])->name('index');
    Route::get('/export', [AuditLogController::class, 'export'])->name('export');
    Route::get('/export/pdf', [AuditLogController::class, 'exportPdf'])->name('export.pdf');
    Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
});

Route::middleware(['auth', 'role:administrator'])->group(function () {
    Route::prefix('staff-users')->name('staff-users.')->group(function () {
        Route::get('/', [StaffUserController::class, 'index'])->name('index');
        Route::get('/create', [StaffUserController::class, 'create'])->name('create');
        Route::post('/', [StaffUserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [StaffUserController::class, 'edit'])->name('edit');
        Route::patch('/{user}', [StaffUserController::class, 'update'])->name('update');
    });

    Route::prefix('system-settings')->name('system-settings.')->group(function () {
        Route::get('/', [SystemSettingController::class, 'edit'])->name('edit');
        Route::patch('/', [SystemSettingController::class, 'update'])->name('update');
    });

    Route::prefix('billable-services')->name('billable-services.')->group(function () {
        Route::get('/', [BillableServiceController::class, 'index'])->name('index');
        Route::get('/create', [BillableServiceController::class, 'create'])->name('create');
        Route::post('/', [BillableServiceController::class, 'store'])->name('store');
        Route::get('/{billableService}/edit', [BillableServiceController::class, 'edit'])->name('edit');
        Route::patch('/{billableService}', [BillableServiceController::class, 'update'])->name('update');
    });
});

require __DIR__.'/auth.php';
